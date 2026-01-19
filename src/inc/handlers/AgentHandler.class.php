<?php

use DBA\Factory;

class AgentHandler implements Handler {
  private $agent;
  
  public function __construct($agentId = null) {
    if ($agentId == null) {
      $this->agent = null;
      return;
    }
    
    $this->agent = Factory::getAgentFactory()->get($agentId);
    if ($this->agent == null) {
      UI::printError("FATAL", "Agent with ID $agentId not found!");
    }
  }
  
  public function handle($action) {
    try {
      switch ($action) {
        case DAgentAction::CLEAR_ERRORS:
          $agent = Factory::getAgentFactory()->get($_POST['agentId']);
          if ($agent == null || Login::getInstance()->getUserID() != $agent->getUserId()) {
            AccessControl::getInstance()->checkPermission(DAgentAction::CLEAR_ERRORS_PERM);
          }
          AgentUtils::clearErrors($_POST['agentId'], Login::getInstance()->getUser());
          break;
        case DAgentAction::RENAME_AGENT:
          $agent = Factory::getAgentFactory()->get($_POST['agentId']);
          if ($agent == null || Login::getInstance()->getUserID() != $agent->getUserId()) {
            AccessControl::getInstance()->checkPermission(DAgentAction::RENAME_AGENT_PERM);
          }
          AgentUtils::rename($_POST['agentId'], $_POST['name'], Login::getInstance()->getUser());
          break;
        case DAgentAction::SET_OWNER:
          AccessControl::getInstance()->checkPermission(DAgentAction::SET_OWNER_PERM);
          AgentUtils::changeOwner($_POST['agentId'], $_POST['owner'], Login::getInstance()->getUser());
          break;
        case DAgentAction::SET_TRUSTED:
          AccessControl::getInstance()->checkPermission(DAgentAction::SET_TRUSTED_PERM);
          AgentUtils::setTrusted($_POST['agentId'], $_POST["trusted"], Login::getInstance()->getUser());
          break;
        case DAgentAction::SET_IGNORE:
          $agent = Factory::getAgentFactory()->get($_POST['agentId']);
          if ($agent == null || Login::getInstance()->getUserID() != $agent->getUserId()) {
            AccessControl::getInstance()->checkPermission(DAgentAction::SET_IGNORE_PERM);
          }
          AgentUtils::changeIgnoreErrors($_POST['agentId'], $_POST['ignore'], Login::getInstance()->getUser());
          break;
        case DAgentAction::SET_PARAMETERS:
          $agent = Factory::getAgentFactory()->get($_POST['agentId']);
          if ($agent == null || Login::getInstance()->getUserID() != $agent->getUserId()) {
            AccessControl::getInstance()->checkPermission(DAgentAction::SET_PARAMETERS_PERM);
          }
          AgentUtils::changeCmdParameters($_POST['agentId'], $_POST["cmdpars"], Login::getInstance()->getUser());
          break;
        case DAgentAction::SET_ACTIVE:
          $agent = Factory::getAgentFactory()->get($_POST['agentId']);
          if ($agent == null || Login::getInstance()->getUserID() != $agent->getUserId()) {
            AccessControl::getInstance()->checkPermission(DAgentAction::SET_ACTIVE_PERM);
          }
          AgentUtils::setActive($_POST['agentId'], false, Login::getInstance()->getUser(), true);
          break;
        case DAgentAction::DELETE_AGENT:
          AccessControl::getInstance()->checkPermission(DAgentAction::DELETE_AGENT_PERM);
          AgentUtils::delete($_POST['agentId'], Login::getInstance()->getUser());
          break;
        case DAgentAction::ASSIGN_AGENT:
          AccessControl::getInstance()->checkPermission(DAgentAction::ASSIGN_AGENT_PERM);
          AgentUtils::assign(intval($_POST['agentId']), intval($_POST['task']), Login::getInstance()->getUser());
          break;
        case DAgentAction::CREATE_VOUCHER:
          AccessControl::getInstance()->checkPermission(DAgentAction::CREATE_VOUCHER_PERM);
          AgentUtils::createVoucher($_POST["newvoucher"]);
          break;
        case DAgentAction::DELETE_VOUCHER:
          AccessControl::getInstance()->checkPermission(DAgentAction::DELETE_VOUCHER_PERM);
          AgentUtils::deleteVoucher($_POST['voucher']);
          break;
        case DAgentAction::DOWNLOAD_AGENT:
          AccessControl::getInstance()->checkPermission(DAgentAction::DOWNLOAD_AGENT_PERM);
          $this->downloadAgent($_POST['binary']);
          break;
        case DAgentAction::SET_CPU:
          $agent = Factory::getAgentFactory()->get($_POST['agentId']);
          if ($agent == null || Login::getInstance()->getUserID() != $agent->getUserId()) {
            AccessControl::getInstance()->checkPermission(DAgentAction::SET_CPU_PERM);
          }
          AgentUtils::setAgentCpu($_POST['agentId'], $_POST['cpuOnly'], Login::getInstance()->getUser());
          break;
        default:
          UI::addMessage(UI::ERROR, "Invalid action!");
          break;
      }
    }
    catch (Exception $e) {
      UI::addMessage(UI::ERROR, $e->getMessage());
    }
  }
  
  /**
   * @param int $binaryId
   * @throws HTException
   */
  public function downloadAgent($binaryId) {
    $agentBinary = Factory::getAgentBinaryFactory()->get($binaryId);
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
}
