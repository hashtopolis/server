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

    public static function getExpandables(): array {
      return ['agent', 'healthCheck'];
    }

    protected static function fetchExpandObjects(array $objects, string $expand): mixed {     
      /* Ensure we receive the proper type */
      array_walk($objects, function($obj) { assert($obj instanceof HealthCheckAgent); });

      /* Expand requested section */
      switch($expand) {
        case 'agent':
          return self::getForeignKeyRelation(
            $objects,
            HealthCheckAgent::AGENT_ID,
            Factory::getAgentFactory(),
            Agent::AGENT_ID
          );
          case 'healthCheck':
            return self::getForeignKeyRelation(
              $objects,
              HealthCheckAgent::HEALTH_CHECK_ID,
              Factory::getHealthCheckFactory(),
              HealthCheck::HEALTH_CHECK_ID
            );  
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
      }
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