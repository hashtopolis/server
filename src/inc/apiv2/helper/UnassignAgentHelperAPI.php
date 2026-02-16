<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\inc\utils\AgentUtils;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Task;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\HTException;

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
   * @throws HttpError
   */
  public function actionPost($data): object|array|null {
    AgentUtils::assign($data[Agent::AGENT_ID], 0, $this->getCurrentUser());
    
    return $this->getResponse();
  }
}
