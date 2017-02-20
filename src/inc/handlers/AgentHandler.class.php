<?php
use DBA\Agent;
use DBA\AgentError;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ComparisonFilter;
use DBA\ContainFilter;
use DBA\Hash;
use DBA\HashBinary;
use DBA\HashlistAgent;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\RegVoucher;

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
    /** @var Login $LOGIN */
    global $LOGIN;
    
    switch ($action) {
      case 'clearerrors':
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->clearErrors();
        break;
      case 'agentrename':
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->rename();
        break;
      case 'agentowner':
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->changeOwner();
        break;
      case 'agenttrusted':
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
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
      case 'agentactive':
        $this->toggleActive();
        break;
      case 'agentdelete':
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
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
      case 'downloadagent':
        $this->downloadAgent();
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function downloadAgent(){
    global $FACTORIES, $binaryId;
    
    $agentBinary = $FACTORIES::getAgentBinaryFactory()->get($binaryId);
    if($agentBinary == null){
      UI::printError("ERROR", "Invalid Agent Binary!");
    }
    $filename = $agentBinary->getFilename();
    if(!file_exists(dirname(__FILE__)."/../../static/".$filename)) {
      UI::printError("ERROR", "Agent Binary not present on server!");
    }
    header("Content-Type: application/force-download");
    header("Content-Description: " . $filename);
    header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
    echo file_get_contents(dirname(__FILE__)."/../../static/".$filename);
    die();
  }
  
  private function assign() {
    global $FACTORIES;
    
    if ($this->agent == null) {
      $this->agent = $FACTORIES::getAgentFactory()->get($_POST['agent']);
    }
    
    if (intval($_POST['task']) == 0) {
      //unassign
      $qF = new QueryFilter(Agent::AGENT_ID, $this->agent->getId(), "=");
      $FACTORIES::getAssignmentFactory()->massDeletion(array($FACTORIES::FILTER => array($qF)));
      if (isset($_GET['task'])) {
        header("Location: tasks.php?id=" . intval($_GET['task']));
        die();
      }
      return;
    }
    
    $this->agent = $FACTORIES::getAgentFactory()->get($_POST['agentId']);
    if($this->agent == null){
      $this->agent = $FACTORIES::getAgentFactory()->get($_POST['agent']);
    }
    if ($this->agent == null) {
      UI::printError("FATAL", "Agent with ID " . $_POST['agentId'] . " not found!");
    }
    
    $task = $FACTORIES::getTaskFactory()->get(intval($_POST['task']));
    if (!$task) {
      UI::printError("ERROR", "Invalid task!");
    }
    $qF = new QueryFilter(Agent::AGENT_ID, $_POST['agentId'], "=");
    $assignments = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF)));
    
    //determine benchmark number
    $benchmark = 0;
    $qF1 = new ComparisonFilter(Chunk::SOLVE_TIME, Chunk::DISPATCH_TIME, ">");
    $qF2 = new ComparisonFilter(Chunk::PROGRESS, Chunk::LENGTH, "=");
    $qF3 = new ContainFilter(Chunk::STATE, array(DHashcatStatus::EXHAUSTED, DHashcatStatus::CRACKED));
    $qF4 = new QueryFilter(Chunk::AGENT_ID, $this->agent->getId(), "=");
    $qF5 = new QueryFilter(Chunk::TASK_ID, $task->getId(), "=");
    $oF = new OrderFilter(Chunk::SOLVE_TIME, "DESC");
    $entries = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2, $qF3, $qF4, $qF5), 'order' => array($oF)));
    if (sizeof($entries) > 0) {
      $benchmark = Util::cast($entries[0], Chunk::class)->getLength();
    }
    unset($entries);
    
    if (sizeof($assignments) > 0) {
      for ($i = 1; $i < sizeof($assignments); $i++) { // clean up if required
        $FACTORIES::getAssignmentFactory()->delete($assignments[$i]);
      }
      $assignment = Util::cast($assignments[0], Assignment::class);
      $assignment->setTaskId($task->getId());
      $assignment->setBenchmark($benchmark);
      $FACTORIES::getAssignmentFactory()->update($assignment);
    }
    else {
      $assignment = new Assignment(0, $task->getId(), $this->agent->getId(), $benchmark);
      $FACTORIES::getAssignmentFactory()->save($assignment);
    }
    if (isset($_GET['task'])) {
      header("Location: tasks.php?id=" . intval($_GET['task']));
      die();
    }
  }
  
  private function delete() {
    /** @var $LOGIN Login */
    global $FACTORIES, $LOGIN;
    
    $FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
    $this->agent = $FACTORIES::getAgentFactory()->get($_POST['agent']);
    if ($this->agent == null) {
      UI::printError("FATAL", "Agent with ID " . $_POST['agent'] . " not found!");
    }
    $name = $this->agent->getAgentName();
    if ($this->deleteDependencies($this->agent)) {
      $FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
      Util::createLogEntry("User", $LOGIN->getUserID(), "INFO", "Agent " . $name . " got deleted.");
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
  
  private function deleteDependencies($agent) {
    global $FACTORIES;
    
    if ($agent == null) {
      $agent = $FACTORIES::getAgentFactory()->get($_POST['agent']);
      if ($agent == null) {
        UI::printError("ERROR", "Invalid agent!");
      }
    }
    
    $qF = new QueryFilter(Assignment::AGENT_ID, $agent->getId(), "=");
    $FACTORIES::getAssignmentFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $qF = new QueryFilter(AgentError::AGENT_ID, $agent->getId(), "=");
    $FACTORIES::getAgentErrorFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $qF = new QueryFilter(HashlistAgent::AGENT_ID, $agent->getId(), "=");
    $FACTORIES::getHashlistAgentFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    //TODO: delete from Zap
    $uS = new UpdateSet(Chunk::CHUNK_ID, null);
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
    $chunkIds = array();
    foreach ($chunks as $chunk) {
      $chunk = Util::cast($chunk, Chunk::class);
      $chunkIds[] = $chunk->getId();
    }
    if (sizeof($chunks) > 0) {
      $containFilter = new ContainFilter(Hash::CHUNK_ID, $chunkIds);
      $FACTORIES::getHashFactory()->massUpdate(array($FACTORIES::FILTER => $containFilter, $FACTORIES::UPDATE => $uS));
      $containFilter = new ContainFilter(HashBinary::CHUNK_ID, $chunkIds);
      $FACTORIES::getHashBinaryFactory()->massUpdate(array($FACTORIES::FILTER => $containFilter, $FACTORIES::UPDATE => $uS));
      $uS = new UpdateSet(Chunk::AGENT_ID, null);
      $FACTORIES::getChunkFactory()->massUpdate(array($FACTORIES::FILTER => $qF, $FACTORIES::UPDATE => $uS));
    }
    $FACTORIES::getAgentFactory()->delete($agent);
    return true;
  }
  
  private function toggleActive() {
    global $FACTORIES;
    
    if ($this->agent == null) {
      $this->agent = $FACTORIES::getAgentFactory()->get($_POST['agent']);
      if ($this->agent == null) {
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
  
  private function changeCmdParameters() {
    global $FACTORIES;
    
    $pars = htmlentities($_POST["cmdpars"], false, "UTF-8");

    if(Util::containsBlacklistedChars($pars)){
      UI::addMessage(UI::ERROR, "Parameters must contain no blacklisted characters!");
      return;
    }
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
    
    $qF = new QueryFilter(AgentError::AGENT_ID, $this->agent->getId(), "=");
    $FACTORIES::getAgentErrorFactory()->massDeletion(array($FACTORIES::FILTER => array($qF)));
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