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
    /** @var $LOGIN Login */
    global $ACCESS_CONTROL, $LOGIN;

    try {
      switch ($action) {
        case DAgentAction::CLEAR_ERRORS:
          $ACCESS_CONTROL->checkPermission(DAgentAction::CLEAR_ERRORS_PERM);
          AgentUtils::clearErrors($_POST['agentId'], $LOGIN->getUser());
          break;
        case DAgentAction::RENAME_AGENT:
          $ACCESS_CONTROL->checkPermission(DAgentAction::RENAME_AGENT_PERM);
          AgentUtils::rename($_POST['agentId'], $_POST['name'], $LOGIN->getUser());
          break;
        case DAgentAction::SET_OWNER:
          $ACCESS_CONTROL->checkPermission(DAgentAction::SET_OWNER_PERM);
          AgentUtils::changeOwner($_POST['agentId'], $_POST['owner'], $LOGIN->getUser());
          break;
        case DAgentAction::SET_TRUSTED:
          $ACCESS_CONTROL->checkPermission(DAgentAction::SET_TRUSTED_PERM);
          AgentUtils::setTrusted($_POST['agentId'], $_POST["trusted"], $LOGIN->getUser());
          break;
        case DAgentAction::SET_IGNORE:
          $ACCESS_CONTROL->checkPermission(DAgentAction::SET_IGNORE_PERM);
          AgentUtils::changeIgnoreErrors($_POST['agentId'], $_POST['ignore'], $LOGIN->getUser());
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
    }
    catch (HTException $e) {
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
