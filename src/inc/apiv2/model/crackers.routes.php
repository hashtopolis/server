<?php

use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\CrackerBinary;
use DBA\CrackerBinaryType;
use DBA\Task;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class CrackerBinaryAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/crackers";
  }
  
  public static function getDBAclass(): string {
    return CrackerBinary::class;
  }
  
  public static function getToOneRelationships(): array {
    return [
      'crackerBinaryType' => [
        'key' => CrackerBinary::CRACKER_BINARY_TYPE_ID,
        
        'relationType' => CrackerBinaryType::class,
        'relationKey' => CrackerBinaryType::CRACKER_BINARY_TYPE_ID,
      ],
    ];
  }
  
  public static function getToManyRelationships(): array {
    return [
      'tasks' => [
        'key' => CrackerBinary::CRACKER_BINARY_ID,
        
        'relationType' => Task::class,
        'relationKey' => Task::CRACKER_BINARY_ID,
      ],
    ];
  }
  
  /**
   * @throws HttpError
   * @throws HTException
   */
  protected function createObject(array $data): int {
    $binary = CrackerUtils::createBinary(
      $data[CrackerBinary::VERSION],
      $data[CrackerBinary::BINARY_NAME],
      $data[CrackerBinary::DOWNLOAD_URL],
      $data[CrackerBinary::CRACKER_BINARY_TYPE_ID]
    );
    return $binary->getId();
  }
  
  /**
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
    CrackerUtils::deleteBinary($object->getId());
  }
}

use Slim\App;
/** @var App $app */
CrackerBinaryAPI::register($app);