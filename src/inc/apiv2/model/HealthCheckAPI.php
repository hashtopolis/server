<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\HealthCheck;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\utils\HealthUtils;
use Hashtopolis\inc\HTException;


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
      'hashType' => [
        'key' => HealthCheck::HASHTYPE_ID,
        
        'relationType' => HashType::class,
        'relationKey' => HashType::HASH_TYPE_ID,
      ]
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
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    $healthCheck = HealthUtils::createHealthCheck(
      $data[HealthCheck::HASHTYPE_ID],
      $data[HealthCheck::CHECK_TYPE],
      $data[HealthCheck::CRACKER_BINARY_ID]
    );
    
    return $healthCheck->getId();
  }
  
  /**
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
    HealthUtils::deleteHealthCheck($object->getId());
  }
}
