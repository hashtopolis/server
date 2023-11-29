<?php
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\CrackerBinary;
use DBA\CrackerBinaryType;

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


    protected function createObject(array $data): int {
      CrackerUtils::createBinary(
        $data[CrackerBinary::VERSION],
        $data[CrackerBinary::BINARY_NAME],
        $data[CrackerBinary::DOWNLOAD_URL],
        $data[CrackerBinary::CRACKER_BINARY_TYPE_ID]
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(CrackerBinary::VERSION, $data[CrackerBinary::VERSION], '='),
        new QueryFilter(CrackerBinary::BINARY_NAME, $data[CrackerBinary::BINARY_NAME], '='),
        new QueryFilter(CrackerBinary::DOWNLOAD_URL, $data[CrackerBinary::DOWNLOAD_URL], '='),
        new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $data[CrackerBinary::CRACKER_BINARY_TYPE_ID], '='),

      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(CrackerBinary::CRACKER_BINARY_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      /* No unique properties set on columns, thus multiple entries could exists, pick the latest (DESC ordering used) */
      assert(count($objects) >= 1);
      
      return $objects[0]->getId();
    }

    protected function deleteObject(object $object): void {
      CrackerUtils::deleteBinary($object->getId());
    }
}
CrackerBinaryAPI::register($app);