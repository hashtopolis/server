<?php

use DBA\AccessGroup;
use DBA\AccessGroupAgent;
use DBA\Agent;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ContainFilter;
use DBA\File;
use DBA\FileTask;
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\TaskWrapper;
use DBA\NotificationSetting;
use DBA\AgentError;
use DBA\Hash;

class TaskUtils {
  /**
   * Splits a given task into subtasks within a supertask by splitting the rule file
   * @param Task $task
   * @param TaskWrapper $taskWrapper
   * @param File[] $files
   * @param File $splitFile
   * @param array $split
   */
  public static function splitByRules($task, $taskWrapper, $files, $splitFile, $split){
    global $FACTORIES;

    // calculate how much we need to split
    $numSplits = $split[1] / 1000 / ($task->getChunkTime() * 0.75);
    $numLines = Util::countLines(dirname(__FILE__) . "/../../files/" . $splitFile->getFilename());
    $linesPerFile = $numLines / $numSplits + 1;

    // create the temporary rule files
    $newFiles = [];
    $content = explode("\n", str_replace("\r\n", "\n", file_get_contents(dirname(__FILE__) . "/../../files/" . $splitFile->getFilename())));
    $count = 0;
    for($i=0; $i < $numLines; $i += $linesPerFile, $count++){
      $copy = [];
      for($j=$i; $j < $i + $linesPerFile && $j < sizeof($content); $j++){
        $copy[] = $content[$j];
      }
      file_put_contents(dirname(__FILE__) . "/../../files/" . $splitFile->getFilename() . "_p$count", implode("\n", $copy));
      $f = new File(0, $splitFile->getFilename() . "_p$count", Util::filesize(dirname(__FILE__) . "/../../files/" . $splitFile->getFilename() . "_p$count"), $splitFile->getIsSecret(), DFileType::TEMPORARY);
      $f = $FACTORIES::getFileFactory()->save($f);
      $newFiles[] = $f;
    }

    // take out the split file from the file list
    for($i = 0; $i < sizeof($files); $i++){
      if($files[$i]->getId() == $splitFile->getId()){
        unset($files[$i]);
        break;
      }
    }

    // create new tasks as supertask
    $newWrapper = new TaskWrapper(0, 0, DTaskTypes::SUPERTASK, $taskWrapper->getHashlistId(), $taskWrapper->getAccessGroupId(), $task->getTaskName());
    $newWrapper = $FACTORIES::getTaskWrapperFactory()->save($newWrapper);
    $prio = sizeof($newFiles) + 1;
    foreach($newFiles as $newFile){
      $newTask = new Task(0,
        "Part " . (sizeof($newFiles) + 2 - $prio),
        str_replace($splitFile->getFilename(), $newFile->getFilename(), $task->getAttackCmd()),
        $task->getChunkTime(),
        $task->getStatusTimer(),
        0,
        0,
        $prio,
        $task->getColor(),
        0,
        $task->getIsCpuTask(),
        $task->getUseNewBench(),
        $task->getSkipKeyspace(),
        $task->getCrackerBinaryId(),
        $task->getCrackerBinaryTypeId(),
        $newWrapper->getId());
      $newTask = $FACTORIES::getTaskFactory()->save($newTask);
      $taskFiles = [];
      $taskFiles[] = new FileTask(0, $newFile->getId(), $newTask->getId());
      foreach($files as $f){
        $taskFiles[] = new FileTask(0, $f->getId(), $newTask->getId());
      }
      $FACTORIES::getFileTaskFactory()->massSave($taskFiles);
      $prio--;
    }
    $newWrapper->setPriority($taskWrapper->getPriority());
    $FACTORIES::getTaskWrapperFactory()->update($newWrapper);

    // cleanup
    TaskUtils::deleteTask($task);
    $FACTORIES::getTaskWrapperFactory()->delete($taskWrapper);
  }

  /**
   * @param $agent Agent
   * @param bool $all set true to get all matching tasks for this agent
   * @return Task|Task[]
   */
  public static function getBestTask($agent, $all = false) {
    global $FACTORIES;

    $allTasks = array();

    // load all groups where this agent has access to
    $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=", $FACTORIES::getAccessGroupAgentFactory());
    $jF = new JoinFilter($FACTORIES::getAccessGroupAgentFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupAgent::ACCESS_GROUP_ID);
    $joined = $FACTORIES::getAccessGroupFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    /** @var $accessGroupAgent AccessGroup[] */
    $accessGroupAgent = $joined[$FACTORIES::getAccessGroupFactory()->getModelName()];
    $accessGroups = Util::arrayOfIds($accessGroupAgent);

    // get all TaskWrappers which we have access to
    $qF1 = new ContainFilter(TaskWrapper::ACCESS_GROUP_ID, $accessGroups);
    $qF2 = new QueryFilter(TaskWrapper::PRIORITY, 0, ">");
    if ($all) {
      // if we want to retrieve all tasks which are accessible, we also show the ones with 0 priority
      $qF2 = new QueryFilter(TaskWrapper::PRIORITY, 0, ">=");
    }
    $oF = new OrderFilter(TaskWrapper::PRIORITY, "DESC");
    $taskWrappers = $FACTORIES::getTaskWrapperFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2), $FACTORIES::ORDER => $oF));

    // go trough task wrappers and test if we have access
    foreach ($taskWrappers as $taskWrapper) {
      $hashlists = Util::checkSuperHashlist($FACTORIES::getHashlistFactory()->get($taskWrapper->getHashlistId()));
      $permitted = true;
      foreach ($hashlists as $hashlist) {
        if ($hashlist->getIsSecret() > $agent->getIsTrusted()) {
          $permitted = false;
        }
        else if (!in_array($hashlist->getAccessGroupId(), $accessGroups)) {
          $permitted = false;
        }
      }
      if (!$permitted) {
        continue; // if at least one of the hashlists is secret and the agent not, this taskWrapper cannot be used
      }

      // load assigned tasks for this TaskWrapper
      $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
      $oF = new OrderFilter(Task::PRIORITY, "DESC");
      $tasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF));
      foreach ($tasks as $task) {
        // check if a task suits to this agent
        $files = TaskUtils::getFilesOfTask($task);
        $permitted = true;
        foreach ($files as $file) {
          if ($file->getIsSecret() > $agent->getIsTrusted()) {
            $permitted = false;
          }
        }
        if (!$permitted) {
          continue; // at least one of the files required for this task is secret and the agent not, so this task cannot be used
        }

        // we need to check now if the task is already completed or fully dispatched
        $task = TaskUtils::checkTask($task, $agent);
        if ($task == null) {
          continue; // if it is completed we go to the next
        }

        // check if it's a small task
        if ($task->getIsSmall() == 1) {
          $qF1 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
          $qF2 = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "<>");
          $numAssignments = $FACTORIES::getAssignmentFactory()->countFilter(array($FACTORIES::FILTER => array($qF1, $qF2)));
          if ($numAssignments > 0) {
            continue; // at least one agent is already assigned here
          }
        }
        // check if it's a cpu/gpu task
        if ($task->getIsCpuTask() != $agent->getCpuOnly()) {
          continue;
        }

        // this task is available for this user regarding permissions
        if ($all) {
          $allTasks[] = $task;
          continue;
        }
        return $task;
      }
    }
    if ($all) {
      return $allTasks;
    }
    return null;
  }

  /**
   * @param $task Task
   */
  public function deleteTask($task) {
    global $FACTORIES;

    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $chunkIds = Util::arrayOfIds($FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF)));

    $qF = new QueryFilter(NotificationSetting::OBJECT_ID, $task->getId(), "=");
    $notifications = $FACTORIES::getNotificationSettingFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach ($notifications as $notification) {
      if (DNotificationType::getObjectType($notification->getAction()) == DNotificationObjectType::TASK) {
        $FACTORIES::getNotificationSettingFactory()->delete($notification);
      }
    }

    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $FACTORIES::getAssignmentFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $qF = new QueryFilter(AgentError::TASK_ID, $task->getId(), "=");
    $FACTORIES::getAgentErrorFactory()->massDeletion(array($FACTORIES::FILTER => $qF));

    // test if this task used temporary files
    $qF = new QueryFilter(FileTask::TASK_ID, $task->getId(), "=", $FACTORIES::getFileTaskFactory());
    $jF = new JoinFilter($FACTORIES::getFileTaskFactory(), File::FILE_ID, FileTask::FILE_ID);
    $joined = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    $files = $joined[$FACTORIES::getFileFactory()->getModelName()];
    $toDelete = [];
    foreach($files as $file){ /** @var $file File */
      if($file->getFileType() == DFileType::TEMPORARY){
        $toDelete[] = $file;
      }
    }
    $FACTORIES::getFileTaskFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $qF = new ContainFilter(File::FILE_ID, Util::arrayOfIds($toDelete));
    $FACTORIES::getFileFactory()->massDeletion(array($FACTORIES::FILTER => $qF));

    $uS = new UpdateSet(Hash::CHUNK_ID, null);
    if (sizeof($chunkIds) > 0) {
      $qF2 = new ContainFilter(Hash::CHUNK_ID, $chunkIds);
      $FACTORIES::getHashFactory()->massUpdate(array($FACTORIES::FILTER => $qF2, $FACTORIES::UPDATE => $uS));
      $FACTORIES::getHashBinaryFactory()->massUpdate(array($FACTORIES::FILTER => $qF2, $FACTORIES::UPDATE => $uS));
    }
    $FACTORIES::getChunkFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $FACTORIES::getTaskFactory()->delete($task);
  }

  /**
   * Checks if a task is completed or fully dispatched.
   *
   * @param $task Task
   * @param $agent Agent
   * @return Task null if the task is completed or fully dispatched
   */
  public static function checkTask($task, $agent = null) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;

    if ($task->getKeyspace() == 0) {
      return $task;
    }

    // check chunks
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
    $dispatched = $task->getSkipKeyspace();
    $completed = $task->getSkipKeyspace();
    foreach ($chunks as $chunk) {
      if ($chunk->getProgress() >= 10000) {
        $dispatched += $chunk->getLength();
        $completed += $chunk->getLength();
      }
      else if ($chunk->getAgentId() == null) {
        return $task; // at least one chunk is not assigned
      }
      else if (time() - max($chunk->getSolveTime(), $chunk->getDispatchTime()) > $CONFIG->getVal(DConfig::AGENT_TIMEOUT)) {
        // this chunk timed out, so we remove the agent from it and therefore this task is not complete yet
        //$chunk->setAgentId(null);
        //$FACTORIES::getChunkFactory()->update($chunk);
        return $task;
      }
      else if ($agent != null && $chunk->getAgentId() == $agent->getId()) {
        return $task;
      }
      else {
        $dispatched += $chunk->getLength();
      }
    }
    if ($completed >= $task->getKeyspace()) {
      // task is completed, set priority to 0
      $task->setPriority(0);
      $FACTORIES::getTaskFactory()->update($task);
      $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
      if ($taskWrapper->getTaskType() != DTaskTypes::SUPERTASK) {
        $taskWrapper->setPriority(0);
        $FACTORIES::getTaskWrapperFactory()->update($taskWrapper);
      }
      return null;
    }
    else if ($dispatched >= $task->getKeyspace()) {
      return null;
    }
    return $task;
  }

  /**
   * @param $hashlists Hashlist[]
   */
  public static function unassignAllAgents($hashlists) {
    global $FACTORIES;

    $twFilter = new ContainFilter(TaskWrapper::HASHLIST_ID, Util::arrayOfIds($hashlists));
    $taskWrappers = $FACTORIES::getTaskWrapperFactory()->filter(array($FACTORIES::FILTER => $twFilter));
    foreach ($taskWrappers as $taskWrapper) {
      $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
      $tasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF));
      $qF = new ContainFilter(Assignment::TASK_ID, Util::arrayOfIds($tasks));
      $FACTORIES::getAssignmentFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    }
    $uS = new UpdateSet(TaskWrapper::PRIORITY, 0);
    $FACTORIES::getTaskWrapperFactory()->massUpdate(array($FACTORIES::FILTER => $twFilter, $FACTORIES::UPDATE => $uS));
  }

  /**
   * @param $task1 Task
   * @param $task2 Task
   * @return Task task which should be worked on
   */
  public static function getImportantTask($task1, $task2) {
    global $FACTORIES;

    if ($task1 == null) {
      return $task2;
    }
    else if ($task2 == null) {
      return $task1;
    }

    $taskWrapper1 = $FACTORIES::getTaskWrapperFactory()->get($task1->getTaskWrapperId());
    $taskWrapper2 = $FACTORIES::getTaskWrapperFactory()->get($task2->getTaskWrapperId());
    if ($taskWrapper1->getPriority() > $taskWrapper2->getPriority()) {
      return $task1; // if first task wrapper has more priority, this task should be done
    }
    return $task2;
  }

  /**
   * @param $hashlists Hashlist[]
   */
  public static function depriorizeAllTasks($hashlists) {
    global $FACTORIES;

    $qF = new ContainFilter(TaskWrapper::HASHLIST_ID, Util::arrayOfIds($hashlists));
    $uS = new UpdateSet(TaskWrapper::PRIORITY, 0);
    $FACTORIES::getTaskWrapperFactory()->massUpdate(array($FACTORIES::FILTER => $qF, $FACTORIES::UPDATE => $uS));
    $taskWrappers = $FACTORIES::getTaskWrapperFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach ($taskWrappers as $tW) {
      $tW->setPriority(0);
      $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $tW->getId(), "=");
      $uS = new UpdateSet(Task::PRIORITY, 0);
      $FACTORIES::getTaskFactory()->massUpdate(array($FACTORIES::FILTER => $qF, $FACTORIES::UPDATE => $uS));
    }
  }

  /**
   * @param $task Task
   * @return File[]
   */
  public static function getFilesOfTask($task) {
    global $FACTORIES;

    $qF = new QueryFilter(FileTask::TASK_ID, $task->getId(), "=", $FACTORIES::getFileTaskFactory());
    $jF = new JoinFilter($FACTORIES::getFileTaskFactory(), File::FILE_ID, FileTask::FILE_ID);
    $joined = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    /** @var $files File[] */
    return $joined[$FACTORIES::getFileFactory()->getModelName()];
  }
}