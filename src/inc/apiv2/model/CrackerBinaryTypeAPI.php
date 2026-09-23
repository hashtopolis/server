<?php

namespace Hashtopolis\inc\apiv2\model;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\utils\CrackerUtils;

use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\models\Task;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\Util;


/**
 * @extends AbstractModelAPI<CrackerBinaryType>
 */
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
        'filterACL' => true,
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
    // because it is currently not settable by the user and not fully supported yet
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
   * @param CrackerBinaryType $object
   * @throws HTException
   * @throws HttpForbidden
   * @throws Exception
   */
  protected function deleteObject(AbstractModel $object): void {
    $currentUser = $this->getCurrentUser();
    $rightGroup = Factory::getRightGroupFactory()->get($currentUser->getRightGroupId());
    if ($rightGroup->getPermissions() !== 'ALL') {
      $accessGroupIds = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($currentUser));
      $filter = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $object->getId(), '=');
      $binaries = Factory::getCrackerBinaryFactory()->filter([Factory::FILTER => $filter]);
      foreach ($binaries as $binary) {
        if (!in_array($binary->getAccessGroupId(), $accessGroupIds)) {
          throw new HttpForbidden("No access to all cracker binaries of this type!", 403);
        }
      }
    }

    CrackerUtils::deleteBinaryType($object->getId());
  }
}
