<?php

use DBA\AccessGroupUser;
use DBA\FileTask;
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
    global $ACCESS_CONTROL, $LOGIN;

    $error = false;
    switch ($action) {
      case DTaskAction::SET_BENCHMARK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::SET_BENCHMARK_PERM);
        $error = TaskUtils::setBenchmark($_POST['agentId'], $_POST['bench'], $LOGIN->getUser());
        break;
      case DTaskAction::SET_SMALL_TASK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::SET_SMALL_TASK_PERM);
        $error = TaskUtils::setSmallTask($_POST['task'], $_POST['isSmall'], $LOGIN->getUser());
        break;
      case DTaskAction::SET_CPU_TASK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::SET_CPU_TASK_PERM);
        $error = TaskUtils::setCpuTask($_POST['task'], $_POST['isCpu'], $LOGIN->getUser());
        break;
      case DTaskAction::ABORT_CHUNK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::ABORT_CHUNK_PERM);
        $error = TaskUtils::abortChunk($_POST['chunk'], $LOGIN->getUser());
        break;
      case DTaskAction::RESET_CHUNK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::RESET_CHUNK_PERM);
        $error = TaskUtils::resetChunk($_POST['chunk'], $LOGIN->getUser());
        break;
      case DTaskAction::PURGE_TASK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::PURGE_TASK_PERM);
        $error = TaskUtils::purgeTask($_POST['task'], $LOGIN->getUser());
        break;
      case DTaskAction::SET_COLOR:
        $ACCESS_CONTROL->checkPermission(DTaskAction::SET_COLOR_PERM);
        $error = TaskUtils::updateColor($_POST['task'], $_POST['color'], $LOGIN->getUser());
        break;
      case DTaskAction::SET_TIME:
        $ACCESS_CONTROL->checkPermission(DTaskAction::SET_TIME_PERM);
        $error = TaskUtils::changeChunkTime($_POST['task'], $_POST['chunktime'], $LOGIN->getUser());
        break;
      case DTaskAction::RENAME_TASK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::RENAME_TASK_PERM);
        $error = TaskUtils::rename($_POST['task'], $_POST['name'], $LOGIN->getUser());
        break;
      case DTaskAction::DELETE_FINISHED:
        $ACCESS_CONTROL->checkPermission(DTaskAction::DELETE_FINISHED_PERM);
        $error = TaskUtils::deleteFinished($LOGIN->getUser());
        break;
      case DTaskAction::DELETE_TASK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::DELETE_TASK_PERM);
        $error = TaskUtils::delete($_POST['taskId'], $LOGIN->getUser());
        break;
      case DTaskAction::SET_PRIORITY:
        $ACCESS_CONTROL->checkPermission(DTaskAction::SET_PRIORITY_PERM);
        $error = TaskUtils::updatePriority($_POST["task"], $_POST['priority'], $LOGIN->getUser());
        break;
      case DTaskAction::CREATE_TASK:
        $ACCESS_CONTROL->checkPermission(array_merge(DTaskAction::CREATE_TASK_PERM, DAccessControl::RUN_TASK_ACCESS));
        $this->create();
        break;
      case DTaskAction::DELETE_SUPERTASK:
        $ACCESS_CONTROL->checkPermission(DTaskAction::DELETE_SUPERTASK_PERM);
        $error = TaskUtils::deleteSupertask($_POST['supertaskId'], $LOGIN->getUser());
        break;
      case DTaskAction::SET_SUPERTASK_PRIORITY:
        $ACCESS_CONTROL->checkPermission(DTaskAction::SET_SUPERTASK_PRIORITY_PERM);
        $error = TaskUtils::setSupertaskPriority($_POST['supertaskId'], $_POST['priority'], $LOGIN->getUser());
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
    if($error !== false){
      UI::addMessage(UI::ERROR, $error);
    }
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

    if ($ACCESS_CONTROL->hasPermission(DAccessControl::CREATE_TASK_ACCESS)) {
      $task = new Task(0, $name, $cmdline, $chunk, $status, 0, 0, 0, $color, $isSmall, $isCpuTask, $useNewBench, $skipKeyspace, $crackerBinary->getId(), $crackerBinaryType->getId(), $taskWrapper->getId());
    }
    else {
      $copy = $FACTORIES::getPretaskFactory()->get($_POST['copy']);
      if ($copy == null) {
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
}