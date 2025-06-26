<?php
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\CrackerBinary;
use DBA\CrackerBinaryType;
use DBA\Task;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


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
   * @throws HTException
   */
  protected function createObject(array $data): int {
      CrackerUtils::createBinaryType($data[CrackerBinaryType::TYPE_NAME]);

      /* On successfully insert, return ID */
      $qFs = [
        new QueryFilter(CrackerBinaryType::TYPE_NAME, $data[CrackerBinaryType::TYPE_NAME], '=')
      ];

      /* Hackish way to retrieve object since Id is not returned on creation */
      $oF = new OrderFilter(CrackerBinaryType::CRACKER_BINARY_TYPE_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) == 1);
      
      return $objects[0]->getId();
    }
  
  
  /**
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
      CrackerUtils::deleteBinaryType($object->getId());
    }
}

CrackerBinaryTypeAPI::register($app);