<?php

use DBA\AccessGroupAgent;
use DBA\Agent;
use DBA\AgentError;
use DBA\AgentZap;
use DBA\Assignment;
use DBA\Chunk;
use DBA\ContainFilter;
use DBA\Hash;
use DBA\HashBinary;
use DBA\NotificationSetting;
use DBA\QueryFilter;
use DBA\RegVoucher;
use DBA\Zap;

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
      case DAgentAction::CLEAR_ERRORS:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->clearErrors();
        break;
      case DAgentAction::RENAME_AGENT:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER && $this->agent->getUserId() != $LOGIN->getUserID()) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->rename($_POST['name']);
        break;
      case DAgentAction::SET_OWNER:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->changeOwner($_POST['owner']);
        break;
      case DAgentAction::SET_TRUSTED:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->changeTrusted($_POST["trusted"]);
        break;
      case DAgentAction::SET_IGNORE:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER && $this->agent->getUserId() != $LOGIN->getUserID()) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->changeIgnoreErrors($_POST["ignore"]);
        break;
      case DAgentAction::SET_PARAMETERS:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER && $this->agent->getUserId() != $LOGIN->getUserID()) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->changeCmdParameters($_POST["cmdpars"]);
        break;
      case DAgentAction::SET_ACTIVE:
        $this->toggleActive();
        break;
      case DAgentAction::DELETE_AGENT:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->delete($_POST['agentId']);
        break;
      case DAgentAction::ASSIGN_AGENT:
        $this->assign();
        break;
      case DAgentAction::CREATE_VOUCHER:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->createVoucher($_POST["newvoucher"]);
        break;
      case DAgentAction::DELETE_VOUCHER:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->deleteVoucher();
        break;
      case DAgentAction::DOWNLOAD_AGENT:
        $this->downloadAgent($_POST['binary']);
        break;
      case DAgentAction::SET_CPU:
        if ($LOGIN->getLevel() < DAccessLevel::SUPERUSER && $this->agent->getUserId() != $LOGIN->getUserID()) {
          UI::printError("ERROR", "You have no rights to execute this action!");
        }
        $this->setAgentCpu($_POST['cpuOnly']);
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function setAgentCpu($cpuOnly) {
    global $FACTORIES;
    
    $cpuOnly = ($cpuOnly == 1) ? 1 : 0;
    $this->agent->setCpuOnly($cpuOnly);
    $FACTORIES::getAgentFactory()->update($this->agent);
  }
  
  public function downloadAgent($binaryId) {
    global $FACTORIES;
    
    $agentBinary = $FACTORIES::getAgentBinaryFactory()->get($binaryId);
    if ($agentBinary == null) {
      UI::printError("ERROR", "Invalid Agent Binary!");
    }
    $filename = $agentBinary->getFilename();
    if (!file_exists(dirname(__FILE__) . "/../../bin/" . $filename)) {
      UI::printError("ERROR", "Agent Binary not present on server!");
    }
    header("Content-Type: application/force-download");
    header("Content-Description: " . $filename);
    header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
    echo file_get_contents(dirname(__FILE__) . "/../../bin/" . $filename);
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
    $task = $FACTORIES::getTaskFactory()->get(intval($_POST['task']));
    if ($this->agent == null) {
      $this->agent = $FACTORIES::getAgentFactory()->get($_POST['agent']);
    }
    else if ($this->agent == null) {
      UI::printError("FATAL", "Agent with ID " . $_POST['agentId'] . " not found!");
    }
    else if ($task == null) {
      UI::printError("ERROR", "Invalid task!");
    }
    else if (!AccessUtils::agentCanAccessTask($this->agent, $task)) {
      UI::printError("ERROR", "Task cannot be accessed by this agent!");
    }
    
    $qF = new QueryFilter(Assignment::TASK_ID, $task->getId(), "=");
    $assignments = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => $qF));
    if ($task->getIsSmall() && sizeof($assignments) > 0) {
      UI::printError("ERROR", "You cannot assign agent to this task as the limit of assignments is reached!");
    }
    
    $qF = new QueryFilter(Agent::AGENT_ID, $this->agent->getId(), "=");
    $assignments = $FACTORIES::getAssignmentFactory()->filter(array($FACTORIES::FILTER => array($qF)));
    
    $benchmark = 0;
    if (sizeof($assignments) > 0) {
      for ($i = 1; $i < sizeof($assignments); $i++) { // clean up if required
        $FACTORIES::getAssignmentFactory()->delete($assignments[$i]);
      }
      $assignment = $assignments[0];
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
  
  private function delete($agentId) {
    /** @var $LOGIN Login */
    global $FACTORIES, $LOGIN;
    
    $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
    $this->agent = $FACTORIES::getAgentFactory()->get($agentId);
    if ($this->agent == null) {
      UI::printError("FATAL", "Agent with ID " . $agentId . " not found!");
    }
    $name = $this->agent->getAgentName();
    $agent = $this->agent;
    
    $payload = new DataSet(array(DPayloadKeys::AGENT => $agent));
    NotificationHandler::checkNotifications(DNotificationType::DELETE_AGENT, $payload);
    
    if ($this->deleteDependencies($this->agent)) {
      $FACTORIES::getAgentFactory()->getDB()->commit();
      Util::createLogEntry("User", $LOGIN->getUserID(), DLogEntry::INFO, "Agent " . $name . " got deleted.");
    }
    else {
      $FACTORIES::getAgentFactory()->getDB()->rollBack();
      UI::printError("FATAL", "Error occured on deletion of agent!");
    }
  }
  
  private function deleteVoucher() {
    global $FACTORIES;
    
    $voucher = $FACTORIES::getRegVoucherFactory()->get(intval($_POST["voucher"]));
    $FACTORIES::getRegVoucherFactory()->delete($voucher);
  }
  
  private function createVoucher($newVoucher) {
    global $FACTORIES;
    
    $key = htmlentities($newVoucher, ENT_QUOTES, "UTF-8");
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
    $qF = new QueryFilter(NotificationSetting::OBJECT_ID, $agent->getId(), "=");
    $notifications = $FACTORIES::getNotificationSettingFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach ($notifications as $notification) {
      if (DNotificationType::getObjectType($notification->getAction()) == DNotificationObjectType::AGENT) {
        $FACTORIES::getNotificationSettingFactory()->delete($notification);
      }
    }
    $qF = new QueryFilter(AgentError::AGENT_ID, $agent->getId(), "=");
    $FACTORIES::getAgentErrorFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $qF = new QueryFilter(AgentZap::AGENT_ID, $agent->getId(), "=");
    $FACTORIES::getAgentZapFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $qF = new QueryFilter(Zap::AGENT_ID, $agent->getId(), "=");
    $FACTORIES::getZapFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    $qF = new QueryFilter(AccessGroupAgent::AGENT_ID, $agent->getId(), "=");
    $FACTORIES::getAccessGroupAgentFactory()->massDeletion(array($FACTORIES::FILTER => $qF));
    
    $uS = new UpdateSet(Chunk::CHUNK_ID, null);
    $chunks = $FACTORIES::getChunkFactory()->filter(array($FACTORIES::FILTER => $qF));
    $chunkIds = array();
    foreach ($chunks as $chunk) {
      $chunkIds[] = $chunk->getId();
    }
    if (sizeof($chunks) > 0) {
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
  
  private function changeCmdParameters($cmdParameters) {
    global $FACTORIES;
    
    $pars = htmlentities($cmdParameters, ENT_QUOTES, "UTF-8");
    
    if (Util::containsBlacklistedChars($pars)) {
      UI::addMessage(UI::ERROR, "Parameters must contain no blacklisted characters!");
      return;
    }
    $this->agent->setCmdPars($pars);
    $FACTORIES::getAgentFactory()->update($this->agent);
  }
  
  private function changeIgnoreErrors($ignoreErrors) {
    global $FACTORIES;
    
    $ignore = intval($ignoreErrors);
    if ($ignore != 0 && $ignore != 1) {
      UI::printError("ERROR", "Invalid Ignore state!");
    }
    $this->agent->setIgnoreErrors($ignore);
    $FACTORIES::getAgentFactory()->update($this->agent);
  }
  
  private function changeTrusted($isTrusted) {
    /** @var $LOGIN Login */
    global $FACTORIES, $LOGIN;
    
    $trusted = intval($isTrusted);
    if ($trusted != 0 && $trusted != 1) {
      UI::printError("ERROR", "Invalid trusted state!");
    }
    $this->agent->setIsTrusted($trusted);
    Util::createLogEntry(DLogEntryIssuer::USER, $LOGIN->getUserID(), DLogEntry::INFO, "Trust status for agent " . $this->agent->getAgentName() . " was changed to " . $this->agent->getIsTrusted());
    $FACTORIES::getAgentFactory()->update($this->agent);
  }
  
  private function changeOwner($owner) {
    /** @var $LOGIN Login */
    global $FACTORIES, $LOGIN;
    
    if ($owner == 0) {
      $this->agent->setUserId(null);
      $username = "NONE";
      $FACTORIES::getAgentFactory()->update($this->agent);
    }
    else {
      $user = $FACTORIES::getUserFactory()->get(intval($owner));
      if (!$user) {
        UI::printError("ERROR", "Invalid user selected!");
      }
      $username = $user->getUsername();
      $this->agent->setUserId($user->getId());
    }
    Util::createLogEntry(DLogEntryIssuer::USER, $LOGIN->getUserID(), DLogEntry::INFO, "Owner for agent " . $this->agent->getAgentName() . " was changed to " . $username);
    $FACTORIES::getAgentFactory()->update($this->agent);
  }
  
  private function clearErrors() {
    global $FACTORIES;
    
    $qF = new QueryFilter(AgentError::AGENT_ID, $this->agent->getId(), "=");
    $FACTORIES::getAgentErrorFactory()->massDeletion(array($FACTORIES::FILTER => array($qF)));
  }
  
  private function rename($newname) {
    global $FACTORIES;
    
    $name = htmlentities($newname, ENT_QUOTES, "UTF-8");
    if (strlen($name) > 0) {
      $this->agent->setAgentName($name);
      $FACTORIES::getAgentFactory()->update($this->agent);
    }
  }
}