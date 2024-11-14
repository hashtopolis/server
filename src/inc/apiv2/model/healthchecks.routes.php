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


    public static function getToOneRelationships(): array {
      return [
        'crackerBinary' => [
          'key' => HealthCheck::CRACKER_BINARY_ID, 

          'relationType' => CrackerBinary::class,
          'relationKey' => CrackerBinary::CRACKER_BINARY_ID,
        ],
      ];
    }

    public static function getToManyRelationships(): array {
      return [
        'healthCheckAgents' => [
          'key' => HealthCheck::HEALTH_CHECK_ID,
          
          'relationType' => HealthCheckAgent::class,
          'relationKey' => HealthCheckAgent::HEALTH_CHECK_ID,        
        ],
      ];
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