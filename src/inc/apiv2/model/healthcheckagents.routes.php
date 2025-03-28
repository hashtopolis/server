<?php
use DBA\Factory;

use DBA\Agent;
use DBA\HealthCheck;
use DBA\HealthCheckAgent;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class HealthCheckAgentAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/healthcheckagents";
    }

    public static function getAvailableMethods(): array {
      return ['GET'];
    }

    public static function getDBAclass(): string {
      return HealthCheckAgent::class;
    }

    public static function getToOneRelationships(): array {
      return [
        'agent' => [
          'key' => HealthCheckAgent::AGENT_ID, 

          'relationType' => Agent::class,
          'relationKey' => Agent::AGENT_ID,
        ],
        'healthCheck' => [
          'key' => HealthCheckAgent::HEALTH_CHECK_ID, 

          'relationType' => HealthCheck::class,
          'relationKey' => HealthCheck::HEALTH_CHECK_ID,
        ],
      ];
    }
    
    protected function createObject(array $object): int {
       /* Dummy code to implement abstract functions */
       assert(False, "HealthCheckAgents cannot be created via API");
       return -1;
    }

    public function updateObject(object $object, array $data, array $processed = []): void {
      assert(False, "HealthCheckAgents cannot be updated via API");
   }

    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "HealthCheckAgents cannot be deleted via API");
    }
}

HealthCheckAgentAPI::register($app);