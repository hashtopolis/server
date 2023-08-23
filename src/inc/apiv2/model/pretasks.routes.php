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

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
      return [
        "files" => ['type' => 'array', 'subtype' => 'int']
      ];
    }

    protected function createObject(array $data): int {
      /* Use quirk on 'files' since this is casted to DB representation  */
      PretaskUtils::createPretask(
        $data[PreTask::TASK_NAME],
        $data[PreTask::ATTACK_CMD],
        $data[PreTask::CHUNK_TIME],
        $data[PreTask::STATUS_TIMER],
        $data[PreTask::COLOR],
        $data[PreTask::IS_CPU_TASK],
        $data[PreTask::IS_SMALL],
        $data[PreTask::USE_NEW_BENCH],
        $this->db2json($this->getFeatures()['files'], $data["files"]),
        $data[PreTask::CRACKER_BINARY_TYPE_ID],
        $data[PreTask::MAX_AGENTS],
        $data[PreTask::PRIORITY]
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(PreTask::TASK_NAME, $data[PreTask::TASK_NAME], '='),
        new QueryFilter(PreTask::ATTACK_CMD, $data[PreTask::ATTACK_CMD], '=')
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