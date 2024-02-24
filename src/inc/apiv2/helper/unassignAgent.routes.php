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
  
  public function getFormFields(): array {
    return [
      Agent::AGENT_ID => ["type" => "int"],
    ];
  }
  
  public function actionPost($data): array|null {
    AgentUtils::assign($data[Agent::AGENT_ID], 0, $this->getCurrentUser());
    
    # TODO: Check how to handle custom return messages that are not object, probably we want that to be in some kind of standardized form.
    return ["unassign" => "success"];
  }
}

UnassignAgentHelperAPI::register($app);