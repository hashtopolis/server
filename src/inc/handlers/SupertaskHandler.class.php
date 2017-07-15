<?php
use DBA\JoinFilter;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Supertask;
use DBA\SupertaskTask;
use DBA\Task;
use DBA\TaskFile;
use DBA\TaskTask;

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 10.11.16
 * Time: 14:38
 */
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
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
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
      if($task->getIsCpuTask() == 1){
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
    foreach($subTasks as $task){
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
    $qF = new QueryFilter(SupertaskTask::SUPERTASK_ID, $supertask->getId(), "=");
    $FACTORIES::getSupertaskTaskFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $FACTORIES::getSupertaskFactory()->delete($supertask);
    $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    UI::addMessage(UI::SUCCESS, "Supertask deleted successfully!");
  }
}