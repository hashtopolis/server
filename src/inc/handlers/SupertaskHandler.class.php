<?php

use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Supertask;
use DBA\SupertaskTask;
use DBA\Task;
use DBA\TaskFile;
use DBA\TaskTask;

class SupertaskHandler implements Handler {
  public function __construct($supertaskId = null) {
    //nothing
  }
  
  public function handle($action) {
    switch ($action) {
      case 'taskdelete':
        $this->delete();
        break;
      case 'createsupertask':
        $this->create();
        break;
      case 'newsupertask':
        $this->createTasks();
        break;
      case 'importsupertask':
        $this->importSupertask();
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function importSupertask() {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;
    
    $name = htmlentities($_POST['name'], false, "UTF-8");
    $masks = $_POST['masks'];
    if (strlen($name) == 0 || strlen($masks) == 0) {
      UI::addMessage(UI::ERROR, "Name or masks is empty!");
      return;
    }
    
    $masks = explode("\n", str_replace("\r\n", "\n", $masks));
    for ($i = 0; $i < sizeof($masks); $i++) {
      if (strlen($masks[$i]) == 0) {
        unset($masks[$i]);
        continue;
      }
      $mask = str_replace("\\,", "COMMA_PLACEHOLDER", $masks[$i]);
      $mask = str_replace("\\#", "HASH_PLACEHOLDER", $mask);
      if (strpos($mask, "#") !== false) {
        $mask = substr($mask, 0, strpos($mask, "#"));
      }
      $mask = explode(",", $mask);
      if (sizeof($mask) > 5) {
        unset($masks[$i]);
        continue;
      }
      $masks[$i] = $mask;
    }
    
    if (sizeof($masks) == 0) {
      UI::addMessage(UI::ERROR, "No valid mask lines! Supertask was not created.");
      return;
    }
    
    // create the preconf tasks
    $preTasks = array();
    $priority = sizeof($masks) + 1;
    foreach ($masks as $mask) {
      $pattern = $mask[sizeof($mask) - 1];
      $cmd = "";
      switch (sizeof($mask)) {
        case 5:
          $cmd = " -4 " . $mask[3] . $cmd;
        case 4:
          $cmd = " -3 " . $mask[2] . $cmd;
        case 3:
          $cmd = " -2 " . $mask[1] . $cmd;
        case 2:
          $cmd = " -1 " . $mask[0] . $cmd;
        case 1:
          $cmd .= " $pattern";
      }
      $cmd = str_replace("COMMA_PLACEHOLDER", "\\,", $cmd);
      $cmd = str_replace("HASH_PLACEHOLDER", "\\#", $cmd);
      $preTaskName = "HIDDEN: " . implode(",", $mask);
      $preTaskName = str_replace("COMMA_PLACEHOLDER", "\\,", $preTaskName);
      $preTaskName = str_replace("HASH_PLACEHOLDER", "\\#", $preTaskName);
      
      //TODO: make configurable if small task, cpu only task etc.
      $preTask = new Task(0, $preTaskName, $CONFIG->getVal(DConfig::HASHLIST_ALIAS) . " -a 3 " . $cmd, null, $CONFIG->getVal(DConfig::CHUNK_DURATION), $CONFIG->getVal(DConfig::STATUS_TIMER), 0, 0, $priority, "", 0, 0, 0, 0, 0);
      $preTask = $FACTORIES::getTaskFactory()->save($preTask);
      $preTasks[] = $preTask;
      $priority--;
    }
    $supertask = new Supertask(0, $name);
    $supertask = $FACTORIES::getSupertaskFactory()->save($supertask);
    foreach ($preTasks as $preTask) {
      $relation = new SupertaskTask(0, $preTask->getId(), $supertask->getId());
      $FACTORIES::getSupertaskTaskFactory()->save($relation);
    }
    header("Location: supertasks.php");
    die();
  }
  
  private function createTasks() {
    /** @var DataSet $CONFIG */
    global $FACTORIES, $CONFIG;
    
    $supertask = $FACTORIES::getSupertaskFactory()->get($_POST['supertask']);
    $hashlist = $FACTORIES::getHashlistFactory()->get($_POST['hashlist']);
    if ($supertask == null) {
      UI::printError("ERROR", "Invalid supertask ID!");
    }
    else if ($hashlist == null) {
      UI::printError("ERROR", "Invalid hashlist ID!");
    }
    
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    
    $subTasks = array();
    
    $oF = new OrderFilter(Task::PRIORITY, "DESC LIMIT 1");
    $qF = new QueryFilter(Task::HASHLIST_ID, null, "<>");
    $highestTask = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF), true);
    $highestPriority = 1;
    if ($highestTask != null) {
      $highestPriority = $highestTask->getPriority() + 1;
    }
    
    $isCpuTask = 0;
    
    $qF = new QueryFilter(SupertaskTask::SUPERTASK_ID, $supertask->getId(), "=", $FACTORIES::getSupertaskTaskFactory());
    $jF = new JoinFilter($FACTORIES::getSupertaskTaskFactory(), SupertaskTask::TASK_ID, Task::TASK_ID);
    $joinedTasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    $tasks = $joinedTasks[$FACTORIES::getTaskFactory()->getModelName()];
    foreach ($tasks as $task) {
      /** @var $task Task */
      if (strpos($task->getAttackCmd(), $CONFIG->getVal(DConfig::HASHLIST_ALIAS)) === false) {
        UI::addMessage(UI::WARN, "Task must contain the hashlist alias for cracking!");
        continue;
      }
      $qF = new QueryFilter(TaskFile::TASK_ID, $task->getId(), "=");
      $taskFiles = $FACTORIES::getTaskFileFactory()->filter(array($FACTORIES::FILTER => $qF));
      $task->setId(0);
      if ($hashlist->getHexSalt() == 1 && strpos($task->getAttackCmd(), "--hex-salt") === false) {
        $task->setAttackCmd("--hex-salt " . $task->getAttackCmd());
      }
      $task->setPriority($highestPriority + $task->getPriority());
      $task->setHashlistId($hashlist->getId());
      $task->setTaskType(DTaskTypes::SUBTASK);
      $task = $FACTORIES::getTaskFactory()->save($task);
      if ($task->getIsCpuTask() == 1) {
        $isCpuTask = 1;
      }
      $subTasks[] = $task;
      foreach ($taskFiles as $taskFile) {
        $taskFile = Util::cast($taskFile, TaskFile::class);
        $taskFile->setId(0);
        $taskFile->setTaskId($task->getId());
        $FACTORIES::getTaskFileFactory()->save($taskFile);
      }
    }
    $supTask = new Task(0, $supertask->getSupertaskName(), "SUPER", $hashlist->getId(), 0, 0, 0, 0, 0, "", 0, $isCpuTask, 0, 0, DTaskTypes::SUPERTASK);
    $supTask = $FACTORIES::getTaskFactory()->save($supTask);
    foreach ($subTasks as $task) {
      $task->setIsCpuTask($isCpuTask); // we need to enforce that all tasks have either cpu task or not cpu task setting
      $FACTORIES::getTaskFactory()->update($task);
      $taskTask = new TaskTask(0, $supTask->getId(), $task->getId());
      $FACTORIES::getTaskTaskFactory()->save($taskTask);
    }
    
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    UI::addMessage(UI::SUCCESS, "New supertask applied successfully!");
  }
  
  private function create() {
    global $FACTORIES;
    
    $name = htmlentities($_POST['name'], false, "UTF-8");
    $tasks = $_POST['task'];
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    $supertask = new Supertask(0, $name);
    $supertask = Util::cast($FACTORIES::getSupertaskFactory()->save($supertask), Supertask::class);
    foreach ($tasks as $t) {
      $task = $FACTORIES::getTaskFactory()->get($t);
      if ($task == null) {
        continue;
      }
      else if ($task->getHashlistId() != null) {
        continue;
      }
      $supertaskTask = new SupertaskTask(0, $task->getId(), $supertask->getId());
      $FACTORIES::getSupertaskTaskFactory()->save($supertaskTask);
    }
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    UI::addMessage(UI::SUCCESS, "New supertask created successfully!");
  }
  
  private function delete() {
    global $FACTORIES;
    
    $supertask = $FACTORIES::getSupertaskFactory()->get($_POST['supertask']);
    if ($supertask == null) {
      UI::printError("ERROR", "Invalid supertask ID!");
    }
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    $qF = new QueryFilter(SupertaskTask::SUPERTASK_ID, $supertask->getId(), "=", $FACTORIES::getSupertaskTaskFactory());
    $jF = new JoinFilter($FACTORIES::getSupertaskTaskFactory(), Task::TASK_ID, SupertaskTask::TASK_ID);
    $joinedTasks = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    for ($i = 0; $i < sizeof($joinedTasks[$FACTORIES::getTaskFactory()->getModelName()]); $i++) {
      /** @var $task Task */
      $task = $joinedTasks[$FACTORIES::getTaskFactory()->getModelName()][$i];
      if (strpos($task->getTaskName(), "HIDDEN:") === 0) {
        $FACTORIES::getTaskFactory()->delete($task);
      }
    }
    
    $FACTORIES::getSupertaskTaskFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $FACTORIES::getSupertaskFactory()->delete($supertask);
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    UI::addMessage(UI::SUCCESS, "Supertask deleted successfully!");
  }
}