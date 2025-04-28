<?php

use DBA\Agent;
use DBA\Task;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class AssignAgentHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/assignAgent";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Agent::PERM_UPDATE, Task::PERM_UPDATE];
  }
  
  /**
   * The agentId is the Id of the agent that has to be assigned to the task.
   * The taskId is the Id of the task that will be assigned to the agent. If this is set to 0,
   * the agent will be unassigned from its current assigned task.
   */
  public function getFormFields(): array {
    return [
      Agent::AGENT_ID => ["type" => "int"],
      Task::TASK_ID => ["type" => "int"],
    ];
  }

  public static function getResponse(): array {
    return ["Assign" => "Success"];
  }
  
  /**
   * This endpoint is responsible for assigning a task to a specific agent.
   */
  public function actionPost($data): object|array|null {
    AgentUtils::assign($data[Agent::AGENT_ID], $data[Task::TASK_ID], $this->getCurrentUser());
    
    return self::getResponse();
  }
}

AssignAgentHelperAPI::register($app);