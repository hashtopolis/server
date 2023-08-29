<?php

use DBA\AccessGroupUser;
use DBA\FileTask;
use DBA\QueryFilter;
use DBA\Task;
use DBA\TaskWrapper;
use DBA\Factory;

class TaskHandler implements Handler {
  private $task;
  
  public function __construct($taskId = null) {
    if ($taskId == null) {
      $this->task = null;
      return;
    }
    
    $this->task = Factory::getAgentFactory()->get($taskId);
    if ($this->task == null) {
      UI::printError("FATAL", "Task with ID $taskId not found!");
    }
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DTaskAction::SET_BENCHMARK:
          AccessControl::getInstance()->checkPermission(DTaskAction::SET_BENCHMARK_PERM);
          TaskUtils::setBenchmark($_POST['agentId'], $_POST['bench'], Login::getInstance()->getUser());
          break;
        case DTaskAction::SET_SMALL_TASK:
          AccessControl::getInstance()->checkPermission(DTaskAction::SET_SMALL_TASK_PERM);
          TaskUtils::setSmallTask($_POST['task'], $_POST['isSmall'], Login::getInstance()->getUser());
          break;
        case DTaskAction::SET_CPU_TASK:
          AccessControl::getInstance()->checkPermission(DTaskAction::SET_CPU_TASK_PERM);
          TaskUtils::setCpuTask($_POST['task'], $_POST['isCpu'], Login::getInstance()->getUser());
          break;
        case DTaskAction::ABORT_CHUNK:
          AccessControl::getInstance()->checkPermission(DTaskAction::ABORT_CHUNK_PERM);
          TaskUtils::abortChunk($_POST['chunk'], Login::getInstance()->getUser());
          break;
        case DTaskAction::RESET_CHUNK:
          AccessControl::getInstance()->checkPermission(DTaskAction::RESET_CHUNK_PERM);
          TaskUtils::resetChunk($_POST['chunk'], Login::getInstance()->getUser());
          break;
        case DTaskAction::PURGE_TASK:
          AccessControl::getInstance()->checkPermission(DTaskAction::PURGE_TASK_PERM);
          TaskUtils::purgeTask($_POST['task'], Login::getInstance()->getUser());
          break;
        case DTaskAction::SET_COLOR:
          AccessControl::getInstance()->checkPermission(DTaskAction::SET_COLOR_PERM);
          TaskUtils::updateColor($_POST['task'], $_POST['color'], Login::getInstance()->getUser());
          break;
        case DTaskAction::SET_TIME:
          AccessControl::getInstance()->checkPermission(DTaskAction::SET_TIME_PERM);
          TaskUtils::changeChunkTime($_POST['task'], $_POST['chunktime'], Login::getInstance()->getUser());
          break;
        case DTaskAction::RENAME_TASK:
          AccessControl::getInstance()->checkPermission(DTaskAction::RENAME_TASK_PERM);
          TaskUtils::rename($_POST['task'], $_POST['name'], Login::getInstance()->getUser());
          break;
        case DTaskAction::DELETE_FINISHED:
          AccessControl::getInstance()->checkPermission(DTaskAction::DELETE_FINISHED_PERM);
          TaskUtils::deleteFinished(Login::getInstance()->getUser());
          break;
        case DTaskAction::DELETE_TASK:
          AccessControl::getInstance()->checkPermission(DTaskAction::DELETE_TASK_PERM);
          TaskUtils::delete($_POST['task'], Login::getInstance()->getUser());
          break;
        case DTaskAction::SET_STATUS_TIMER:
          AccessControl::getInstance()->checkPermission(DTaskAction::SET_STATUS_TIMER_PERM);
          TaskUtils::updateStatusTimer($_POST['task'], $_POST['statusTimer'], Login::getInstance()->getUser());
          break;
        case DTaskAction::SET_PRIORITY:
          AccessControl::getInstance()->checkPermission(DTaskAction::SET_PRIORITY_PERM);
          TaskUtils::updatePriority($_POST["task"], $_POST['priority'], Login::getInstance()->getUser());
          break;
        case DTaskAction::SET_MAX_AGENTS:
          AccessControl::getInstance()->checkPermission(DTaskAction::SET_MAX_AGENTS_PERM);
          TaskUtils::updateMaxAgents($_POST["task"], $_POST['maxAgents'], Login::getInstance()->getUser());
          break;
        case DTaskAction::SET_TOP_PRIORITY:
          AccessControl::getInstance()->checkPermission(DTaskAction::SET_PRIORITY_PERM);
          TaskUtils::updatePriority($_POST["task"], -1, Login::getInstance()->getUser(), true);
          break;
        case DTaskAction::CREATE_TASK:
          AccessControl::getInstance()->checkPermission(array_merge(DTaskAction::CREATE_TASK_PERM, DAccessControl::RUN_TASK_ACCESS));
          $this->create();
          break;
        case DTaskAction::DELETE_SUPERTASK:
          AccessControl::getInstance()->checkPermission(DTaskAction::DELETE_SUPERTASK_PERM);
          TaskUtils::deleteSupertask($_POST['supertaskId'], Login::getInstance()->getUser());
          break;
        case DTaskAction::SET_SUPERTASK_PRIORITY:
          AccessControl::getInstance()->checkPermission(DTaskAction::SET_SUPERTASK_PRIORITY_PERM);
          TaskUtils::setSupertaskPriority($_POST['supertaskId'], $_POST['priority'], Login::getInstance()->getUser());
          break;
        case DTaskAction::SET_SUPERTASK_MAX_AGENTS:
          AccessControl::getInstance()->checkPermission(DTaskAction::SET_SUPERTASK_MAX_AGENTS_PERM);
          TaskUtils::setSuperTaskMaxAgents($_POST['supertaskId'], $_POST['maxAgents'], Login::getInstance()->getUser());
          break;
        case DTaskAction::SET_SUPERTASK_TOP_PRIORITY:
          AccessControl::getInstance()->checkPermission(DTaskAction::SET_SUPERTASK_PRIORITY_PERM);
          TaskUtils::setSupertaskPriority($_POST['supertaskId'], -1, Login::getInstance()->getUser(), true);
          break;
        case DTaskAction::ARCHIVE_TASK:
          AccessControl::getInstance()->checkPermission(DTaskAction::ARCHIVE_TASK_PERM);
          TaskUtils::archiveTask($_POST['task'], Login::getInstance()->getUser());
          break;
        case DTaskAction::ARCHIVE_SUPERTASK:
          AccessControl::getInstance()->checkPermission(DTaskAction::ARCHIVE_SUPERTASK_PERM);
          TaskUtils::archiveSupertask($_POST['supertaskId'], Login::getInstance()->getUser());
          break;
        case DTaskAction::CHANGE_ATTACK:
          AccessControl::getInstance()->checkPermission(DTaskAction::CHANGE_ATTACK_PERM);
          TaskUtils::changeAttackCmd($_POST['task'], $_POST['attackCmd'], Login::getInstance()->getUser());
          break;
        case DTaskAction::DELETE_ARCHIVED:
          AccessControl::getInstance()->checkPermission(DTaskAction::DELETE_ARCHIVED_PERM);
          TaskUtils::deleteArchived(Login::getInstance()->getUser());
          break;
        case DTaskAction::EDIT_NOTES:
          AccessControl::getInstance()->checkPermission(DTaskAction::EDIT_NOTES_PERM);
          TaskUtils::editNotes($_POST['task'], $_POST['notes'], Login::getInstance()->getUser());
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (HTException $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
  
  /**
   * @throws HTException
   */
  private function create() {
    // new task creator
    $name = htmlentities($_POST["name"], ENT_QUOTES, "UTF-8");
    $notes = htmlentities($_POST["notes"], ENT_QUOTES, "UTF-8");
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
    $staticChunking = intval(@$_POST['staticChunking']);
    $chunkSize = intval(@$_POST['chunkSize']);
    $priority = intval(@$_POST['priority']);
    $maxAgents = intval(@$_POST['maxAgents']);
    $enforcePipe = intval(@$_POST['enforcePipe']);
    $usePreprocessor = intval(@$_POST['usePreprocessor']);
    $preprocessorCommand = @$_POST['preprocessorCommand'];
    
    $crackerBinaryType = Factory::getCrackerBinaryTypeFactory()->get($crackerBinaryTypeId);
    $crackerBinary = Factory::getCrackerBinaryFactory()->get($crackerBinaryVersionId);
    $hashlist = Factory::getHashlistFactory()->get($_POST["hashlist"]);
    if ($hashlist == null) {
      UI::addMessage(UI::ERROR, "No hashlist was selected!");
      return;
    }
    else if ($hashlist->getIsArchived()) {
      UI::addMessage(UI::ERROR, "You cannot create a task for an archived hashlist!");
      return;
    }
    $accessGroup = Factory::getAccessGroupFactory()->get($hashlist->getAccessGroupId());
    if ($usePreprocessor < 0) {
      $usePreprocessor = 0;
    }
    else if ($usePreprocessor > 0) {
      PreprocessorUtils::getPreprocessor($usePreprocessor);
    }
    
    if (strpos($cmdline, SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS)) === false) {
      UI::addMessage(UI::ERROR, "Command line must contain hashlist (" . SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS) . ")!");
      return;
    }
    else if ($accessGroup == null) {
      UI::addMessage(UI::ERROR, "Invalid access group!");
      return;
    }
    else if ($staticChunking < DTaskStaticChunking::NORMAL || $staticChunking > DTaskStaticChunking::NUM_CHUNKS) {
      UI::addMessage(UI::ERROR, "Invalid static chunking value selected!");
      return;
    }
    else if ($enforcePipe < 0 || $enforcePipe > 1) {
      UI::addMessage(UI::ERROR, "Invalid enforce pipe value selected!");
      return;
    }
    else if ($staticChunking > DTaskStaticChunking::NORMAL && $chunkSize <= 0) {
      UI::addMessage(UI::ERROR, "Invalid chunk size / number of chunks for static chunking selected!");
      return;
    }
    else if (Util::containsBlacklistedChars($cmdline)) {
      UI::addMessage(UI::ERROR, "The command must contain no blacklisted characters!");
      return;
    }
    else if (Util::containsBlacklistedChars($preprocessorCommand)) {
      UI::addMessage(UI::ERROR, "The preprocessor command must contain no blacklisted characters!");
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
    $qF2 = new QueryFilter(AccessGroupUser::USER_ID, Login::getInstance()->getUserID(), "=");
    $accessGroupUser = Factory::getAccessGroupUserFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($accessGroupUser == null) {
      UI::addMessage(UI::ERROR, "No access to this access group!");
      return;
    }
    
    if ($skipKeyspace < 0) {
      $skipKeyspace = 0;
    }
    if ($priority < 0) {
      $priority = 0;
    }
    if ($maxAgents < 0) {
      $maxAgents = 0;
    }
    if ($usePreprocessor && !$useNewBench) {
      // enforce speed benchmark when using prince
      $useNewBench = 1;
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
    
    Factory::getAgentFactory()->getDB()->beginTransaction();
    $taskWrapper = new TaskWrapper(null, $priority, $maxAgents, DTaskTypes::NORMAL, $hashlistId, $accessGroup->getId(), "", 0, 0);
    $taskWrapper = Factory::getTaskWrapperFactory()->save($taskWrapper);
    
    if (AccessControl::getInstance()->hasPermission(DAccessControl::CREATE_TASK_ACCESS)) {
      $task = new Task(
        null,
        $name,
        $cmdline,
        $chunk,
        $status,
        0,
        0,
        $priority,
        $maxAgents,
        $color,
        $isSmall,
        $isCpuTask,
        $useNewBench,
        $skipKeyspace,
        $crackerBinary->getId(),
        $crackerBinaryType->getId(),
        $taskWrapper->getId(),
        0,
        $notes,
        $staticChunking,
        $chunkSize,
        $enforcePipe,
        $usePreprocessor,
        $preprocessorCommand
      );
    }
    else {
      $copy = Factory::getPretaskFactory()->get($_POST['copy']);
      if ($copy == null) {
        UI::addMessage(UI::ERROR, "Invalid preconfigured task used!");
        return;
      }
      // force to copy from pretask to make sure user cannot change anything he is not allowed to
      $task = new Task(
        null,
        $name,
        $copy->getAttackCmd(),
        $copy->getChunkTime(),
        $copy->getStatusTimer(),
        0,
        0,
        $priority,
        $copy->getMaxAgents(),
        $copy->getColor(),
        $copy->getIsSmall(),
        $copy->getIsCpuTask(),
        $copy->getUseNewBench(),
        0,
        $crackerBinary->getId(),
        $crackerBinaryType->getId(),
        $taskWrapper->getId(),
        0,
        $notes,
        0,
        0,
        0,
        0,
        ''
      );
      $forward = "pretasks.php";
    }
    
    $task = Factory::getTaskFactory()->save($task);
    if (isset($_POST["adfile"])) {
      $adfile = array_unique($_POST['adfile']);
      foreach ($adfile as $fileId) {
        $taskFile = new FileTask(null, $fileId, $task->getId());
        Factory::getFileTaskFactory()->save($taskFile);
        FileDownloadUtils::addDownload($taskFile->getFileId());
      }
    }
    Factory::getAgentFactory()->getDB()->commit();
    
    $payload = new DataSet(array(DPayloadKeys::TASK => $task));
    NotificationHandler::checkNotifications(DNotificationType::NEW_TASK, $payload);
    
    header("Location: $forward");
    die();
  }
}
