<?php

use DBA\AccessGroupAgent;
use DBA\Agent;
use DBA\AgentError;
use DBA\AgentZap;
use DBA\Assignment;
use DBA\Chunk;
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
    global $ACCESS_CONTROL, $LOGIN;

    try{
      switch ($action) {
        case DAgentAction::CLEAR_ERRORS:
          $ACCESS_CONTROL->checkPermission(DAgentAction::CLEAR_ERRORS_PERM);
          $this->clearErrors();
          break;
        case DAgentAction::RENAME_AGENT:
          $ACCESS_CONTROL->checkPermission(DAgentAction::RENAME_AGENT_PERM);
          AgentUtils::rename($_POST['agentId'], $_POST['name'], $LOGIN->getUser());
          break;
        case DAgentAction::SET_OWNER:
          $ACCESS_CONTROL->checkPermission(DAgentAction::SET_OWNER_PERM);
          $this->changeOwner($_POST['owner']);
          break;
        case DAgentAction::SET_TRUSTED:
          $ACCESS_CONTROL->checkPermission(DAgentAction::SET_TRUSTED_PERM);
          $this->changeTrusted($_POST["trusted"]);
          break;
        case DAgentAction::SET_IGNORE:
          $ACCESS_CONTROL->checkPermission(DAgentAction::SET_IGNORE_PERM);
          $this->changeIgnoreErrors($_POST["ignore"]);
          break;
        case DAgentAction::SET_PARAMETERS:
          $ACCESS_CONTROL->checkPermission(DAgentAction::SET_PARAMETERS_PERM);
          AgentUtils::changeCmdParameters($_POST['agentId'], $_POST["cmdpars"], $LOGIN->getUser());
          break;
        case DAgentAction::SET_ACTIVE:
          $ACCESS_CONTROL->checkPermission(DAgentAction::SET_ACTIVE_PERM);
          AgentUtils::setActive($_POST['agentId'], false, $LOGIN->getUser(), true);
          break;
        case DAgentAction::DELETE_AGENT:
          $ACCESS_CONTROL->checkPermission(DAgentAction::DELETE_AGENT_PERM);
          AgentUtils::delete($_POST['agentId'], $LOGIN->getUser());
          break;
        case DAgentAction::ASSIGN_AGENT:
          $ACCESS_CONTROL->checkPermission(DAgentAction::ASSIGN_AGENT_PERM);
          AgentUtils::assign($_POST['agentId'], $_POST['task'], $LOGIN->getUser());
          break;
        case DAgentAction::CREATE_VOUCHER:
          $ACCESS_CONTROL->checkPermission(DAgentAction::CREATE_VOUCHER_PERM);
          AgentUtils::createVoucher($_POST["newvoucher"]);
          break;
        case DAgentAction::DELETE_VOUCHER:
          $ACCESS_CONTROL->checkPermission(DAgentAction::DELETE_VOUCHER_PERM);
          AgentUtils::deleteVoucher($_POST['voucher']);
          break;
        case DAgentAction::DOWNLOAD_AGENT:
          $ACCESS_CONTROL->checkPermission(DAgentAction::DOWNLOAD_AGENT_PERM);
          $this->downloadAgent($_POST['binary']);
          break;
        case DAgentAction::SET_CPU:
          $ACCESS_CONTROL->checkPermission(DAgentAction::SET_CPU_PERM);
          AgentUtils::setAgentCpu($_POST['agentId'], $_POST['cpuOnly'], $LOGIN->getUser());
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    } catch(HTException $e){
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }

  /**
   * @param int $binaryId 
   * @throws HTException 
   */
  public function downloadAgent($binaryId) {
    global $FACTORIES;

    $agentBinary = $FACTORIES::getAgentBinaryFactory()->get($binaryId);
    if ($agentBinary == null) {
      throw new HTException("Invalid Agent Binary!");
    }
    $filename = $agentBinary->getFilename();
    if (!file_exists(dirname(__FILE__) . "/../../bin/" . $filename)) {
      throw new HTException("Agent Binary not present on server!");
    }
    header("Content-Type: application/force-download");
    header("Content-Description: " . $filename);
    header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
    echo file_get_contents(dirname(__FILE__) . "/../../bin/" . $filename);
    die();
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
}
