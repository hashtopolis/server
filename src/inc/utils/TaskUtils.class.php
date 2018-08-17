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
use DBA\FilePretask;
use DBA\Pretask;
use DBA\User;
use DBA\Hashlist;
use DBA\AccessGroupUser;
use DBA\TaskDebugOutput;

class TaskUtils {
  /**
   * @param Pretask $copy
   * @return Task
   */
  public static function getFromPretask($copy){
    return new Task(
        0,
        $copy->getTaskName(),
        $copy->getAttackCmd(),
        $copy->getChunkTime(),
        $copy->getStatusTimer(),
        0,
        0,
        $copy->getPriority(),
        $copy->getColor(),
        $copy->getIsSmall(),
        $copy->getIsCpuTask(),
        $copy->getUseNewBench(),
        0,
        0,
        $copy->getCrackerBinaryTypeId(),
        0,
        0,
        0,
        '',
        0,
        0
      );
  }

  /**
   * @return Task
   */
  public static function getDefault(){
    return new Task(
      0,
      "",
      "",
      SConfig::getInstance()->getVal(DConfig::CHUNK_DURATION),
      SConfig::getInstance()->getVal(DConfig::STATUS_TIMER),
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
      0,
      '',
      0,
      0
    );
  }

  /**
   * @param int $taskId
   * @param string $notes
   * @param User $user
   */
  public static function editNotes($taskId, $notes, $user){
    global $FACTORIES;

    $notes = htmlentities($notes, ENT_QUOTES, "UTF-8");
    $task = TaskUtils::getTask($taskId, $user);
    $task->setNotes($notes);
    $FACTORIES::getTaskFactory()->update($task);
  }

  /**
   * @param User $user
   */
  public static function deleteArchived($user) {
    global $FACTORIES;

    $accessGroups = AccessUtils::getAccessGroupsOfUser($user);
    $qF1 = new QueryFilter(TaskWrapper::IS_ARCHIVED, 1, "=");
    $qF2 = new ContainFilter(TaskWrapper::ACCESS_GROUP_ID, Util::arrayOfIds($accessGroups));
    $taskWrappers = $FACTORIES::getTaskWrapperFactory()->filter([$FACTORIES::FILTER => [$qF1, $qF2]]);
    foreach ($taskWrappers as $taskWrapper) {
      $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
      $tasks = TaskUtils::getTasksOfWrapper($taskWrapper->getId());
      foreach ($tasks as $task) {
        TaskUtils::deleteTask($task);
      }
      $FACTORIES::getTaskWrapperFactory()->delete($taskWrapper);
      $FACTORIES::getAgentFactory()->getDB()->commit();
    }
  }

  /**
   * @param int $taskId
   * @param string $attackCmd
   * @param User $user
   * @throws HTException
   * @return void
   */
  public static function changeAttackCmd($taskId, $attackCmd, $user){
    global $FACTORIES;

    if(strlen($attackCmd) == 0){
      throw new HTException("Attack command cannot be empty!");
    }
    else if(strpos($attackCmd, SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS)) === false){
      throw new HTException("Attack command must contain the hashlist alias!");
    }

    $task = TaskUtils::getTask($taskId, $user);
    if($task->getAttackCmd() == $attackCmd){
      // no change required, we avoid all the overhead
      return;
    }
    TaskUtils::purgeTask($task->getId(), $user);
    $task->setAttackCmd($attackCmd);
    $FACTORIES::getTaskFactory()->update($task);
  }

  /**
   * @param int $supertaskId
   * @param User $user
   */
  public static function archiveSupertask($supertaskId, $user){
    global $FACTORIES;

    $taskWrapper = TaskUtils::getTaskWrapper($supertaskId, $user);
    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
    $uS = new UpdateSet(Task::IS_ARCHIVED, 1);
    $FACTORIES::getTaskFactory()->massUpdate(array($FACTORIES::FILTER => $qF, $FACTORIES::UPDATE => $uS));
    $taskWrapper->setIsArchived(1);
    $FACTORIES::getTaskWrapperFactory()->update($taskWrapper);
  }

  /**
   * @param int $taskId
   * @param User $user
   */
  public static function archiveTask($taskId, $user){
    global $FACTORIES;

    $task = TaskUtils::getTask($taskId, $user);
    $taskWrapper = TaskUtils::getTaskWrapper($task->getTaskWrapperId(), $user);
    if($taskWrapper->getTaskType() == DTaskTypes::NORMAL){
      $taskWrapper->setIsArchived(1);
      $FACTORIES::getTaskWrapperFactory()->update($taskWrapper);
    }
    $task->setIsArchived(1);
    $FACTORIES::getTaskFactory()->update($task);
  }

  /**
   * @param int $taskWrapperId
   * @param string $newName
   * @param User $user
   * @throws HTException
   */
  public static function renameSupertask($taskWrapperId, $newName, $user) {
    global $FACTORIES;

    $taskWrapper = TaskUtils::getTaskWrapper($taskWrapperId, $user);
    $name = htmlentities($newName, ENT_QUOTES, "UTF-8");
    $taskWrapper->setTaskWrapperName($name);
    $FACTORIES::getTaskWrapperFactory()->update($taskWrapper);
  }

  /**
   * @param int $taskWrapperId
   * @return Task
   */
  public static function getTaskOfWrapper($taskWrapperId) {
    global $FACTORIES;

    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapperId, "=");
    return $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF), true);
  }

  /**
   * @param int $taskWrapperId
   * @return Task[]
   */
  public static function getTasksOfWrapper($taskWrapperId) {
    global $FACTORIES;

    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapperId, "=");
    return $FACTORIES::getTaskFactory()->filter([$FACTORIES::FILTER => $qF]);
  }

  /**
   * @param User $user
   * @return TaskWrapper[]
   */
  public static function getTaskWrappersForUser($user) {
    global $FACTORIES;

    $accessGroupIds = Util::getAccessGroupIds($user->getId());

    $qF = new ContainFilter(TaskWrapper::ACCESS_GROUP_ID, $accessGroupIds);
    $oF1 = new OrderFilter(TaskWrapper::PRIORITY, "DESC");
    $oF2 = new OrderFilter(TaskWrapper::TASK_WRAPPER_ID, "DESC");
    return $FACTORIES::getTaskWrapperFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => array($oF1, $oF2)));
  }

  /**
   * @param int $taskId
   * @return Assignment[]
   */
  public static function getAssignments($taskId) {
    global $FACTORIES;

    $qF = new QueryFilter(Assignment::TASK_ID, $taskId, "=");
    return $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF));
  }

  /**
   * @param int $taskId
   * @return Chunk[]
   */
  public static function getChunks($taskId) {
    global $FACTORIES;

    $qF = new QueryFilter(Chunk::TASK_ID, $taskId, "=");
    $oF = new OrderFilter(Chunk::DISPATCH_TIME, "DESC");
    return $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF));
  }

  /**
   * @param int $taskWrapperId
   * @param User $user
   * @throws HTException
   * @return TaskWrapper
   */
  public static function getTaskWrapper($taskWrapperId, $user) {
    global $FACTORIES;

    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($taskWrapperId);
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
   * @throws HTException
   * @return Task
   */
  public static function getTask($taskId, $user) {
    global $FACTORIES;

    $task = $FACTORIES::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HTException("Invalid task ID!");
    }
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
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
    global $FACTORIES;

    $qF = new QueryFilter(FilePretask::PRETASK_ID, $pretask->getId(), "=");
    $pretaskFiles = $FACTORIES::getFilePretaskFactory()->filter(array($FACTORIES::FILTER => $qF));
    $subTasks[] = $task;
    foreach ($pretaskFiles as $pretaskFile) {
      $fileTask = new FileTask(0, $pretaskFile->getFileId(), $task->getId());
      $FACTORIES::getFileTaskFactory()->save($fileTask);
    }
  }

  /**
   * @param int $supertaskId
   * @param int $priority
   * @param User $user
   * @throws HTException
   */
  public static function setSupertaskPriority($supertaskId, $priority, $user) {
    global $FACTORIES;

    $supertask = $FACTORIES::getTaskWrapperFactory()->get($supertaskId);
    if ($supertask === null) {
      throw new HTException("Invalid supertask!");
    }
    else if (!AccessUtils::userCanAccessTask($supertask, $user)) {
      throw new HTException("No access to this task!");
    }
    $priority = ($priority < 0) ? 0 : $priority;
    $supertask->setPriority($priority);
    $FACTORIES::getTaskWrapperFactory()->update($supertask);
  }

  /**
   * @param int $taskId
   * @param int $isCpuOnly
   * @param User $user
   * @throws HTException
   */
  public static function setCpuTask($taskId, $isCpuOnly, $user) {
    global $FACTORIES;

    $task = $FACTORIES::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HTException("No such task!");
    }
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    $isCpuTask = intval($isCpuOnly);
    if ($isCpuTask != 0 && $isCpuTask != 1) {
      $isCpuTask = 0;
    }
    $task->setIsCpuTask($isCpuTask);
    $FACTORIES::getTaskFactory()->update($task);
  }

  /**
   * @param User $user
   * @throws HTException
   */
  public static function deleteFinished($user) {
    global $FACTORIES;

    // check every task wrapper (non-archived ones)
    $qF = new QueryFilter(TaskWrapper::IS_ARCHIVED, 0, "=");
    $taskWrappers = $FACTORIES::getTaskWrapperFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach ($taskWrappers as $taskWrapper) {
      if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
        continue; // we only delete finished ones where the user has access to
      }
      $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
      $tasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF));
      $isComplete = true;
      foreach ($tasks as $task) {
        if ($task->getKeyspace() == 0 || $task->getKeyspace() > $task->getKeyspaceProgress()) {
          $isComplete = false;
          break;
        }
        $sumProg = 0;
        $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
        $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
        foreach ($chunks as $chunk) {
          $sumProg += $chunk->getCheckpoint() - $chunk->getSkip();
        }
        if ($sumProg < $task->getKeyspace()) {
          $isComplete = false;
          break;
        }
      }
      if ($isComplete) {
        $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
        foreach ($tasks as $task) {
          TaskUtils::deleteTask($task);
        }
        $FACTORIES::getTaskWrapperFactory()->delete($taskWrapper);
        $FACTORIES::getAgentFactory()->getDB()->commit();
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
    global $FACTORIES;

    // update task chunk time
    $task = $FACTORIES::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HTException("No such task!");
    }
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    $chunktime = intval($chunkTime);
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=", $FACTORIES::getTaskFactory());
    $jF = new JoinFilter($FACTORIES::getTaskFactory(), Task::TASK_ID, Assignment::TASK_ID);
    $join = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    for ($i = 0; $i < sizeof($join[$FACTORIES::getTaskFactory()->getModelName()]); $i++) {
      /** @var $assignment Assignment */
      $assignment = $join[$FACTORIES::getAssignmentFactory()->getModelName()][$i];
      $assignment->setBenchmark($assignment->getBenchmark() / $task->getChunkTime() * $chunktime);
      $FACTORIES::getAssignmentFactory()->update($assignment);
    }
    $task->setChunkTime($chunktime);
    $FACTORIES::getTaskFactory()->update($task);
    $FACTORIES::getAgentFactory()->getDB()->commit();
  }

  /**
   * @param int $chunkId
   * @param User $user
   * @throws HTException
   */
  public static function abortChunk($chunkId, $user) {
    global $FACTORIES;

    // reset chunk state and progress to zero
    $chunk = $FACTORIES::getChunkFactory()->get($chunkId);
    if ($chunk == null) {
      throw new HTException("No such chunk!");
    }
    $task = $FACTORIES::getTaskFactory()->get($chunk->getTaskId());
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    $chunk->setState(DHashcatStatus::ABORTED);
    $FACTORIES::getChunkFactory()->update($chunk);
  }

  /**
   * @param int $chunkId
   * @param User $user
   * @throws HTException
   */
  public static function resetChunk($chunkId, $user) {
    global $FACTORIES;

    // reset chunk state and progress to zero
    $chunk = $FACTORIES::getChunkFactory()->get($chunkId);
    if ($chunk == null) {
      throw new HTException("No such chunk!");
    }
    $task = $FACTORIES::getTaskFactory()->get($chunk->getTaskId());
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    $chunk->setState(0);
    $chunk->setProgress(0);
    $chunk->setCheckpoint($chunk->getSkip());
    $chunk->setDispatchTime(0);
    $chunk->setSolveTime(0);
    $FACTORIES::getChunkFactory()->update($chunk);
  }

  /**
   * @param int $agentId
   * @param string $benchmark
   * @param User $user
   * @throws HTException
   */
  public static function setBenchmark($agentId, $benchmark, $user) {
    global $FACTORIES;

    // adjust agent benchmark
    $qF = new QueryFilter(Assignment::AGENT_ID, $agentId, "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($assignment == null) {
      throw new HTException("No assignment for this agent!");
    }
    else if (!AccessUtils::userCanAccessAgent($FACTORIES::getAgentFactory()->get($agentId), $user)) {
      throw new HTException("No access to this agent!");
    }
    // TODO: check benchmark validity
    $assignment->setBenchmark($benchmark);
    $FACTORIES::getAssignmentFactory()->update($assignment);
  }

  /**
   * @param int $taskId
   * @param User $user
   * @throws HTException
   */
  public static function purgeTask($taskId, $user) {
    global $FACTORIES;

    // delete all task chunks, forget its keyspace value and reset progress to zero
    $task = $FACTORIES::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HTException("No such task!");
    }
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $uS = new UpdateSet(Assignment::BENCHMARK, 0);
    $FACTORIES::getAssignmentFactory()->massUpdate(array($FACTORIES::FILTER => $qF, $FACTORIES::UPDATE => $uS));
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
    $chunkIds = array();
    foreach ($chunks as $chunk) {
      $chunkIds[] = $chunk->getId();
    }
    if (sizeof($chunkIds) > 0) {
      $qF2 = new ContainFilter(Hash::CHUNK_ID, $chunkIds);
      $uS = new UpdateSet(Hash::CHUNK_ID, null);
      $FACTORIES::getHashFactory()->massUpdate(array($FACTORIES::FILTER => $qF2, $FACTORIES::UPDATE => $uS));
      $FACTORIES::getHashBinaryFactory()->massUpdate(array($FACTORIES::FILTER => $qF2, $FACTORIES::UPDATE => $uS));
    }
    $FACTORIES::getChunkFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $task->setKeyspace(0);
    $task->setKeyspaceProgress(0);
    $FACTORIES::getTaskFactory()->update($task);
    $FACTORIES::getAgentFactory()->getDB()->commit();
  }

  /**
   * @param int $taskId
   * @param User $user
   * @param boolean $api
   * @throws HTException
   */
  public static function delete($taskId, $user, $api = false) {
    global $FACTORIES;

    // delete a task
    $task = $FACTORIES::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HTException("No such task!");
    }
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();

    $payload = new DataSet(array(DPayloadKeys::TASK => $task));
    NotificationHandler::checkNotifications(DNotificationType::DELETE_TASK, $payload);

    TaskUtils::deleteTask($task);
    if ($taskWrapper->getTaskType() != DTaskTypes::SUPERTASK) {
      $FACTORIES::getTaskWrapperFactory()->delete($taskWrapper);
    }
    $FACTORIES::getAgentFactory()->getDB()->commit();
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
    global $FACTORIES;

    $task = $FACTORIES::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HTException("No such task!");
    }
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    $isSmall = intval($isSmall);
    if ($isSmall != 0 && $isSmall != 1) {
      $isSmall = 0;
    }
    $task->setIsSmall($isSmall);
    $FACTORIES::getTaskFactory()->update($task);
  }

  /**
   * @param int $taskId
   * @param string $color
   * @param User $user
   * @throws HTException
   */
  public static function updateColor($taskId, $color, $user) {
    global $FACTORIES;

    // change task color
    $task = $FACTORIES::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HTException("No such task!");
    }
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    if (preg_match("/[0-9A-Za-z]{6}/", $color) == 0) {
      $color = null;
    }
    $task->setColor($color);
    $FACTORIES::getTaskFactory()->update($task);
  }

  /**
   * @param int $taskId
   * @param string $name
   * @param User $user
   * @throws HTException
   */
  public static function rename($taskId, $name, $user) {
    global $FACTORIES;

    // change task name
    $task = $FACTORIES::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HTException("No such task!");
    }
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    $name = htmlentities($name, ENT_QUOTES, "UTF-8");
    $task->setTaskName($name);
    $FACTORIES::getTaskFactory()->update($task);
  }

  /**
   * @param int $taskId
   * @param int $priority
   * @param User $user
   * @throws HTException
   */
  public static function updatePriority($taskId, $priority, $user) {
    global $FACTORIES;

    // change task priority
    $task = $FACTORIES::getTaskFactory()->get($taskId);
    if ($task == null) {
      throw new HTException("No such task!");
    }
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this task!");
    }
    $priority = intval($priority);
    $priority = ($priority < 0) ? 0 : $priority;
    $task->setPriority($priority);
    $taskWrapper->setPriority($priority);
    $FACTORIES::getTaskFactory()->update($task);
    if ($taskWrapper->getTaskType() != DTaskTypes::SUPERTASK) {
      $FACTORIES::getTaskWrapperFactory()->update($taskWrapper);
    }
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
   * @param int $skip
   * @param int $priority
   * @param int[] $files
   * @param int $crackerVersionId
   * @param User $user
   * @throws HTException
   */
  public static function createTask($hashlistId, $name, $attackCmd, $chunkTime, $status, $benchtype, $color, $isCpuOnly, $isSmall, $isPrince, $skip, $priority, $files, $crackerVersionId, $user, $notes = "", $staticChunking = DTaskStaticChunking::NORMAL, $chunkSize = 0) {
    global $FACTORIES;

    $hashlist = $FACTORIES::getHashlistFactory()->get($hashlistId);
    if ($hashlist == null) {
      throw new HTException("Invalid hashlist ID!");
    }
    $name = htmlentities($name, ENT_QUOTES, "UTF-8");
    if (strlen($name) == 0) {
      $name = "Task_" . $hashlist->getId() . "_" . date("Ymd_Hi");
    }
    $accessGroup = $FACTORIES::getAccessGroupFactory()->get($hashlist->getAccessGroupId());
    $qF1 = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $accessGroup->getId(), "=");
    $qF2 = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), "=");
    $accessGroupUser = $FACTORIES::getAccessGroupUserFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if ($accessGroupUser == null) {
      throw new HTException("You have no access to this hashlist!");
    }
    $cracker = $FACTORIES::getCrackerBinaryFactory()->get($crackerVersionId);
    if ($cracker == null) {
      throw new HTException("Invalid cracker ID!");
    }
    else if (strpos($attackCmd, SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS)) === false) {
      throw new HTException("Attack command does not contain hashlist alias!");
    }
    else if($staticChunking < DTaskStaticChunking::NORMAL || $staticChunking > DTaskStaticChunking::NUM_CHUNKS){
      throw new HTException("Invalid static chunk setting!");
    }
    else if($staticChunking > DTaskStaticChunking::NORMAL && $chunkSize <= 0){
      throw new HTException("Invalid chunk size / number of chunks for static chunking!");
    }
    else if (Util::containsBlacklistedChars($attackCmd)) {
      throw new HTException("Attack command contains blacklisted characters!");
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
    $isPrince = ($isPrince) ? 1 : 0;
    if ($skip < 0) {
      $skip = 0;
    }
    if ($priority < 0) {
      $priority = 0;
    }
    if($isPrince && $benchtype == 'runtime'){
      // enforce speed benchmark type when using PRINCE
      $benchtype = 'speed';
    }

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $taskWrapper = new TaskWrapper(0, $priority, DTaskTypes::NORMAL, $hashlist->getId(), $accessGroup->getId(), "", 0);
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->save($taskWrapper);

    $task = new Task(
      0,
      $name,
      $attackCmd,
      $chunkTime,
      $status,
      0,
      0,
      $priority,
      $color,
      $isSmall,
      $isCpuOnly,
      $benchtype,
      $skip,
      $cracker->getId(),
      $cracker->getCrackerBinaryTypeId(),
      $taskWrapper->getId(),
      0,
      0,
      $isPrince,
      $notes,
      $staticChunking,
      $chunkSize
    );
    $task = $FACTORIES::getTaskFactory()->save($task);

    if (is_array($files) && sizeof($files) > 0) {
      foreach ($files as $fileId) {
        $taskFile = new FileTask(0, $fileId, $task->getId());
        $FACTORIES::getFileTaskFactory()->save($taskFile);
      }
    }
    $FACTORIES::getAgentFactory()->getDB()->commit();

    $payload = new DataSet(array(DPayloadKeys::TASK => $task));
    NotificationHandler::checkNotifications(DNotificationType::NEW_TASK, $payload);
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
    global $FACTORIES;

    // calculate how much we need to split
    $numSplits = floor($split[1] / 1000 / $task->getChunkTime());
    $numLines = Util::countLines(dirname(__FILE__) . "/../../files/" . $splitFile->getFilename());
    $linesPerFile = floor($numLines / $numSplits) + 1;

    // create the temporary rule files
    $newFiles = [];
    $content = explode("\n", str_replace("\r\n", "\n", file_get_contents(dirname(__FILE__) . "/../../files/" . $splitFile->getFilename())));
    $count = 0;
    $taskId = $task->getId();
    for ($i = 0; $i < $numLines; $i += $linesPerFile, $count++) {
      $copy = [];
      for ($j = $i; $j < $i + $linesPerFile && $j < sizeof($content); $j++) {
        $copy[] = $content[$j];
      }
      file_put_contents(dirname(__FILE__) . "/../../files/" . $splitFile->getFilename() . "_p$taskId-$count", implode("\n", $copy));
      $f = new File(0, $splitFile->getFilename() . "_p$taskId-$count", Util::filesize(dirname(__FILE__) . "/../../files/" . $splitFile->getFilename() . "_p$count"), $splitFile->getIsSecret(), DFileType::TEMPORARY, $taskWrapper->getAccessGroupId());
      $f = $FACTORIES::getFileFactory()->save($f);
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
    $newWrapper = new TaskWrapper(0, 0, DTaskTypes::SUPERTASK, $taskWrapper->getHashlistId(), $taskWrapper->getAccessGroupId(), $task->getTaskName(), 0);
    $newWrapper = $FACTORIES::getTaskWrapperFactory()->save($newWrapper);
    $prio = sizeof($newFiles) + 1;
    foreach ($newFiles as $newFile) {
      $newTask = new Task(0,
        "Part " . (sizeof($newFiles) + 2 - $prio),
        str_replace($splitFile->getFilename(), $newFile->getFilename(), $task->getAttackCmd()),
        $task->getChunkTime(),
        $task->getStatusTimer(),
        0,
        0,
        $prio,
        $task->getColor(),
        (SConfig::getInstance()->getVal(DConfig::RULE_SPLIT_SMALL_TASKS) == 0) ? 0 : 1,
        $task->getIsCpuTask(),
        $task->getUseNewBench(),
        $task->getSkipKeyspace(),
        $task->getCrackerBinaryId(),
        $task->getCrackerBinaryTypeId(),
        $newWrapper->getId(),
        0,
        0,
        '',
        0,
        0
      );
      $newTask = $FACTORIES::getTaskFactory()->save($newTask);
      $taskFiles = [];
      $taskFiles[] = new FileTask(0, $newFile->getId(), $newTask->getId());
      foreach ($files as $f) {
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
    $qF3 = new QueryFilter(TaskWrapper::IS_ARCHIVED, 0, "=");
    if ($all) {
      // if we want to retrieve all tasks which are accessible, we also show the ones with 0 priority
      $qF2 = new QueryFilter(TaskWrapper::PRIORITY, 0, ">=");
    }
    $oF = new OrderFilter(TaskWrapper::PRIORITY, "DESC");
    $taskWrappers = $FACTORIES::getTaskWrapperFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2, $qF3), $FACTORIES::ORDER => $oF));

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
          else if(!in_array($file->getAccessGroupId(), $accessGroups)){
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
  public static function deleteTask($task) {
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
    $qF = new QueryFilter(TaskDebugOutput::TASK_ID, $task->getId(), "=");
    $FACTORIES::getTaskDebugOutputFactory()->massDeletion(array($FACTORIES::FILTER => $qF));

    // test if this task used temporary files
    $qF = new QueryFilter(FileTask::TASK_ID, $task->getId(), "=", $FACTORIES::getFileTaskFactory());
    $jF = new JoinFilter($FACTORIES::getFileTaskFactory(), File::FILE_ID, FileTask::FILE_ID);
    $joined = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    $files = $joined[$FACTORIES::getFileFactory()->getModelName()];
    $toDelete = [];
    foreach ($files as $file) {
      /** @var $file File */
      if ($file->getFileType() == DFileType::TEMPORARY) {
        unlink(dirname(__FILE__) . "/../../files/" . $file->getFilename());
        $toDelete[] = $file;
      }
    }
    $FACTORIES::getFileTaskFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $cF = new ContainFilter(File::FILE_ID, Util::arrayOfIds($toDelete));
    $FACTORIES::getFileFactory()->massDeletion(array($FACTORIES::FILTER => $cF));

    $uS = new UpdateSet(Hash::CHUNK_ID, null);
    if (sizeof($chunkIds) > 0) {
      $qF2 = new ContainFilter(Hash::CHUNK_ID, $chunkIds);
      $FACTORIES::getHashFactory()->massUpdate(array($FACTORIES::FILTER => $qF2, $FACTORIES::UPDATE => $uS));
      $FACTORIES::getHashBinaryFactory()->massUpdate(array($FACTORIES::FILTER => $qF2, $FACTORIES::UPDATE => $uS));
    }

    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $FACTORIES::getChunkFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $FACTORIES::getTaskFactory()->delete($task);
  }

  /**
   * @param int $chunkId
   * @param User $user
   * @throws HTException
   * @return Chunk
   */
  public static function getChunk($chunkId, $user) {
    global $FACTORIES;

    $chunk = $FACTORIES::getChunkFactory()->get($chunkId);
    if ($chunk == null) {
      throw new HTException("Invalid chunk ID!");
    }
    $task = $FACTORIES::getTaskFactory()->get($chunk->getTaskId());
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
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
    global $FACTORIES;

    if($task->getIsArchived() == 1){
      return null;
    }
    else if ($task->getKeyspace() == 0) {
      return $task;
    }
    else if($task->getIsPrince() && $task->getKeyspace() == DPrince::PRINCE_KEYSPACE){
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
      else if (time() - max($chunk->getSolveTime(), $chunk->getDispatchTime()) > SConfig::getInstance()->getVal(DConfig::AGENT_TIMEOUT)) {
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

  /**
   * @param $pretask Pretask
   * @return File[]
   */
  public static function getFilesOfPretask($pretask) {
    global $FACTORIES;

    $qF = new QueryFilter(FilePretask::PRETASK_ID, $pretask->getId(), "=", $FACTORIES::getFilePretaskFactory());
    $jF = new JoinFilter($FACTORIES::getFilePretaskFactory(), File::FILE_ID, FilePretask::FILE_ID);
    $joined = $FACTORIES::getFileFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    /** @var $files File[] */
    return $joined[$FACTORIES::getFileFactory()->getModelName()];
  }

  /**
   * @param int $supertaskId
   * @param User $user
   * @throws HTException
   */
  public static function deleteSupertask($supertaskId, $user) {
    global $FACTORIES;

    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($supertaskId);
    if ($taskWrapper === null) {
      throw new HTException("Invalid supertask!");
    }
    else if (!AccessUtils::userCanAccessTask($taskWrapper, $user)) {
      throw new HTException("No access to this supertask!");
    }

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
    $tasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach ($tasks as $task) {
      TaskUtils::deleteTask($task);
    }
    $FACTORIES::getTaskWrapperFactory()->delete($taskWrapper);
    $FACTORIES::getAgentFactory()->getDB()->commit();
  }
}