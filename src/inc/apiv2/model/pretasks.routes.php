<?php
use DBA\Factory;
use DBA\JoinFilter;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\File;
use DBA\FilePretask;
use DBA\Pretask;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class PreTaskAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/pretasks";
    }

    public static function getDBAclass(): string {
      return Pretask::class;
    }

    protected function getFactory(): object {
      return Factory::getPretaskFactory();
    }

    public function getExpandables(): array {
      return ["pretaskFiles"];
    }

    protected function doExpand(object $object, string $expand): mixed {
      assert($object instanceof PreTask);
      switch($expand) {
        case 'pretaskFiles':
          $qF = new QueryFilter(FilePretask::PRETASK_ID, $object->getId(), "=", Factory::getFilePretaskFactory());
          $jF = new JoinFilter(Factory::getFilePretaskFactory(), File::FILE_ID, FilePretask::FILE_ID);
          return $this->joinQuery(Factory::getFileFactory(), $qF, $jF);
      }
    }  

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
      return [
        "files" => ['type' => 'array', 'subtype' => 'int']
      ];
    }

    protected function createObject($mappedQuery, $QUERY): int {
      PretaskUtils::createPretask(
        $mappedQuery[PreTask::TASK_NAME],
        $mappedQuery[PreTask::ATTACK_CMD],
        $mappedQuery[PreTask::CHUNK_TIME],
        $mappedQuery[PreTask::STATUS_TIMER],
        $mappedQuery[PreTask::COLOR],
        $mappedQuery[PreTask::IS_CPU_TASK],
        $mappedQuery[PreTask::IS_SMALL],
        $mappedQuery[PreTask::USE_NEW_BENCH],
        $QUERY["files"],
        $mappedQuery[PreTask::CRACKER_BINARY_TYPE_ID],
        $mappedQuery[PreTask::MAX_AGENTS],
        $mappedQuery[PreTask::PRIORITY]
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(PreTask::TASK_NAME, $mappedQuery[PreTask::TASK_NAME], '='),
        new QueryFilter(PreTask::ATTACK_CMD, $mappedQuery[PreTask::ATTACK_CMD], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(PreTask::PRETASK_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) >= 1);

      return $objects[0]->getId();      
    }

    protected function deleteObject(object $object): void {
      PretaskUtils::deletePretask($object->getId());
    }
}

PreTaskAPI::register($app);