<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 10.11.16
 * Time: 14:38
 */
class AgentHandler {
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
    }
  }
  
  private function assign() {
    global $FACTORIES;
    
    if (intval($_POST['task']) == 0) {
      //unassign
      $qF = new QueryFilter("agentId", $this->agent->getId(), "=");
      $FACTORIES::getAssignmentFactory()->massDeletion(array('filter' => array($qF)));
      return;
    }
    
    $task = $FACTORIES::getTaskFactory()->get(intval($_POST['task']));
    if (!$task) {
      UI::printError("ERROR", "Invalid task!");
    }
    $qF = new QueryFilter("agentId", $this->agent->getId(), "=");
    $assignments = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF)));
    
    //determine benchmark number
    $benchmark = 0;
    $qF1 = new ComparisonFilter("solveTime", "dispatchTime", ">");
    $qF2 = new ComparisonFilter("progress", "length", "=");
    $qF3 = new ContainFilter("state", array("4, 5"));
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
      $assignment = new Assignment(0, $task->getId(), $this->agent->getId(), $benchmark, $task->getAutoAdjust(), 0);
      $FACTORIES::getAssignmentFactory()->save($assignment);
    }
    if (isset($_GET['task'])) {
      header("Location: tasks.php?id=" . intval($_GET['task']));
      die();
    }
  }
  
  private function delete() {
    global $FACTORIES;
    
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    if ($this->deleteDependencies()) {
      $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
    }
    else {
      $FACTORIES::getAgentFactory()->getDB()->query("ROLLBACK");
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
  
  private function deleteDependencies() {
    global $FACTORIES;
    
    //TODO: update agant deletion function
    
    $DB = $FACTORIES::getagentsFactory()->getDB();
    
    $vysledek1 = $DB->query("DELETE FROM assignments WHERE agent=" . $agent->getId());
    $vysledek2 = $vysledek1 && $DB->query("DELETE FROM errors WHERE agent=" . $agent->getId());
    $vysledek3 = $vysledek2 && $DB->query("DELETE FROM hashlistusers WHERE agent=" . $agent->getId());
    $vysledek4 = $vysledek3 && $DB->query("DELETE FROM zapqueue WHERE agent=" . $agent->getId());
    
    // orphan the chunks
    $vysledek5 = $vysledek4 && $DB->query("UPDATE hashes JOIN chunks ON hashes.chunk=chunks.id AND chunks.agent=" . $agent->getId() . " SET chunk=NULL");
    $vysledek6 = $vysledek5 && $DB->query("UPDATE hashes_binary JOIN chunks ON hashes_binary.chunk=chunks.id AND chunks.agent=" . $agent->getId() . " SET chunk=NULL");
    $vysledek7 = $vysledek6 && $DB->query("UPDATE chunks SET agent=NULL WHERE agent=" . $agent->getId());
    
    $vysledek8 = $vysledek7 && $DB->query("DELETE FROM agents WHERE id=" . $agent->getId());
    
    return ($vysledek8);
  }
  
  private function toggleActive() {
    global $FACTORIES;
    
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
      $this->agent->setUserId(0);
      $FACTORIES::getAgentFactory()->update($this->agent);
    }
    $user = $FACTORIES::getUserFactory()->get(intval($_POST["owner"]));
    if (!$user) {
      UI::printError("ERROR", "Invalid user selected!");
    }
    $this->agent->setUserId($user->getId());
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