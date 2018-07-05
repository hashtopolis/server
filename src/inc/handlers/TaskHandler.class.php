<?php

use DBA\AccessGroupUser;
use DBA\AgentError;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ContainFilter;
use DBA\FileTask;
use DBA\Hash;
use DBA\JoinFilter;
use DBA\NotificationSetting;
use DBA\QueryFilter;
use DBA\Task;
use DBA\TaskWrapper;

class TaskHandler implements Handler {
  private $task;

  public function __construct($taskId = null) {
    global $FACTORIES;

    if ($taskId == null) {
      $this->task = null;
      return;
    }

    $this->task = $FACTORIES::getAgentFactory()->get($taskId);
    if ($this->task == null) {
      UI::printError("FATAL", "Task with ID $taskId not found!");
    }
  }

  public function handle($action) {
    global $ACCESS_CONTROL;

    switch ($action) {
      case DTaskAction::SET_BENCHMARK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::SET_BENCHMARK_PERM);
        $this->adjustBenchmark();
        break;
      case DTaskAction::SET_SMALL_TASK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::SET_SMALL_TASK_PERM);
        $this->setSmallTask();
        break;
      case DTaskAction::SET_CPU_TASK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::SET_CPU_TASK_PERM);
        $this->setCpuTask();
        break;
      case DTaskAction::ABORT_CHUNK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::ABORT_CHUNK_PERM);
        $this->abortChunk();
        break;
      case DTaskAction::RESET_CHUNK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::RESET_CHUNK_PERM);
        $this->resetChunk();
        break;
      case DTaskAction::PURGE_TASK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::PURGE_TASK_PERM);
        $this->purgeTask();
        break;
      case DTaskAction::SET_COLOR:
        $ACCESS_CONTROL->checkPermission(DTaskAction::SET_COLOR_PERM);
        $this->updateColor();
        break;
      case DTaskAction::SET_TIME:
        $ACCESS_CONTROL->checkPermission(DTaskAction::SET_TIME_PERM);
        $this->changeChunkTime();
        break;
      case DTaskAction::RENAME_TASK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::RENAME_TASK_PERM);
        $this->rename();
        break;
      case DTaskAction::DELETE_FINISHED:
        $ACCESS_CONTROL->checkPermission(DTaskAction::DELETE_FINISHED_PERM);
        $this->deleteFinished();
        break;
      case DTaskAction::DELETE_TASK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::DELETE_TASK_PERM);
        $this->delete();
        break;
      case DTaskAction::SET_PRIORITY:
        $ACCESS_CONTROL->checkPermission(DTaskAction::SET_PRIORITY_PERM);
        $this->updatePriority();
        break;
      case DTaskAction::CREATE_TASK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::CREATE_TASK_PERM);
        $this->create();
        break;
      case DTaskAction::DELETE_SUPERTASK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::DELETE_SUPERTASK_PERM);
        $this->deleteSupertask($_POST['supertaskId']);
        break;
      case DTaskAction::SET_SUPERTASK_PRIORITY:
        $ACCESS_CONTROL->checkPermission(DTaskAction::SET_SUPERTASK_PRIORITY_PERM);
        $this->setSupertaskPriority($_POST['supertaskId'], $_POST['priority']);
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }

  private function deleteSupertask($supertaskId) {
    global $FACTORIES;

    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($supertaskId);
    if ($taskWrapper === null) {
      UI::addMessage(UI::ERROR, "Invalid supertask!");
      return;
    }

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $taskWrapper->getId(), "=");
    $tasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach ($tasks as $task) {
      $this->deleteTask($task);
    }
    $FACTORIES::getTaskWrapperFactory()->delete($taskWrapper);
    $FACTORIES::getAgentFactory()->getDB()->commit();
  }

  private function setSupertaskPriority($supertaskId, $priority) {
    global $FACTORIES;

    $supertask = $FACTORIES::getTaskWrapperFactory()->get($supertaskId);
    if ($supertask === null) {
      UI::addMessage(UI::ERROR, "Invalid supertask!");
      return;
    }
    $priority = ($priority < 0) ? 0 : $priority;
    $supertask->setPriority($priority);
    $FACTORIES::getTaskWrapperFactory()->update($supertask);
  }

  private function setSmallTask() {
    global $FACTORIES;

    $task = $FACTORIES::getTaskFactory()->get($_POST['task']);
    if ($task == null) {
      UI::addMessage(UI::ERROR, "No such task!");
      return;
    }
    $isSmall = intval($_POST['isSmall']);
    if ($isSmall != 0 && $isSmall != 1) {
      $isSmall = 0;
    }
    $task->setIsSmall($isSmall);
    $FACTORIES::getTaskFactory()->update($task);
  }

  private function setCpuTask() {
    global $FACTORIES;

    $task = $FACTORIES::getTaskFactory()->get($_POST['task']);
    if ($task == null) {
      UI::addMessage(UI::ERROR, "No such task!");
      return;
    }
    $isCpuTask = intval($_POST['isCpu']);
    if ($isCpuTask != 0 && $isCpuTask != 1) {
      $isCpuTask = 0;
    }
    $task->setIsCpuTask($isCpuTask);
    $FACTORIES::getTaskFactory()->update($task);
  }

  private function create() {
    /** @var DataSet $CONFIG */
    /** @var $LOGIN Login */
    global $FACTORIES, $CONFIG, $LOGIN, $ACCESS_CONTROL;

    // new task creator
    $name = htmlentities($_POST["name"], ENT_QUOTES, "UTF-8");
    $cmdline = @$_POST["cmdline"];
    $chunk = intval(@$_POST["chunk"]);
    $status = intval(@$_POST["status"]);
    $useNewBench = intval(@$_POST['benchType']);
    $isCpuTask = intval(@$_POST['cpuOnly']);
    $isSmall = intval(@$_POST['isSmall']);
    $skipKeyspace = intval(@$_POST['skipKeyspace']);
    $crackerBinaryTypeId = intval($_POST['crackerBinaryTypeId']);
    $crackerBinaryVersionId = intval($_POST['crackerBinaryVersionId']);
    $color = @$_POST["color"];

    $crackerBinaryType = $FACTORIES::getCrackerBinaryTypeFactory()->get($crackerBinaryTypeId);
    $crackerBinary = $FACTORIES::getCrackerBinaryFactory()->get($crackerBinaryVersionId);
    $hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
    $accessGroup = $FACTORIES::getAccessGroupFactory()->get($hashlist->getAccessGroupId());

    if (strpos($cmdline, $CONFIG->getVal(DConfig::HASHLIST_ALIAS)) === false) {
      UI::addMessage(UI::ERROR, "Command line must contain hashlist (" . $CONFIG->getVal(DConfig::HASHLIST_ALIAS) . ")!");
      return;
    }
    else if ($accessGroup == null) {
      UI::addMessage(UI::ERROR, "Invalid access group!");
      return;
    }
    else if (Util::containsBlacklistedChars($cmdline)) {
      UI::addMessage(UI::ERROR, "The command must contain no blacklisted characters!");
      return;
    }
    else if ($crackerBinary == null || $crackerBinaryType == null) {
      UI::addMessage(UI::ERROR, "Invalid cracker binary selection!");
      return;
    }
    else if ($crackerBinary->getCrackerBinaryTypeId() != $crackerBinaryType->getId()) {
      UI::addMessage(UI::ERROR, "Non-matching cracker binary selection!");
      return;
    }
    else if ($hashlist == null) {
      UI::addMessage(UI::ERROR, "Invalid hashlist selected!");
      return;
    }
    else if ($chunk < 0 || $status < 0 || $chunk < $status) {
      UI::addMessage(UI::ERROR, "Chunk time must be higher than status timer!");
      return;
    }

    $qF1 = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $accessGroup->getId(), "=");
    $qF2 = new QueryFilter(AccessGroupUser::USER_ID, $LOGIN->getUserID(), "=");
    $accessGroupUser = $FACTORIES::getAccessGroupUserFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
    if ($accessGroupUser == null) {
      UI::addMessage(UI::ERROR, "No access to this access group!");
      return;
    }

    if ($skipKeyspace < 0) {
      $skipKeyspace = 0;
    }
    if (preg_match("/[0-9A-Za-z]{6}/", $color) != 1) {
      $color = null;
    }
    $hashlistId = $hashlist->getId();
    if (strlen($name) == 0) {
      $name = "HL" . $hashlistId . "_" . date("Ymd_Hi");
    }
    $forward = "tasks.php";
    if ($hashlistId != null && $hashlist->getHexSalt() == 1 && strpos($cmdline, "--hex-salt") === false) {
      $cmdline = "--hex-salt $cmdline"; // put the --hex-salt if the user was not clever enough to put it there :D
    }

    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $taskWrapper = new TaskWrapper(0, 0, DTaskTypes::NORMAL, $hashlistId, $accessGroup->getId(), "");
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->save($taskWrapper);

    if($ACCESS_CONTROL->hasPermission(DAccessControl::CREATE_TASK_ACCESS)){
      $task = new Task(0, $name, $cmdline, $chunk, $status, 0, 0, 0, $color, $isSmall, $isCpuTask, $useNewBench, $skipKeyspace, $crackerBinary->getId(), $crackerBinaryType->getId(), $taskWrapper->getId());
    }
    else{
      $copy = $FACTORIES::getPretaskFactory()->get($_POST['copy']);
      if($copy == null){
        UI::addMessage(UI::ERROR, "Invalid preconfigured task used!");
        return;
      }
      // force to copy from pretask to make sure user cannot change anything he is not allowed to
      $task = new Task(0, $name, $copy->getAttackCmd(), $copy->getChunkTime(), $copy->getStatusTimer(), 0, 0, 0, $copy->getColor(), $copy->getIsSmall(), $copy->getIsCpuTask(), $copy->getUseNewBench(), 0, $crackerBinary->getId(), $crackerBinaryType->getId(), $taskWrapper->getId());
      $forward = "pretasks.php";
    }

    $task = $FACTORIES::getTaskFactory()->save($task);
    if (isset($_POST["adfile"])) {
      foreach ($_POST["adfile"] as $fileId) {
        $taskFile = new FileTask(0, $fileId, $task->getId());
        $FACTORIES::getFileTaskFactory()->save($taskFile);
      }
    }
    $FACTORIES::getAgentFactory()->getDB()->commit();

    $payload = new DataSet(array(DPayloadKeys::TASK => $task));
    NotificationHandler::checkNotifications(DNotificationType::NEW_TASK, $payload);

    header("Location: $forward");
    die();
  }

  private function updatePriority() {
    global $FACTORIES;

    // change task priority
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if ($task == null) {
      UI::addMessage(UI::ERROR, "No such task!");
      return;
    }
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    $priority = intval($_POST["priority"]);
    $priority = ($priority < 0) ? 0 : $priority;
    $task->setPriority($priority);
    $taskWrapper->setPriority($priority);
    $FACTORIES::getTaskFactory()->update($task);
    if ($taskWrapper->getTaskType() != DTaskTypes::SUPERTASK) {
      $FACTORIES::getTaskWrapperFactory()->update($taskWrapper);
    }
  }

  private function delete() {
    global $FACTORIES;

    // delete a task
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if ($task == null) {
      UI::addMessage(UI::ERROR, "No such task!");
      return;
    }
    $taskWrapper = $FACTORIES::getTaskWrapperFactory()->get($task->getTaskWrapperId());
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();

    $payload = new DataSet(array(DPayloadKeys::TASK => $task));
    NotificationHandler::checkNotifications(DNotificationType::DELETE_TASK, $payload);

    $this->deleteTask($task);
    if ($taskWrapper->getTaskType() != DTaskTypes::SUPERTASK) {
      $FACTORIES::getTaskWrapperFactory()->delete($taskWrapper);
    }
    $FACTORIES::getAgentFactory()->getDB()->commit();
    header("Location: tasks.php");
  }

  /**
   * @param $task Task
   */
  private function deleteTask($task) {
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
    $qF = new QueryFilter(FileTask::TASK_ID, $task->getId(), "=");
    $FACTORIES::getFileTaskFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $uS = new UpdateSet(Hash::CHUNK_ID, null);
    if (sizeof($chunkIds) > 0) {
      $qF2 = new ContainFilter(Hash::CHUNK_ID, $chunkIds);
      $FACTORIES::getHashFactory()->massUpdate(array($FACTORIES::FILTER => $qF2, $FACTORIES::UPDATE => $uS));
      $FACTORIES::getHashBinaryFactory()->massUpdate(array($FACTORIES::FILTER => $qF2, $FACTORIES::UPDATE => $uS));
    }
    $FACTORIES::getChunkFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $FACTORIES::getTaskFactory()->delete($task);
  }

  private function deleteFinished() {
    global $FACTORIES;

    // check every task wrapper
    $taskWrappers = $FACTORIES::getTaskWrapperFactory()->filter(array());
    foreach ($taskWrappers as $taskWrapper) {
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
          $this->deleteTask($task);
        }
        $FACTORIES::getTaskWrapperFactory()->delete($taskWrapper);
        $FACTORIES::getAgentFactory()->getDB()->commit();
      }
    }
  }

  private function rename() {
    global $FACTORIES;

    // change task name
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if ($task == null) {
      UI::addMessage(UI::ERROR, "No such task!");
      return;
    }
    $name = htmlentities($_POST["name"], ENT_QUOTES, "UTF-8");
    $task->setTaskName($name);
    $FACTORIES::getTaskFactory()->update($task);
  }

  private function changeChunkTime() {
    global $FACTORIES;

    // update task chunk time
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if ($task == null) {
      UI::addMessage(UI::ERROR, "No such task!");
      return;
    }
    $chunktime = intval($_POST["chunktime"]);
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=", $FACTORIES::getTaskFactory());
    $jF = new JoinFilter($FACTORIES::getTaskFactory(), Task::TASK_ID, Assignment::TASK_ID);
    $join = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    for ($i = 0; $i < sizeof($join[$FACTORIES::getTaskFactory()->getModelName()]); $i++) {
      $assignment = \DBA\Util::cast($join[$FACTORIES::getAssignmentFactory()->getModelName()][$i], \DBA\Assignment::class);
      $assignment->setBenchmark($assignment->getBenchmark() / $task->getChunkTime() * $chunktime);
      $FACTORIES::getAssignmentFactory()->update($assignment);
    }
    $task->setChunkTime($chunktime);
    $FACTORIES::getTaskFactory()->update($task);
    $FACTORIES::getAgentFactory()->getDB()->commit();
  }

  private function updateColor() {
    global $FACTORIES;

    // change task color
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if ($task == null) {
      UI::addMessage(UI::ERROR, "No such task!");
      return;
    }
    $color = $_POST["color"];
    if (preg_match("/[0-9A-Za-z]{6}/", $color) == 0) {
      $color = null;
    }
    $task->setColor($color);
    $FACTORIES::getTaskFactory()->update($task);
  }

  private function abortChunk() {
    global $FACTORIES;

    // reset chunk state and progress to zero
    $chunk = $FACTORIES::getChunkFactory()->get($_POST['chunk']);
    if ($chunk == null) {
      UI::addMessage(UI::ERROR, "No such chunk!");
      return;
    }
    $chunk->setState(DHashcatStatus::ABORTED);
    $FACTORIES::getChunkFactory()->update($chunk);
  }

  private function resetChunk() {
    global $FACTORIES;

    // reset chunk state and progress to zero
    $chunk = $FACTORIES::getChunkFactory()->get($_POST['chunk']);
    if ($chunk == null) {
      UI::addMessage(UI::ERROR, "No such chunk!");
      return;
    }
    $chunk->setState(0);
    $chunk->setProgress(0);
    $chunk->setCheckpoint($chunk->getSkip());
    $chunk->setDispatchTime(0);
    $chunk->setSolveTime(0);
    $FACTORIES::getChunkFactory()->update($chunk);
  }

  private function purgeTask() {
    global $FACTORIES;

    // delete all task chunks, forget its keyspace value and reset progress to zero
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if ($task == null) {
      UI::addMessage(UI::ERROR, "No such task!");
      return;
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

  private function adjustBenchmark() {
    global $FACTORIES;

    // adjust agent benchmark
    $qF = new QueryFilter(Assignment::AGENT_ID, $_POST['agentId'], "=");
    $assignment = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($assignment == null) {
      UI::addMessage(UI::ERROR, "No assignment for this agent!");
      return;
    }
    $bench = floatval($_POST["bench"]);
    $assignment->setBenchmark($bench);
    $FACTORIES::getAssignmentFactory()->update($assignment);
  }
}