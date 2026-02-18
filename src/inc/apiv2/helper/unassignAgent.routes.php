<?php

use DBA\Agent;
use DBA\Task;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class UnassignAgentHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/unassignAgent";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Agent::PERM_UPDATE, Task::PERM_UPDATE];
  }
  
  /**
   * agentId is the id of the agent which you want to unassign.
   */
  public function getFormFields(): array {
    return [
      Agent::AGENT_ID => ["type" => "int"],
    ];
  }
  
  public static function getResponse(): array {
    return ["Unassign" => "Success"];
  }
  
  /**
   * Endpoint to unassign an agent.
   * @throws HTException
   */
  public function actionPost($data): object|array|null {
    AgentUtils::assign($data[Agent::AGENT_ID], 0, $this->getCurrentUser());
    
    return $this->getResponse();
  }
}

use Slim\App;
/** @var App $app */
UnassignAgentHelperAPI::register($app);