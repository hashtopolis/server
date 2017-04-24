<?php
use DBA\AgentError;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ComparisonFilter;
use DBA\ContainFilter;
use DBA\Hash;
use DBA\Hashlist;
use DBA\JoinFilter;
use DBA\NotificationSetting;
use DBA\QueryFilter;
use DBA\SupertaskTask;
use DBA\Task;
use DBA\TaskFile;

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 18.11.16
 * Time: 20:21
 */
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
    /** @var Login $LOGIN */
    global $LOGIN;
    
    switch ($action) {
      case 'agentbench':
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->adjustBenchmark();
        break;
      case 'smalltask':
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->setSmallTask();
        break;
      case 'cputask':
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->setCpuTask();
        break;
      case 'chunkabort':
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->abortChunk();
        break;
      case 'chunkreset':
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->resetChunk();
        break;
      case 'taskpurge':
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->purgeTask();
        break;
      case 'taskcolor':
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->updateColor();
        break;
      case 'taskchunk':
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->changeChunkTime();
        break;
      case 'taskrename':
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->rename();
        break;
      case "finishedtasksdelete":
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->deleteFinished();
        break;
      case 'taskdelete':
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->delete();
        break;
      case 'taskprio':
        if ($LOGIN->getLevel() < DAccessLevel::USER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->updatePriority();
        break;
      case 'newtaskp':
        $this->create();
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
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
    global $FACTORIES, $CONFIG;
    
    // new task creator
    $name = htmlentities($_POST["name"], false, "UTF-8");
    $cmdline = $_POST["cmdline"];
    $chunk = intval($_POST["chunk"]);
    $status = intval($_POST["status"]);
    $useNewBench = intval($_POST['benchType']);
    $isCpuTask = intval($_POST['cpuOnly']);
    $isSmall = intval($_POST['isSmall']);
    $skipKeyspace = intval($_POST['skipKeyspace']);
    $color = $_POST["color"];
    if (preg_match("/[0-9A-Za-z]{6}/", $color) != 1) {
      $color = null;
    }
    if (strpos($cmdline, $CONFIG->getVal(DConfig::HASHLIST_ALIAS)) === false) {
      UI::addMessage(UI::ERROR, "Command line must contain hashlist (" . $CONFIG->getVal(DConfig::HASHLIST_ALIAS) . ")!");
      return;
    }
    else if (Util::containsBlacklistedChars($cmdline)) {
      UI::addMessage(UI::ERROR, "The command must contain no blacklisted characters!");
      return;
    }
    else if ($skipKeyspace < 0) {
      $skipKeyspace = 0;
    }
    $hashlist = null;
    if ($_POST["hashlist"] == null) {
      // it will be a preconfigured task
      $hashlistId = null;
      if (strlen($name) == 0) {
        $name = "PC_" . date("Ymd_Hi");
      }
      $forward = "pretasks.php";
    }
    else {
      $hashlist = $FACTORIES::getHashlistFactory()->get($_POST["hashlist"]);
      if ($hashlist <= 0) {
        UI::addMessage(UI::ERROR, "Invalid hashlist!");
        return;
      }
      $hashlistId = $hashlist->getId();
      if (strlen($name) == 0) {
        $name = "HL" . $hashlistId . "_" . date("Ymd_Hi");
      }
      $forward = "tasks.php";
    }
    if ($chunk < 0 || $status < 0 || $chunk < $status) {
      UI::addMessage(UI::ERROR, "Chunk time must be higher than status timer!");
      return;
    }
    if ($hashlistId != null && $hashlist->getHexSalt() == 1 && strpos($cmdline, "--hex-salt") === false) {
      $cmdline = "--hex-salt $cmdline"; // put the --hex-salt if the user was not clever enough to put it there :D
    }
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    $task = new Task(0, $name, $cmdline, $hashlistId, $chunk, $status, 0, 0, 0, $color, $isSmall, $isCpuTask, $useNewBench, $skipKeyspace);
    $task = Util::cast($FACTORIES::getTaskFactory()->save($task), Task::class);
    if (isset($_POST["adfile"])) {
      foreach ($_POST["adfile"] as $fileId) {
        $taskFile = new TaskFile(0, $task->getId(), $fileId);
        $FACTORIES::getTaskFileFactory()->save($taskFile);
      }
    }
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    
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
    $pretask = false;
    if (isset($_GET['pre'])) {
      $pretask = true;
    }
    $priority = intval($_POST["priority"]);
    $task->setPriority($priority);
    $FACTORIES::getTaskFactory()->update($task);
    if ($pretask) {
      header("Location: pretasks.php");
    }
    else {
      header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
    }
    die();
  }
  
  private function delete() {
    global $FACTORIES;
    
    // delete a task
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if ($task == null) {
      UI::addMessage(UI::ERROR, "No such task!");
      return;
    }
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    
    $payload = new DataSet(array(DPayloadKeys::TASK => $task));
    NotificationHandler::checkNotifications(DNotificationType::DELETE_TASK, $payload);
    
    $this->deleteTask($task);
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    
    if ($task->getHashlistId() == null) {
      header("Location: pretasks.php");
      die();
    }
  }
  
  /**
   * @param $task Task
   * @param bool $onlyFinished set to true if deletion only should be applied if the task is completely finished
   */
  private function deleteTask($task, $onlyFinished = false) {
    global $FACTORIES;
    
    
    $uS = new UpdateSet(Hash::CHUNK_ID, null);
    $qF = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
    $chunkIds = array();
    foreach ($chunks as $chunk) {
      if ($chunk->getRprogress() != 10000 && $onlyFinished) {
        return; //if at least one chunk is not finished, we should not delete this task
      }
      $chunkIds[] = $chunk->getId();
    }
    $qF = new QueryFilter(SupertaskTask::TASK_ID, $task->getId(), "=");
    $FACTORIES::getSupertaskTaskFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
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
    $qF = new QueryFilter(TaskFile::TASK_ID, $task->getId(), "=");
    $FACTORIES::getTaskFileFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
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
    
    // delete finished tasks
    $qF1 = new QueryFilter(Task::PROGRESS, 0, ">");
    $qF2 = new ComparisonFilter(Task::KEYSPACE, Task::PROGRESS, "=");
    $tasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)));
    
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    foreach ($tasks as $task) {
      $this->deleteTask($task, true);
    }
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    
    // delete tasks which are not completed but where the hashlist is fully cracked
    $qF = new ComparisonFilter(Hashlist::CRACKED, Hashlist::HASH_COUNT, "=", $FACTORIES::getHashlistFactory());
    $jF = new JoinFilter($FACTORIES::getTaskFactory(), Task::HASHLIST_ID, Hashlist::HASHLIST_ID);
    $joinedTasks = $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    foreach ($joinedTasks[$FACTORIES::getTaskFactory()->getModelName()] as $task) {
      /** @var $task Task */
      $this->deleteTask($task);
    }
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
  }
  
  private function rename() {
    global $FACTORIES;
    
    // change task name
    $task = $FACTORIES::getTaskFactory()->get($_POST["task"]);
    if ($task == null) {
      UI::addMessage(UI::ERROR, "No such task!");
      return;
    }
    $name = htmlentities($_POST["name"], false, "UTF-8");
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
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
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
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
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
    $chunk->setRprogress(0);
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
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
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
    $task->setProgress(0);
    $FACTORIES::getTaskFactory()->update($task);
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
  }
  
  private function adjustBenchmark() {
    global $FACTORIES;
    
    // adjust agent benchmark
    $qF = new QueryFilter(Assignment::AGENT_ID, $_POST['agent'], "=");
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