<?php

use DBA\AccessGroup;
use DBA\AccessGroupAgent;
use DBA\Agent;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ContainFilter;
use DBA\CrackerBinary;
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
use DBA\FilePretask;
use DBA\Pretask;
use DBA\User;
use DBA\Hashlist;
use DBA\AccessGroupUser;
use DBA\TaskDebugOutput;
use DBA\Factory;
use DBA\Speed;

class TaskUtils {
  /**
   * @param Pretask $copy
   * @return Task
   */
  public static function getFromPretask($copy) {
    return new Task(
      null,
      $copy->getTaskName(),
      $copy->getAttackCmd(),
      $copy->getChunkTime(),
      $copy->getStatusTimer(),
      0,
      0,
      $copy->getPriority(),
      $copy->getMaxAgents(),
      $copy->getColor(),
      $copy->getIsSmall(),
      $copy->getIsCpuTask(),
      $copy->getUseNewBench(),
      0,
      0,
      $copy->getCrackerBinaryTypeId(),
      0,
      0,
      '',
      0,
      0,
      0,
      0,
      ''
    );
  }
  
  /**
   * @return Task
   */
  public static function getDefault() {
    return new Task(
      null,
      "",
      "",
      SConfig::getInstance()->getVal(DConfig::CHUNK_DURATION),
      SConfig::getInstance()->getVal(DConfig::STATUS_TIMER),
      0,
      0,
      0,
      0,
      "",
      0,
      0,
      SConfig::getInstance()->getVal(DConfig::DEFAULT_BENCH),
      0,
      0,
      0,
      0,
      0,
      '',
      0,
      0,
      0,
      0,
      ''
    );
  }
  
  /**
   * @param int $taskId
   * @param string $notes
   * @param User $user
   * @throws HTException
   */
  public static function editNotes($taskId, $notes, $user) {
    $task = TaskUtils::getTask($taskId, $user);
    Factory::getTaskFactory()->set($task, Task::NOTES, $notes);
  }
  
  /**
   * @param User $user
   */
  public static function deleteArchived($user) {
    $accessGroups = AccessUtils::getAccessGroupsOfUser($user);
    $qF1 = new QueryFilter(TaskWrapper::IS_ARCHIVED, 1, "=");
    $qF2 = new ContainFilter(TaskWrapper::ACCESS_GROUP_ID, Util::arrayOfIds($accessGroups));
    $taskWrappers = Factory::getTaskWrapperFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    foreach ($taskWrappers as $taskWrapper) {
      Factory::getAgentFactory()->getDB()->beginTransaction();
      $tasks = TaskUtils::getTasksOfWrapper($taskWrapper->getId());
      foreach ($tasks as $task) {
        TaskUtils::deleteTask($task);
      }
      Factory::getTaskWrapperFactory()->delete($taskWrapper);
      Factory::getAgentFactory()->getDB()->commit();
    }
  }
  
  /**
   * @param int $taskId
   * @param string $attackCmd
   * @param User $user
   * @return void
   * @throws HTException
   */
  public static function changeAttackCmd($taskId, $attackCmd, $user) {
    if (strlen($attackCmd) == 0) {
      throw new HTException("Attack command cannot be empty!");
    }
    else if (strpos($attackCmd, SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS)) === false) {
      throw new HTException("Attack command must contain the hashlist alias!");
    }
    else if (Util::containsBlacklistedChars($attackCmd)) {
      throw new HTException("The attack command must contain no blacklisted characters!");
    }
    
    $task = TaskUtils::getTask($taskId, $user);
    if ($task->getAttackCmd() == $attackCmd) {
      // no change required, we avoid all the overhead
      return;
    }
    TaskUtils::purgeTask($task->getId(), $user);
    $task = TaskUtils::getTask($taskId, $user); // reload task, otherwise we overwrite purge changes
    Factory::getTaskFactory()->set($task, Task::ATTACK_CMD, $attackCmd);
  }
  
  /**
   * @param int $supertaskId
   * @param User $user
   * @throws HTException
   */
  public static function archiveSupertask($supertaskId, $user) {
    $taskWrapper = TaskUtils::getTaskWrapper($supertaskId, $user);
    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
    $uS = new UpdateSet(Task::IS_ARCHIVED, 1);
    Factory::getTaskFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
    Factory::getTaskWrapperFactory()->set($taskWrapper, TaskWrapper::IS_ARCHIVED, 1);
  }
  
  /**
   * @param int $taskId
   * @param User $user
   * @throws HTException
   */
  public static function archiveTask($taskId, $user) {
    $task = TaskUtils::getTask($taskId, $user);
    $taskWrapper = TaskUtils::getTaskWrapper($task->getTaskWrapperId(), $user);
    if ($taskWrapper->getTaskType() == DTaskTypes::NORMAL) {
      Factory::getTaskWrapperFactory()->set($taskWrapper, TaskWrapper::IS_ARCHIVED, 1);
    }
    Factory::getTaskFactory()->set($task, Task::IS_ARCHIVED, 1);
  }
  
  /**
   * @param int $taskWrapperId
   * @param string $newName
   * @param User $user
   * @throws HTException
   */
  public static function renameSupertask($taskWrapperId, $newName, $user) {
    $taskWrapper = TaskUtils::getTaskWrapper($taskWrapperId, $user);
    Factory::getTaskWrapperFactory()->set($taskWrapper, TaskWrapper::TASK_WRAPPER_NAME, $newName);
  }
  
  /**
   * @param int $taskWrapperId
   * @return Task
   */
  public static function getTaskOfWrapper($taskWrapperId) {
    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapperId, "=");
    return Factory::getTaskFactory()->filter([Factory::FILTER => $qF], true);
  }
  
  /**
   * @param int $taskWrapperId
   * @return Task[]
   */
  public static function getTasksOfWrapper($taskWrapperId) {
    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapperId, "=");
    return Factory::getTaskFactory()->filter([Factory::FILTER => $qF]);
  }
  
  /**
   * @param User $user
   * @return TaskWrapper[]
   */
  public static function getTaskWrappersForUser($user) {
    $accessGroupIds = Util::getAccessGroupIds($user->getId());
    
    $qF = new ContainFilter(TaskWrapper::ACCESS_GROUP_ID, $accessGroupIds);
    $oF1 = new OrderFilter(TaskWrapper::PRIORITY, "DESC");
    $oF2 = new OrderFilter(TaskWrapper::TASK_WRAPPER_ID, "DESC");
    return Factory::getTaskWrapperFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => [$oF1, $oF2]]);
  }
  
  /**
   * @param int $taskId
   * @return Assignment[]
   */
  public static function getAssignments($taskId) {
    $qF = new QueryFilter(Assignment::TASK_ID, $taskId, "=");
    return Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF]);
  }
  
  /**
   * @param int $taskId
   * @return Chunk[]
   */
  public static function getChunks($taskId) {
    $qF = new QueryFilter(Chunk::TASK_ID, $taskId, "=");
    $oF = new OrderFilter(Chunk::DISPATCH_TIME, "DESC");
    return Factory::getChunkFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
  }
  
  /**
   * @param int $taskWrapperId
   * @param User $user
   * @return TaskWrapper
   * @throws HTException
   */
  public static function getTaskWrapper($taskWrapperId, $user) {
    $taskWrapper = Factory::getTaskWrapperFactory()->get($taskWrapperId);
    if ($taskWrapper == null) {
      throw new HTException("Invalid taskWrapper ID!");
    }
    else if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    return $taskWrapper;
  }
  
  /**
   * @param int $taskId
   * @param User $user
   * @return Task
   * @throws HTException
   */
  public static function getTask($taskId, $user) {
    $task = Factory::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HTException("Invalid task ID!");
    }
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    return $task;
  }
  
  /**
   * @param Pretask $pretask
   * @param Task $task
   */
  public static function copyPretaskFiles($pretask, $task) {
    $qF = new QueryFilter(FilePretask::PRETASK_ID, $pretask->getId(), "=");
    $pretaskFiles = Factory::getFilePretaskFactory()->filter([Factory::FILTER => $qF]);
    $subTasks[] = $task;
    foreach ($pretaskFiles as $pretaskFile) {
      $fileTask = new FileTask(null, $pretaskFile->getFileId(), $task->getId());
      Factory::getFileTaskFactory()->save($fileTask);
      FileDownloadUtils::addDownload($fileTask->getFileId());
    }
  }
  
  /**
   * @param int $supertaskId
   * @param int $priority
   * @param User $user
   * @param bool $topPriority
   * @throws HTException
   */
  public static function setSupertaskPriority($supertaskId, $priority, $user, $topPriority = false) {
    // note that supertaskId here corresponds with the taskwrapper Id of the underlying subtasks of the running supertask
    $supertaskWrapper = TaskUtils::getTaskWrapper($supertaskId, $user);
    if ($supertaskWrapper === null) {
      throw new HTException("Invalid supertask!");
    }
    else if (!AccessUtils::userCanAccessTask($supertaskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    $priority = self::getIntegerPriorityValue($priority, $topPriority, $user, $supertaskWrapper);
    Factory::getTaskWrapperFactory()->set($supertaskWrapper, TaskWrapper::PRIORITY, $priority);
  }
  
  /**
   * @param int $priority
   * @param bool $topPriority
   * @param $user
   * @param $taskWrapper
   * @return int
   */
  public static function getIntegerPriorityValue($priority, $topPriority, $user, $taskWrapper) {
    if ($topPriority) {
      // determine the current highest priority of all tasks this user has access to
      $auxTaskWrappers = TaskUtils::getTaskWrappersForUser($user);
      $highestPriority = 0;
      foreach ($auxTaskWrappers as $auxTaskWrapper) {
        if ($auxTaskWrapper != $taskWrapper) {
          if ($auxTaskWrapper->getPriority() > $highestPriority) {
            $highestPriority = $auxTaskWrapper->getPriority();
          }
        }
      }
      // set task priority to the current highest priority plus one hundred
      $priority = $highestPriority + 100;
    }
    else {
      $priority = intval($priority);
      $priority = ($priority < 0) ? 0 : $priority;
    }
    return $priority;
  }
  
  /**
   * @param int $taskId
   * @param int $isCpuOnly
   * @param User $user
   * @throws HTException
   */
  public static function setCpuTask($taskId, $isCpuOnly, $user) {
    $task = Factory::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HTException("No such task!");
    }
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    $isCpuTask = intval($isCpuOnly);
    if ($isCpuTask != 0 && $isCpuTask != 1) {
      $isCpuTask = 0;
    }
    Factory::getTaskFactory()->set($task, Task::IS_CPU_TASK, $isCpuTask);
  }
  
  /**
   * @param User $user
   */
  public static function deleteFinished($user) {
    // check every task wrapper (non-archived ones)
    $qF = new QueryFilter(TaskWrapper::IS_ARCHIVED, 0, "=");
    $taskWrappers = Factory::getTaskWrapperFactory()->filter([Factory::FILTER => $qF]);
    foreach ($taskWrappers as $taskWrapper) {
      if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
        continue; // we only delete finished ones where the user has access to
      }
      $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
      $tasks = Factory::getTaskFactory()->filter([Factory::FILTER => $qF]);
      $isComplete = true;
      foreach ($tasks as $task) {
        if ($task->getKeyspace() == 0 || $task->getKeyspace() > $task->getKeyspaceProgress()) {
          $isComplete = false;
          break;
        }
        $sumProg = 0;
        $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
        $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
        foreach ($chunks as $chunk) {
          $sumProg += $chunk->getCheckpoint() - $chunk->getSkip();
        }
        if ($sumProg < $task->getKeyspace()) {
          $isComplete = false;
          break;
        }
      }
      if ($isComplete) {
        Factory::getAgentFactory()->getDB()->beginTransaction();
        foreach ($tasks as $task) {
          TaskUtils::deleteTask($task);
        }
        Factory::getTaskWrapperFactory()->delete($taskWrapper);
        Factory::getAgentFactory()->getDB()->commit();
      }
    }
  }
  
  /**
   * @param int $taskId
   * @param int $chunkTime
   * @param User $user
   * @throws HTException
   */
  public static function changeChunkTime($taskId, $chunkTime, $user) {
    // update task chunk time
    $task = Factory::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HTException("No such task!");
    }
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    $chunktime = intval($chunkTime);
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=", Factory::getTaskFactory());
    $jF = new JoinFilter(Factory::getTaskFactory(), Task::TASK_ID, Assignment::TASK_ID);
    $join = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $assignments Assignment[] */
    $assignments = $join[Factory::getAssignmentFactory()->getModelName()];
    foreach ($assignments as $assignment) {
      if ($task->getUseNewBench() == 0) {
        Factory::getAssignmentFactory()->set($assignment, Assignment::BENCHMARK, $assignment->getBenchmark() / $task->getChunkTime() * $chunktime);
      }
    }
    $task->setChunkTime($chunktime);
    Factory::getTaskFactory()->update($task);
    Factory::getAgentFactory()->getDB()->commit();
  }
  
  /**
   * @param int $chunkId
   * @param User $user
   * @throws HTException
   */
  public static function abortChunk($chunkId, $user) {
    // reset chunk state and progress to zero
    $chunk = Factory::getChunkFactory()->get($chunkId);
    if ($chunk == null) {
      throw new HTException("No such chunk!");
    }
    $task = Factory::getTaskFactory()->get($chunk->getTaskId());
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    Factory::getChunkFactory()->set($chunk, Chunk::STATE, DHashcatStatus::ABORTED);
  }
  
  /**
   * @param int $chunkId
   * @param User $user
   * @throws HTException
   */
  public static function resetChunk($chunkId, $user) {
    // reset chunk state and progress to zero
    $chunk = Factory::getChunkFactory()->get($chunkId);
    if ($chunk == null) {
      throw new HTException("No such chunk!");
    }
    $task = Factory::getTaskFactory()->get($chunk->getTaskId());
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    $initialProgress = ($task->getUsePreprocessor() || $task->getForcePipe()) ? null : 0;
    Factory::getChunkFactory()->mset($chunk, [
        Chunk::STATE => DHashcatStatus::INIT,
        Chunk::PROGRESS => $initialProgress,
        Chunk::CHECKPOINT => $chunk->getSkip(),
        Chunk::DISPATCH_TIME => 0,
        Chunk::SOLVE_TIME => 0
      ]
    );
  }
  
  /**
   * @param int $agentId
   * @param string $benchmark
   * @param User $user
   * @throws HTException
   */
  public static function setBenchmark($agentId, $benchmark, $user) {
    // adjust agent benchmark
    $qF = new QueryFilter(Assignment::AGENT_ID, $agentId, "=");
    $assignment = Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF], true);
    if ($assignment == null) {
      throw new HTException("No assignment for this agent!");
    }
    else if (!AccessUtils::userCanAccessAgent(Factory::getAgentFactory()->get($agentId), $user)) {
      throw new HTException("No access to this agent!");
    }
    // TODO: check benchmark validity
    Factory::getAssignmentFactory()->set($assignment, Assignment::BENCHMARK, $benchmark);
  }
  
  /**
   * @param int $taskId
   * @param User $user
   * @throws HTException
   */
  public static function purgeTask($taskId, $user) {
    // delete all task chunks, forget its keyspace value and reset progress to zero
    $task = Factory::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HTException("No such task!");
    }
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $uS = new UpdateSet(Assignment::BENCHMARK, 0);
    Factory::getAssignmentFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
    $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
    $chunkIds = array();
    foreach ($chunks as $chunk) {
      $chunkIds[] = $chunk->getId();
    }
    if (sizeof($chunkIds) > 0) {
      $qF2 = new ContainFilter(Hash::CHUNK_ID, $chunkIds);
      $uS = new UpdateSet(Hash::CHUNK_ID, null);
      Factory::getHashFactory()->massUpdate([Factory::FILTER => $qF2, Factory::UPDATE => $uS]);
      Factory::getHashBinaryFactory()->massUpdate([Factory::FILTER => $qF2, Factory::UPDATE => $uS]);
    }
    Factory::getChunkFactory()->massDeletion([Factory::FILTER => $qF]);
    Factory::getTaskFactory()->mset($task, [Task::KEYSPACE => 0, Task::KEYSPACE_PROGRESS => 0]);
    Factory::getAgentFactory()->getDB()->commit();
  }
  
  /**
   * @param int $taskId
   * @param User $user
   * @param boolean $api
   * @throws HTException
   */
  public static function delete($taskId, $user, $api = false) {
    // delete a task
    $task = Factory::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HTException("No such task!");
    }
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    
    Factory::getAgentFactory()->getDB()->beginTransaction();
    
    $payload = new DataSet(array(DPayloadKeys::TASK => $task));
    NotificationHandler::checkNotifications(DNotificationType::DELETE_TASK, $payload);
    
    TaskUtils::deleteTask($task);
    if ($taskWrapper->getTaskType() != DTaskTypes::SUPERTASK) {
      Factory::getTaskWrapperFactory()->delete($taskWrapper);
    }
    Factory::getAgentFactory()->getDB()->commit();
    if (!$api) {
      header("Location: tasks.php");
      die();
    }
  }
  
  /**
   * @param int $taskId
   * @param int $isSmall
   * @param User $user
   * @throws HTException
   */
  public static function setSmallTask($taskId, $isSmall, $user) {
    $task = TaskUtils::getTask($taskId, $user);
    $isSmall = intval($isSmall);
    if ($isSmall != 0 && $isSmall != 1) {
      $isSmall = 0;
    }
    Factory::getTaskFactory()->set($task, Task::IS_SMALL, $isSmall);
  }
  
  /**
   * @param int $taskId
   * @param int $maxAgents
   * @param User $user
   * @throws HTException
   */
  public static function setTaskMaxAgents($taskId, $maxAgents, $user) {
    $task = TaskUtils::getTask($taskId, $user);
    $taskWrapper = TaskUtils::getTaskWrapper($task->getTaskWrapperId(), $user);
    $maxAgents = intval($maxAgents);
    Factory::getTaskFactory()->set($task, Task::MAX_AGENTS, $maxAgents);
    if ($taskWrapper->getTaskType() != DTaskTypes::SUPERTASK) {
      Factory::getTaskWrapperFactory()->set($taskWrapper, TaskWrapper::MAX_AGENTS, $maxAgents);
    }
  }

  /**
   * @param int $superTaskId
   * @param int $maxAgents
   * @param User $user
   * @throws HTException
   */
  public static function setSuperTaskMaxAgents($superTaskId, $maxAgents, $user) {
    $taskWrapper = TaskUtils::getTaskWrapper($superTaskId, $user);
    $maxAgents = intval($maxAgents);
    Factory::getTaskWrapperFactory()->set($taskWrapper, TaskWrapper::MAX_AGENTS, $maxAgents);
  }

  /**
   * @param int $taskId
   * @param string $color
   * @param User $user
   * @throws HTException
   */
  public static function updateColor($taskId, $color, $user) {
    // change task color
    $task = TaskUtils::getTask($taskId, $user);
    if (preg_match("/[0-9A-Za-z]{6}/", $color) == 0) {
      $color = null;
    }
    Factory::getTaskFactory()->set($task, Task::COLOR, $color);
  }
  
  /**
   * @param int $taskId
   * @param string $name
   * @param User $user
   * @throws HTException
   */
  public static function rename($taskId, $name, $user) {
    // change task name
    $task = TaskUtils::getTask($taskId, $user);
    Factory::getTaskFactory()->set($task, Task::TASK_NAME, $name);
  }
  
  /**
   * @param int $taskId
   * @param int $statusTimer
   * @param User $user
   * @throws HTException
   */
  public static function updateStatusTimer($taskId, $statusTimer, $user) {
    // change the statusTimer value, the interval in seconds clients should report back to the server
    $task = TaskUtils::getTask($taskId, $user);
    if ($task == null) {
      throw new HTException("No such task!");
    }
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    $statusTimer = intval($statusTimer);
    
    if ($statusTimer <= 0 || !is_numeric($statusTimer)) {
      throw new HTException("Invalid status interval!");
    }
    if ($statusTimer > $task->getChunkTime()) {
      throw new HTException("Chunk time must be higher than status timer!");
    }
    
    Factory::getTaskFactory()->set($task, Task::STATUS_TIMER, $statusTimer);
  }
  
  /**
   * @param int $taskId
   * @param int $priority
   * @param User $user
   * @param bool $top
   * @throws HTException
   */
  public static function updatePriority($taskId, $priority, $user, $topPriority = false) {
    // change task priority
    $task = TaskUtils::getTask($taskId, $user);
    $taskWrapper = TaskUtils::getTaskWrapper($task->getTaskWrapperId(), $user);
    $priority = self::getIntegerPriorityValue($priority, $topPriority, $user, $taskWrapper);
    Factory::getTaskFactory()->set($task, Task::PRIORITY, $priority);
    if ($taskWrapper->getTaskType() != DTaskTypes::SUPERTASK) {
      Factory::getTaskWrapperFactory()->set($taskWrapper, TaskWrapper::PRIORITY, $priority);
    }
  }
  
  /**
   * @param int $taskId
   * @param int $maxAgents
   * @param User $user
   * @throws HTException
   */
  public static function updateMaxAgents($taskId, $maxAgents, $user) {
    $task = TaskUtils::getTask($taskId, $user);
    if ($task == null) {
      throw new HTException("No such task!");
    }
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    if (!is_numeric($maxAgents)) {
      throw new HTException("Invalid number of agents!");
    }
    $maxAgents = intval($maxAgents);
    if ($maxAgents < 0) {
      throw new HTException("Invalid number of agents!");
    }
    if ($taskWrapper->getTaskType() != DTaskTypes::SUPERTASK) {
      Factory::getTaskWrapperFactory()->set($taskWrapper, TaskWrapper::MAX_AGENTS, $maxAgents);
    }
    Factory::getTaskFactory()->set($task, Task::MAX_AGENTS, $maxAgents);
  }
  
  /**
   * @param int $hashlistId
   * @param string $name
   * @param string $attackCmd
   * @param int $chunkTime
   * @param int $status
   * @param string $benchtype
   * @param string $color
   * @param boolean $isCpuOnly
   * @param boolean $isSmall
   * @param $usePreprocessor
   * @param $preprocessorCommand
   * @param int $skip
   * @param int $priority
   * @param int $maxAgents
   * @param int[] $files
   * @param int $crackerVersionId
   * @param User $user
   * @param string $notes
   * @param int $staticChunking
   * @param int $chunkSize
   * @return Task
   * @throws HTException
   */
  public static function createTask($hashlistId, $name, $attackCmd, $chunkTime, $status, $benchtype, $color, $isCpuOnly, $isSmall, $usePreprocessor, $preprocessorCommand, $skip, $priority, $maxAgents, $files, $crackerVersionId, $user, $notes = "", $staticChunking = DTaskStaticChunking::NORMAL, $chunkSize = 0) {
    $hashlist = Factory::getHashlistFactory()->get($hashlistId);
    if ($hashlist == null) {
      throw new HTException("Invalid hashlist ID!");
    }
    else if ($hashlist->getIsArchived()) {
      throw new HTException("You cannot create a task for an archived hashlist!");
    }
    
    if (strlen($name) == 0) {
      $name = "Task_" . $hashlist->getId() . "_" . date("Ymd_Hi");
    }
    $accessGroup = Factory::getAccessGroupFactory()->get($hashlist->getAccessGroupId());
    $qF1 = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $accessGroup->getId(), "=");
    $qF2 = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), "=");
    $accessGroupUser = Factory::getAccessGroupUserFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($accessGroupUser == null) {
      throw new HTException("You have no access to this hashlist!");
    }
    $cracker = Factory::getCrackerBinaryFactory()->get($crackerVersionId);
    if ($cracker == null) {
      throw new HTException("Invalid cracker ID!");
    }
    else if (strpos($attackCmd, SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS)) === false) {
      throw new HTException("Attack command does not contain hashlist alias!");
    }
    else if (strlen($attackCmd) > 65535) {
      throw new HTException("Attack command is too long (max 65535 characters)!");
    }
    else if ($staticChunking < DTaskStaticChunking::NORMAL || $staticChunking > DTaskStaticChunking::NUM_CHUNKS) {
      throw new HTException("Invalid static chunk setting!");
    }
    else if ($staticChunking > DTaskStaticChunking::NORMAL && $chunkSize <= 0) {
      throw new HTException("Invalid chunk size / number of chunks for static chunking!");
    }
    else if (Util::containsBlacklistedChars($attackCmd)) {
      throw new HTException("Attack command contains blacklisted characters!");
    }
    else if (Util::containsBlacklistedChars($preprocessorCommand)) {
      throw new HTException("Preprocessor command contains blacklisted characters!");
    }
    else if (!is_numeric($chunkTime) || $chunkTime < 1) {
      throw new HTException("Invalid chunk size!");
    }
    else if (!is_numeric($status) || $status < 1) {
      throw new HTException("Invalid status timer!");
    }
    else if ($benchtype != 'speed' && $benchtype != 'runtime') {
      throw new HTException("Invalid benchmark type!");
    }
    $benchtype = ($benchtype == 'speed') ? 1 : 0;
    if (preg_match("/[0-9A-Za-z]{6}/", $color) != 1) {
      $color = null;
    }
    $isCpuOnly = ($isCpuOnly) ? 1 : 0;
    $isSmall = ($isSmall) ? 1 : 0;
    if ($usePreprocessor < 0) {
      $usePreprocessor = 0;
    }
    else if ($usePreprocessor > 0) {
      $preprocessor = PreprocessorUtils::getPreprocessor($usePreprocessor);
    }
    if ($skip < 0) {
      $skip = 0;
    }
    if ($priority < 0) {
      $priority = 0;
    }
    if ($usePreprocessor && $benchtype == 'runtime') {
      // enforce speed benchmark type when using PRINCE
      $benchtype = 'speed';
    }
    
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $taskWrapper = new TaskWrapper(null, $priority, $maxAgents, DTaskTypes::NORMAL, $hashlist->getId(), $accessGroup->getId(), "", 0, 0);
    $taskWrapper = Factory::getTaskWrapperFactory()->save($taskWrapper);
    
    $task = new Task(
      null,
      $name,
      $attackCmd,
      $chunkTime,
      $status,
      0,
      0,
      $priority,
      $maxAgents,
      $color,
      $isSmall,
      $isCpuOnly,
      $benchtype,
      $skip,
      $cracker->getId(),
      $cracker->getCrackerBinaryTypeId(),
      $taskWrapper->getId(),
      0,
      $notes,
      $staticChunking,
      $chunkSize,
      0,
      ($usePreprocessor > 0) ? $preprocessor->getId() : 0,
      ($usePreprocessor > 0) ? $preprocessorCommand : ''
    );
    $task = Factory::getTaskFactory()->save($task);
    
    if (is_array($files) && sizeof($files) > 0) {
      foreach ($files as $fileId) {
        $taskFile = new FileTask(null, $fileId, $task->getId());
        Factory::getFileTaskFactory()->save($taskFile);
        FileDownloadUtils::addDownload($taskFile->getFileId());
      }
    }
    Factory::getAgentFactory()->getDB()->commit();
    
    $payload = new DataSet(array(DPayloadKeys::TASK => $task));
    NotificationHandler::checkNotifications(DNotificationType::NEW_TASK, $payload);
    return $task;
  }
  
  /**
   * Splits a given task into subtasks within a supertask by splitting the rule file
   * @param Task $task
   * @param TaskWrapper $taskWrapper
   * @param File[] $files
   * @param File $splitFile
   * @param array $split
   */
  public static function splitByRules($task, $taskWrapper, $files, $splitFile, $split) {
    // calculate how much we need to split
    $numSplits = floor($split[1] / 1000 / $task->getChunkTime());
    // replace countLines with fileLineCount? Could be a better option: not OS-dependent
    $numLines = Util::countLines(Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $splitFile->getFilename());
    $linesPerFile = floor($numLines / $numSplits) + 1;
    
    // create the temporary rule files
    $newFiles = [];
    $content = explode("\n", str_replace("\r\n", "\n", file_get_contents(Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $splitFile->getFilename())));
    $count = 0;
    $taskId = $task->getId();
    for ($i = 0; $i < $numLines; $i += $linesPerFile, $count++) {
      $copy = [];
      for ($j = $i; $j < $i + $linesPerFile && $j < sizeof($content); $j++) {
        $copy[] = $content[$j];
      }
      $filename = $splitFile->getFilename() . "_p$taskId-$count";
      $path = Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $splitFile->getFilename() . "_p$taskId-$count";
      file_put_contents($path, implode("\n", $copy) . "\n");
      $f = new File(null, $filename, Util::filesize($path), $splitFile->getIsSecret(), DFileType::TEMPORARY, $taskWrapper->getAccessGroupId(), Util::fileLineCount($path));
      $f = Factory::getFileFactory()->save($f);
      $newFiles[] = $f;
    }
    
    // take out the split file from the file list
    for ($i = 0; $i < sizeof($files); $i++) {
      if ($files[$i]->getId() == $splitFile->getId()) {
        unset($files[$i]);
        break;
      }
    }
    
    // create new tasks as supertask
    $newWrapper = new TaskWrapper(null, 0, 0, DTaskTypes::SUPERTASK, $taskWrapper->getHashlistId(), $taskWrapper->getAccessGroupId(), $task->getTaskName() . " (From Rule Split)", 0, 0);
    $newWrapper = Factory::getTaskWrapperFactory()->save($newWrapper);
    $prio = sizeof($newFiles) + 1;
    foreach ($newFiles as $newFile) {
      $newTask = new Task(null,
        "Part " . (sizeof($newFiles) + 2 - $prio),
        str_replace($splitFile->getFilename(), $newFile->getFilename(), $task->getAttackCmd()),
        $task->getChunkTime(),
        $task->getStatusTimer(),
        0,
        0,
        $prio,
        $task->getMaxAgents(),
        $task->getColor(),
        (SConfig::getInstance()->getVal(DConfig::RULE_SPLIT_SMALL_TASKS) == 0) ? 0 : 1,
        $task->getIsCpuTask(),
        $task->getUseNewBench(),
        $task->getSkipKeyspace(),
        $task->getCrackerBinaryId(),
        $task->getCrackerBinaryTypeId(),
        $newWrapper->getId(),
        0,
        '',
        0,
        0,
        0,
        0,
        ''
      );
      $newTask = Factory::getTaskFactory()->save($newTask);
      $taskFiles = [];
      $taskFiles[] = new FileTask(null, $newFile->getId(), $newTask->getId());
      foreach ($files as $f) {
        $taskFiles[] = new FileTask(null, $f->getId(), $newTask->getId());
        FileDownloadUtils::addDownload($f->getId());
      }
      Factory::getFileTaskFactory()->massSave($taskFiles);
      $prio--;
    }
    $newWrapper->setPriority($taskWrapper->getPriority());
    $newWrapper->setMaxAgents($taskWrapper->getMaxAgents());
    Factory::getTaskWrapperFactory()->update($newWrapper);
    
    // cleanup
    TaskUtils::deleteTask($task);
    Factory::getTaskWrapperFactory()->delete($taskWrapper);
  }
  
  /**
   * @param $agent Agent
   * @param bool $all set true to get all matching tasks for this agent
   * @return Task|Task[]
   */
  public static function getBestTask($agent, $all = false) {
    $allTasks = array();
    
    // load all groups where this agent has access to
    $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=", Factory::getAccessGroupAgentFactory());
    $jF = new JoinFilter(Factory::getAccessGroupAgentFactory(), AccessGroup::ACCESS_GROUP_ID, AccessGroupAgent::ACCESS_GROUP_ID);
    $joined = Factory::getAccessGroupFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $accessGroupAgent AccessGroup[] */
    $accessGroupAgent = $joined[Factory::getAccessGroupFactory()->getModelName()];
    $accessGroups = Util::arrayOfIds($accessGroupAgent);
    
    // get all TaskWrappers which we have access to
    $qF1 = new ContainFilter(TaskWrapper::ACCESS_GROUP_ID, $accessGroups);
    $qF2 = new QueryFilter(TaskWrapper::PRIORITY, 0, (SConfig::getInstance()->getVal(DConfig::PRIORITY_0_START)) ? ">=" : ">");
    $qF3 = new QueryFilter(TaskWrapper::IS_ARCHIVED, 0, "=");
    if ($all) {
      // if we want to retrieve all tasks which are accessible, we also show the ones with 0 priority
      $qF2 = new QueryFilter(TaskWrapper::PRIORITY, 0, ">=");
    }
    $oF = new OrderFilter(TaskWrapper::PRIORITY, "DESC");
    $taskWrappers = Factory::getTaskWrapperFactory()->filter([Factory::FILTER => [$qF1, $qF2, $qF3], Factory::ORDER => $oF]);
    
    // go trough task wrappers and test if we have access
    foreach ($taskWrappers as $taskWrapper) {
      $hashlists = Util::checkSuperHashlist(Factory::getHashlistFactory()->get($taskWrapper->getHashlistId()));
      $permitted = true;
      $fullyCracked = true;
      foreach ($hashlists as $hashlist) {
        if ($hashlist->getIsSecret() > $agent->getIsTrusted()) {
          $permitted = false;
        }
        else if (!in_array($hashlist->getAccessGroupId(), $accessGroups)) {
          $permitted = false;
        }
        else if ($hashlist->getHashCount() > $hashlist->getCracked()) {
          $fullyCracked = false;
        }
      }
      if (!$permitted) {
        continue; // if at least one of the hashlists is secret and the agent not, this taskWrapper cannot be used
      }
      else if ($fullyCracked) {
        continue; // all hashes of this hashlist are cracked, so we continue
      }

      $candidateTasks = self::getCandidateTasks($agent, $accessGroups, $taskWrapper);
      if (!$all && !empty($candidateTasks)) {
        return current($candidateTasks);
      }
      
      // These tasks are available for this user regarding permissions, assignments.
      $allTasks = array_merge($allTasks, $candidateTasks);
    }
    if ($all) {
      return $allTasks;
    }
    return null;
  }

  private static function getCandidateTasks($agent, $accessGroups, $taskWrapper) {
    $totalAssignments = 0;
    $candidateTasks = [];

    // load assigned tasks for this TaskWrapper
    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
    $oF = new OrderFilter(Task::PRIORITY, "DESC");
    $tasks = Factory::getTaskFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
    foreach ($tasks as $task) {
      // count number of other agents already working on the task,
      // no tasks can be candidates if limit is already reached
      $totalAssignments += self::numberOfOtherAssignedAgents($task, $agent);
      if ($taskWrapper->getMaxAgents() > 0 && $totalAssignments >= $taskWrapper->getMaxAgents()) {
        return [];
      }

      // check if it's a small task or maxAgents limits the number of assignments
      if ($task->getIsSmall() == 1 || $task->getMaxAgents() > 0) {
        if (self::isSaturatedByOtherAgents($task, $agent)) {
          continue;
        }
      }

      // check if a task suits to this agent
      $files = TaskUtils::getFilesOfTask($task);
      $permitted = true;
      foreach ($files as $file) {
        if ($file->getIsSecret() > $agent->getIsTrusted()) {
          $permitted = false;
        }
        else if (!in_array($file->getAccessGroupId(), $accessGroups)) {
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

      // check if it's a cpu/gpu task
      if ($task->getIsCpuTask() != $agent->getCpuOnly()) {
        continue;
      }

      // accumulate all candidate tasks
      $candidateTasks[] = $task;
    }
    return $candidateTasks;
  }
  
  /**
   * @param $task Task
   */
  public static function deleteTask($task) {
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $chunkIds = Util::arrayOfIds(Factory::getChunkFactory()->filter([Factory::FILTER => $qF]));
    
    $qF = new QueryFilter(NotificationSetting::OBJECT_ID, $task->getId(), "=");
    $notifications = Factory::getNotificationSettingFactory()->filter([Factory::FILTER => $qF]);
    foreach ($notifications as $notification) {
      if (DNotificationType::getObjectType($notification->getAction()) == DNotificationObjectType::TASK) {
        Factory::getNotificationSettingFactory()->delete($notification);
      }
    }
    
    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    Factory::getAssignmentFactory()->massDeletion([Factory::FILTER => $qF]);
    $qF = new QueryFilter(AgentError::TASK_ID, $task->getId(), "=");
    Factory::getAgentErrorFactory()->massDeletion([Factory::FILTER => $qF]);
    $qF = new QueryFilter(TaskDebugOutput::TASK_ID, $task->getId(), "=");
    Factory::getTaskDebugOutputFactory()->massDeletion([Factory::FILTER => $qF]);
    $qF = new QueryFilter(Speed::TASK_ID, $task->getId(), "=");
    Factory::getSpeedFactory()->massDeletion([Factory::FILTER => $qF]);
    
    // test if this task used temporary files
    $qF = new QueryFilter(FileTask::TASK_ID, $task->getId(), "=", Factory::getFileTaskFactory());
    $jF = new JoinFilter(Factory::getFileTaskFactory(), File::FILE_ID, FileTask::FILE_ID);
    $joined = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    $files = $joined[Factory::getFileFactory()->getModelName()];
    $toDelete = [];
    foreach ($files as $file) {
      /** @var $file File */
      if ($file->getFileType() == DFileType::TEMPORARY) {
        unlink(Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $file->getFilename());
        $toDelete[] = $file;
      }
    }
    Factory::getFileTaskFactory()->massDeletion([Factory::FILTER => $qF]);
    $cF = new ContainFilter(File::FILE_ID, Util::arrayOfIds($toDelete));
    Factory::getFileFactory()->massDeletion([Factory::FILTER => $cF]);
    
    $uS = new UpdateSet(Hash::CHUNK_ID, null);
    if (sizeof($chunkIds) > 0) {
      $qF2 = new ContainFilter(Hash::CHUNK_ID, $chunkIds);
      Factory::getHashFactory()->massUpdate([Factory::FILTER => $qF2, Factory::UPDATE => $uS]);
      Factory::getHashBinaryFactory()->massUpdate([Factory::FILTER => $qF2, Factory::UPDATE => $uS]);
    }
    
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    Factory::getChunkFactory()->massDeletion([Factory::FILTER => $qF]);
    Factory::getTaskFactory()->delete($task);
  }
  
  /**
   * @param int $chunkId
   * @param User $user
   * @return Chunk
   * @throws HTException
   */
  public static function getChunk($chunkId, $user) {
    $chunk = Factory::getChunkFactory()->get($chunkId);
    if ($chunk == null) {
      throw new HTException("Invalid chunk ID!");
    }
    $task = Factory::getTaskFactory()->get($chunk->getTaskId());
    $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to corresponding task!");
    }
    return $chunk;
  }
  
  /**
   * Checks if a task is completed or fully dispatched.
   *
   * @param $task Task
   * @param $agent Agent
   * @return Task null if the task is completed or fully dispatched
   */
  public static function checkTask($task, $agent = null) {
    if ($task->getIsArchived() == 1) {
      return null;
    }
    else if ($task->getKeyspace() == 0) {
      return $task;
    }
    else if ($task->getUsePreprocessor() && $task->getKeyspace() == DPrince::PRINCE_KEYSPACE) {
      return $task;
    }
    
    // check chunks
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
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
      else if (time() - max($chunk->getSolveTime(), $chunk->getDispatchTime()) > SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT)) {
        // this chunk timed out, so we remove the agent from it and therefore this task is not complete yet
        //$chunk->setAgentId(null);
        //Factory::getChunkFactory()->update($chunk);
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
      Factory::getTaskFactory()->set($task, Task::PRIORITY, 0);
      $taskWrapper = Factory::getTaskWrapperFactory()->get($task->getTaskWrapperId());
      if ($taskWrapper->getTaskType() != DTaskTypes::SUPERTASK) {
        Factory::getTaskWrapperFactory()->set($taskWrapper, TaskWrapper::PRIORITY, 0);
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
    $twFilter = new ContainFilter(TaskWrapper::HASHLIST_ID, Util::arrayOfIds($hashlists));
    $taskWrappers = Factory::getTaskWrapperFactory()->filter([Factory::FILTER => $twFilter]);
    foreach ($taskWrappers as $taskWrapper) {
      $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
      $tasks = Factory::getTaskFactory()->filter([Factory::FILTER => $qF]);
      $qF = new ContainFilter(Assignment::TASK_ID, Util::arrayOfIds($tasks));
      Factory::getAssignmentFactory()->massDeletion([Factory::FILTER => $qF]);
    }
    $uS = new UpdateSet(TaskWrapper::PRIORITY, 0);
    Factory::getTaskWrapperFactory()->massUpdate([Factory::FILTER => $twFilter, Factory::UPDATE => $uS]);
  }
  
  /**
   * @param $task1 Task
   * @param $task2 Task
   * @return Task task which should be worked on
   */
  public static function getImportantTask($task1, $task2) {
    if ($task1 == null) {
      return $task2;
    }
    else if ($task2 == null) {
      return $task1;
    }
    
    $taskWrapper1 = Factory::getTaskWrapperFactory()->get($task1->getTaskWrapperId());
    $taskWrapper2 = Factory::getTaskWrapperFactory()->get($task2->getTaskWrapperId());
    if ($taskWrapper1->getPriority() > $taskWrapper2->getPriority()) {
      return $task1; // if first task wrapper has more priority, this task should be done
    }
    else if ($taskWrapper1->getPriority() == $taskWrapper2->getPriority() && $task1->getPriority() > $task2->getPriority()) {
      return $task1; // if both wrappers have the same priority but the subtask not (this can be the case when comparing supertasks)
    }
    return $task2;
  }
  
  /**
   * @param $hashlists Hashlist[]
   */
  public static function depriorizeAllTasks($hashlists) {
    $qF = new ContainFilter(TaskWrapper::HASHLIST_ID, Util::arrayOfIds($hashlists));
    $uS = new UpdateSet(TaskWrapper::PRIORITY, 0);
    Factory::getTaskWrapperFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
    $taskWrappers = Factory::getTaskWrapperFactory()->filter([Factory::FILTER => $qF]);
    foreach ($taskWrappers as $tW) {
      $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $tW->getId(), "=");
      $uS = new UpdateSet(Task::PRIORITY, 0);
      Factory::getTaskFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
    }
  }
  
  /**
   * @param $task Task
   * @return File[]
   */
  public static function getFilesOfTask($task) {
    $qF = new QueryFilter(FileTask::TASK_ID, $task->getId(), "=", Factory::getFileTaskFactory());
    $jF = new JoinFilter(Factory::getFileTaskFactory(), File::FILE_ID, FileTask::FILE_ID);
    $joined = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $files File[] */
    return $joined[Factory::getFileFactory()->getModelName()];
  }
  
  /**
   * @param $pretask Pretask
   * @return File[]
   */
  public static function getFilesOfPretask($pretask) {
    $qF = new QueryFilter(FilePretask::PRETASK_ID, $pretask->getId(), "=", Factory::getFilePretaskFactory());
    $jF = new JoinFilter(Factory::getFilePretaskFactory(), File::FILE_ID, FilePretask::FILE_ID);
    $joined = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    /** @var $files File[] */
    return $joined[Factory::getFileFactory()->getModelName()];
  }
  
  /**
   * @param int $supertaskId
   * @param User $user
   * @throws HTException
   */
  public static function deleteSupertask($supertaskId, $user) {
    $taskWrapper = Factory::getTaskWrapperFactory()->get($supertaskId);
    if ($taskWrapper === null) {
      throw new HTException("Invalid supertask!");
    }
    else if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this supertask!");
    }
    
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
    $tasks = Factory::getTaskFactory()->filter([Factory::FILTER => $qF]);
    foreach ($tasks as $task) {
      TaskUtils::deleteTask($task);
    }
    Factory::getTaskWrapperFactory()->delete($taskWrapper);
    Factory::getAgentFactory()->getDB()->commit();
  }
  
  /**
   * @param $taskId
   * @param $user
   * @return array
   * @throws HTException
   */
  public static function getCrackedHashes($taskId, $user) {
    $task = TaskUtils::getTask($taskId, $user);
    $taskWrapper = TaskUtils::getTaskWrapper($task->getTaskWrapperId(), $user);
    $hashlist = HashlistUtils::getHashlist($taskWrapper->getHashlistId());
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);
    $hashes = [];
    $chunkIds = Util::arrayOfIds($chunks);
    $qF1 = new ContainFilter(Hash::CHUNK_ID, $chunkIds);
    $qF2 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
    $entries = Factory::getHashFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    foreach ($entries as $entry) {
      $arr = [
        "hash" => $entry->getHash(),
        "plain" => $entry->getPlaintext(),
        "crackpos" => $entry->getCrackPos()
      ];
      if (strlen($entry->getSalt()) > 0) {
        $arr["hash"] .= $hashlist->getSaltSeparator() . $entry->getSalt();
      }
      $hashes[] = $arr;
    }
    return $hashes;
  }
  
  /**
   * @param $task Task
   * @return bool
   */
  public static function isFinished($task) {
    return ($task->getKeyspace() > 0 && Util::getTaskInfo($task)[0] >= $task->getKeyspace());
  }
  
  /**
   * @param Task $task
   * @param string $modifier
   * @return string
   */
  public static function getCrackerInfo($task, $modifier = "info") {
    if (AccessControl::getInstance()->hasPermission(DAccessControl::CRACKER_BINARY_ACCESS)) {
      $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_ID, $task->getCrackerBinaryId(), "=");
      $binaries = Factory::getCrackerBinaryFactory()->filter([Factory::FILTER => $qF]);
      foreach ($binaries as $binary) {
        if ($modifier == "info") {
          return "Version: " . $binary->getVersion() . " — Binary Name: " . $binary->getBinaryName();
        }
        elseif ($modifier == "id") {
          return $binary->getCrackerBinaryTypeId();
        }
        else {
          return "Invalid modifier";
        }
      }
      return "No binaries found";
    }
    else {
      return "Access denied";
    }
  }
  
  /**
   * Get the number of agents - apart from given agent -.working on given task.
   *
   * @param $task
   * @param $agent
   * @return int the number of agents working on given task
   */
  public static function numberOfOtherAssignedAgents($task, $agent) {
    $qF1 = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $qF2 = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "<>");
    return  Factory::getAssignmentFactory()->countFilter([Factory::FILTER => [$qF1, $qF2]]);
  }

  /**
   * Check if a task already has enough agents - apart from given agent - working on it,
   * with respect to the 'maxAgents' configuration
   *
   * @param Task $task
   * @param Agent $agent
   * @return boolean true if maxAgents != 0 and number of assigned agents >= maxAgents, false otherwise
   */
  public static function isSaturatedByOtherAgents($task, $agent) {
    $numAssignments = self::numberOfOtherAssignedAgents($task, $agent);
    return ($task->getIsSmall() == 1 && $numAssignments > 0) || // at least one agent is already assigned here
      ($task->getMaxAgents() > 0 && $numAssignments >= $task->getMaxAgents()); // at least maxAgents agents are already assigned
  }
}
