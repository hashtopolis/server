<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 10.11.16
 * Time: 14:38
 */
class AgentHandler implements Handler {
  private $agent;
  
  public function __construct($agentId = null) {
    global $FACTORIES;
    
    if ($agentId == null) {
      $this->agent = null;
      return;
    }
    
    $this->agent = $FACTORIES::getAgentFactory()->get($agentId);
    if ($this->agent == null) {
      UI::printError("FATAL", "Agent with ID $agentId not found!");
    }
  }
  
  public function handle($action) {
    global $LOGIN;
    
    switch ($action) {
      case 'clearerrors':
        if ($LOGIN->getLevel() < 30) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->clearErrors();
        break;
      case 'agentrename':
        if ($LOGIN->getLevel() < 30) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->rename();
        break;
      case 'agentowner':
        if ($LOGIN->getLevel() < 30) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->changeOwner();
        break;
      case 'agenttrusted':
        if ($LOGIN->getLevel() < 30) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->changeTrusted();
        break;
      case 'agentignore':
        $this->changeIgnoreErrors();
        break;
      case 'setparam':
        $this->changeCmdParameters();
        break;
      case 'agentwait':
        $this->changeWaitTime();
        break;
      case 'agentactive':
        $this->toggleActive();
        break;
      case 'agentdelete':
        if ($LOGIN->getLevel() < 30) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->delete();
        break;
      case 'agentassign':
        $this->assign();
        break;
      case 'vouchercreate':
        $this->createVoucher();
        break;
      case 'voucherdelete':
        $this->deleteVoucher();
        break;
      default:
        UI::addMessage("danger", "Invalid action!");
        break;
    }
  }
  
  private function assign() {
    global $FACTORIES;
    
    if($this->agent == null){
      $this->agent = $FACTORIES::getAgentFactory()->get($_POST['agent']);
    }
    
    if (intval($_POST['task']) == 0) {
      //unassign
      $qF = new QueryFilter("agentId", $this->agent->getId(), "=");
      $FACTORIES::getAssignmentFactory()->massDeletion(array('filter' => array($qF)));
      if (isset($_GET['task'])) {
        header("Location: tasks.php?id=" . intval($_GET['task']));
        die();
      }
      return;
    }
  
    $this->agent = $FACTORIES::getAgentFactory()->get($_POST['agentId']);
    if ($this->agent == null) {
      UI::printError("FATAL", "Agent with ID ".$_POST['agentId']." not found!");
    }
    
    $task = $FACTORIES::getTaskFactory()->get(intval($_POST['task']));
    if (!$task) {
      UI::printError("ERROR", "Invalid task!");
    }
    $qF = new QueryFilter("agentId", $_POST['agentId'], "=");
    $assignments = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF)));
    
    //determine benchmark number
    $benchmark = 0;
    $qF1 = new ComparisonFilter("solveTime", "dispatchTime", ">");
    $qF2 = new ComparisonFilter("progress", "length", "=");
    $qF3 = new ContainFilter("state", array("4", "5"));
    $qF4 = new QueryFilter("agentId", $this->agent->getId(), "=");
    $qF5 = new QueryFilter("taskId", $task->getId(), "=");
    $oF = new OrderFilter("solveTime", "DESC");
    $entries = $FACTORIES::getChunkFactory()->filter(array('filter' => array($qF1, $qF2, $qF3, $qF4, $qF5), 'order' => array($oF)));
    if (sizeof($entries) > 0) {
      $benchmark = $entries[0]->getLength();
    }
    unset($entries);
    
    if (sizeof($assignments) > 0) {
      for ($i = 1; $i < sizeof($assignments); $i++) { // clean up if required
        $FACTORIES::getAssignmentFactory()->delete($assignments[$i]);
      }
      $assignment = $assignments[0];
      $assignment->setTaskId($task->getId());
      $assignment->setBenchmark($benchmark);
      $assignment->setautoAdjust($task->getAutoAdjust());
      $assignment->setSpeed(0);
      $FACTORIES::getAssignmentFactory()->update($assignment);
    }
    else {
      $assignment = new Assignment(0, $task->getId(), $this->agent->getId(), $benchmark, 0);
      $FACTORIES::getAssignmentFactory()->save($assignment);
    }
    if (isset($_GET['task'])) {
      header("Location: tasks.php?id=" . intval($_GET['task']));
      die();
    }
  }
  
  private function delete() {
    global $FACTORIES;
    
    AbstractModelFactory::getDB()->query("START TRANSACTION");
    if ($this->deleteDependencies($this->agent)) {
      AbstractModelFactory::getDB()->query("COMMIT");
    }
    else {
      AbstractModelFactory::getDB()->query("ROLLBACK");
      UI::printError("FATAL", "Error occured on deletion of agent!");
    }
  }
  
  private function deleteVoucher() {
    global $FACTORIES;
    
    $voucher = $FACTORIES::getRegVoucherFactory()->get(intval($_POST["voucher"]));
    $FACTORIES::getRegVoucherFactory()->delete($voucher);
  }
  
  private function createVoucher() {
    global $FACTORIES;
    
    $key = htmlentities($_POST["newvoucher"], false, "UTF-8");
    $voucher = new RegVoucher(0, $key, time());
    $FACTORIES::getRegVoucherFactory()->save($voucher);
  }
  
  private function deleteDependencies($agent) {
    global $FACTORIES;
    
    if($agent == null){
      $agent = $FACTORIES::getAgentFactory()->get($_POST['agent']);
      if($agent == null){
        UI::printError("ERROR", "Invalid agent!");
      }
    }
    
    $qF = new QueryFilter("agentId", $agent->getId(), "=");
    $FACTORIES::getAssignmentFactory()->massDeletion(array('filter' => $qF));
    $FACTORIES::getAgentErrorFactory()->massDeletion(array('filter' => $qF));
    //TODO: delete from Zap
    $uS = new UpdateSet("chunkId", null);
    $chunks = $FACTORIES::getChunkFactory()->filter(array('filter' => $qF));
    $chunkIds = array();
    foreach($chunks as $chunk){
      $chunkIds[] = $chunk->getId();
    }
    if(sizeof($chunks) > 0) {
      $containFilter = new ContainFilter("chunkId", $chunkIds);
      $FACTORIES::getHashFactory()->massUpdate(array('filter' => $containFilter, 'update' => $uS));
      $FACTORIES::getHashBinaryFactory()->massUpdate(array('filter' => $containFilter, 'update' => $uS));
      $uS = new UpdateSet("agentId", null);
      $FACTORIES::getChunkFactory()->massUpdate(array('filter' => $qF, 'update' => $uS));
    }
    $FACTORIES::getAgentFactory()->delete($agent);
    return true;
  }
  
  private function toggleActive() {
    global $FACTORIES;
    
    if($this->agent == null){
      $this->agent = $FACTORIES::getAgentFactory()->get($_POST['agent']);
      if($this->agent == null){
        UI::printError("ERROR", "Invalid agent!");
      }
    }
    
    if ($this->agent->getIsActive() == 1) {
      $this->agent->setIsActive(0);
    }
    else {
      $this->agent->setIsActive(1);
    }
    $FACTORIES::getAgentFactory()->update($this->agent);
  }
  
  private function changeWaitTime() {
    global $FACTORIES;
    
    $wait = intval($_POST["wait"]);
    if ($wait < 0) {
      UI::printError("ERROR", "Invalid wait time!");
    }
    $this->agent->setWait($wait);
    $FACTORIES::getAgentFactory()->update($this->agent);
  }
  
  private function changeCmdParameters() {
    global $FACTORIES;
    
    $pars = htmlentities($_POST["cmdpars"], false, "UTF-8");
    //PROPOSAL: Check here that only normal command line parameters are given and not any maliscious code
    $this->agent->setCmdPars($pars);
    $FACTORIES::getAgentFactory()->update($this->agent);
  }
  
  private function changeIgnoreErrors() {
    global $FACTORIES;
    
    $ignore = intval($_POST["ignore"]);
    if ($ignore != 0 && $ignore != 1) {
      UI::printError("ERROR", "Invalid Ignore state!");
    }
    $this->agent->setIgnoreErrors($ignore);
    $FACTORIES::getAgentFactory()->update($this->agent);
  }
  
  private function changeTrusted() {
    global $FACTORIES;
    
    $trusted = intval($_POST["trusted"]);
    if ($trusted != 0 && $trusted != 1) {
      UI::printError("ERROR", "Invalid trusted state!");
    }
    $this->agent->setIsTrusted($trusted);
    $FACTORIES::getAgentFactory()->update($this->agent);
  }
  
  private function changeOwner() {
    global $FACTORIES;
    
    if ($_POST['owner'] == 0) {
      $this->agent->setUserId(null);
      $FACTORIES::getAgentFactory()->update($this->agent);
    }
    else {
      $user = $FACTORIES::getUserFactory()->get(intval($_POST["owner"]));
      if (!$user) {
        UI::printError("ERROR", "Invalid user selected!");
      }
      $this->agent->setUserId($user->getId());
    }
    $FACTORIES::getAgentFactory()->update($this->agent);
  }
  
  private function clearErrors() {
    global $FACTORIES;
    
    $qF = new QueryFilter("agentId", $this->agent->getId(), "=");
    $FACTORIES::getAgentErrorFactory()->massDeletion(array('filter' => array($qF)));
  }
  
  private function rename() {
    global $FACTORIES;
    
    $name = htmlentities($_POST['name'], false, "UTF-8");
    if (strlen($name) > 0) {
      $this->agent->setAgentName($name);
      $FACTORIES::getAgentFactory()->update($this->agent);
    }
  }
}