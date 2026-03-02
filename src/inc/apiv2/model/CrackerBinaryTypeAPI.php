<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\inc\utils\CrackerUtils;

use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\models\Task;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\HTException;


class CrackerBinaryTypeAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/crackertypes";
  }
  
  public static function getDBAclass(): string {
    return CrackerBinaryType::class;
  }
  
  
  public static function getToManyRelationships(): array {
    return [
      'crackerVersions' => [
        'key' => CrackerBinaryType::CRACKER_BINARY_TYPE_ID,
        
        'relationType' => CrackerBinary::class,
        'relationKey' => CrackerBinary::CRACKER_BINARY_TYPE_ID,
      ],
      'tasks' => [
        'key' => CrackerBinaryType::CRACKER_BINARY_TYPE_ID,
        
        'relationType' => Task::class,
        'relationKey' => Task::CRACKER_BINARY_TYPE_ID,
      ]
    ];
  }
  
  function getAllPostParameters(array $features): array {
    
    //for documentation purposes isChunkingAvailable has to be removed
    // because it is currently not settable by the user
    $features = parent::getAllPostParameters($features);
    unset($features[CrackerBinaryType::IS_CHUNKING_AVAILABLE]);
    return $features;
  }
  
  /**
   * @param array $data
   * @return int
   * @throws HttpConflict
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    $binaryType = CrackerUtils::createBinaryType($data[CrackerBinaryType::TYPE_NAME]);
    return $binaryType->getId();
  }
  
  
  /**
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
    CrackerUtils::deleteBinaryType($object->getId());
  }
}
