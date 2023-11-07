<?php
use DBA\Factory;

use DBA\CrackerBinary;
use DBA\HealthCheck;
use DBA\HealthCheckAgent;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class HealthCheckAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/healthchecks";
    }
 
    public static function getDBAclass(): string {
      return HealthCheck::class;
    }

    public static function getExpandables(): array {
      return ['crackerBinary', 'healthCheckAgents'];
    }
 
    protected static  function fetchExpandObjects(array $objects, string $expand): mixed {     
      /* Ensure we receive the proper type */
      array_walk($objects, function($obj) { assert($obj instanceof HealthCheck); });

      /* Expand requested section */
      switch($expand) {
        case 'crackerBinary':
          return self::getForeignKeyRelation(
            $objects,
            HealthCheck::CRACKER_BINARY_ID,
            Factory::getCrackerBinaryFactory(),
            CrackerBinary::CRACKER_BINARY_ID
          );
        case 'healthCheckAgents':
          return self::getManyToOneRelation(
            $objects,
            HealthCheck::HEALTH_CHECK_ID,
            Factory::getHealthCheckAgentFactory(),
            HealthCheckAgent::HEALTH_CHECK_ID
          );
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
      }
    }
    
    protected function createObject(array $data): int {
      $obj = HealthUtils::createHealthCheck(
        $data[HealthCheck::HASHTYPE_ID],
        $data[HealthCheck::CHECK_TYPE],
        $data[HealthCheck::CRACKER_BINARY_ID]
      );

      return $obj->getId();
    }

    protected function deleteObject(object $object): void {
      HealthUtils::deleteHealthCheck($object->getId());
    }
}

HealthCheckAPI::register($app);