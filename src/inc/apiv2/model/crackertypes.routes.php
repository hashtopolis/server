<?php
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\CrackerBinary;
use DBA\CrackerBinaryType;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class CrackerBinaryTypeAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/crackertypes";
    }

    public static function getDBAclass(): string {
      return CrackerBinaryType::class;
    }

    public function getExpandables(): array {
      return ["crackerVersions"];
    }

    protected function fetchExpandObjects(array $objects, string $expand): mixed {     
      /* Ensure we receive the proper type */
      array_walk($objects, function($obj) { assert($obj instanceof CrackerBinaryType); });

      /* Expand requested section */
      switch($expand) {
        case 'crackerVersions':
          return $this->getManyToOneRelation(
            $objects,
            CrackerBinaryType::CRACKER_BINARY_TYPE_ID,
            Factory::getCrackerBinaryFactory(),
            CrackerBinary::CRACKER_BINARY_TYPE_ID
          );
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
      }
    }
  
    protected function createObject(array $data): int {
      CrackerUtils::createBinaryType($data[CrackerBinaryType::TYPE_NAME]);

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(CrackerBinaryType::TYPE_NAME, $data[CrackerBinaryType::TYPE_NAME], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(CrackerBinaryType::CRACKER_BINARY_TYPE_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) == 1);
      
      return $objects[0]->getId();
    }


    protected function deleteObject(object $object): void {
      CrackerUtils::deleteBinaryType($object->getId());
    }
}

CrackerBinaryTypeAPI::register($app);