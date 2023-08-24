<?php
use DBA\Factory;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\Pretask;
use DBA\Supertask;
use DBA\SupertaskPretask;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class SupertaskAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/supertasks";
    }

    public static function getDBAclass(): string {
      return Supertask::class;
    }

    public function getExpandables(): array {
      return [ "pretasks" ];
    }

    protected function doExpand(object $object, string $expand): mixed {
      assert($object instanceof Supertask);
      switch($expand) {
        case 'pretasks':
          $qF = new QueryFilter(SupertaskPretask::SUPERTASK_ID, $object->getId(), "=", Factory::getSupertaskPretaskFactory());
          $jF = new JoinFilter(Factory::getSupertaskPretaskFactory(), Pretask::PRETASK_ID, SupertaskPretask::PRETASK_ID);
          return $this->joinQuery(Factory::getPretaskFactory(), $qF, $jF);
      }
    }  

    public function getFormFields(): array {
      return  [
        "pretasks" => ['type' => 'array', 'subtype' => 'int']
      ];
    }

    protected function createObject(array $data): int {
      /* Use quirk on 'pretasks' since this is casted to DB representation  */
      SupertaskUtils::createSupertask(
        $data[Supertask::SUPERTASK_NAME],
        $this->db2json($this->getFeatures()['pretasks'], $data["pretasks"])
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(Supertask::SUPERTASK_NAME, $data[Supertask::SUPERTASK_NAME], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(Supertask::SUPERTASK_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      /* No unique properties set on columns, thus multiple entries could exists, pick the latest (DESC ordering used) */
      assert(count($objects) >= 1);

      return $objects[0]->getId();      
    }

    protected function deleteObject(object $object): void {
      SupertaskUtils::deleteSupertask($object->getId());
    }
}

SupertaskAPI::register($app);