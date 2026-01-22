<?php

use DBA\Factory;
use DBA\QueryFilter;

use DBA\File;
use DBA\FilePretask;
use DBA\JoinFilter;
use DBA\Pretask;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class PreTaskAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/pretasks";
  }
  
  public static function getDBAclass(): string {
    return Pretask::class;
  }
  
  public static function getToManyRelationships(): array {
    return [
      'pretaskFiles' => [
        'key' => Pretask::PRETASK_ID,
        
        'junctionTableType' => FilePretask::class,
        'junctionTableFilterField' => FilePretask::PRETASK_ID,
        'junctionTableJoinField' => FilePretask::FILE_ID,
        
        'relationType' => File::class,
        'relationKey' => File::FILE_ID,
      ],
    ];
  }
  
  public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return [
      "files" => ['type' => 'array', 'subtype' => 'int']
    ];
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    /* Use quirk on 'files' since this is casted to DB representation  */
    $pretask = PretaskUtils::createPretask(
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
    return $pretask->getId();
  }

  //TODO make aggregate data queryable and not included by default
  static function aggregateData(object $object, array &$included_data = [], ?array $aggregateFieldsets = null): array {
    $aggregatedData = [];
    if (is_null($aggregateFieldsets) || (is_array($aggregateFieldsets) && array_key_exists('pretask', $aggregateFieldsets))) {

      $qF1 = new QueryFilter(FilePretask::PRETASK_ID, $object->getId(), "=", Factory::getFilePretaskFactory());
      $jF1 = new JoinFilter(Factory::getFilePretaskFactory(), File::FILE_ID, FilePretask::FILE_ID);
      $files = Factory::getFileFactory()->filter([Factory::FILTER => $qF1, Factory::JOIN => $jF1]);
      $files = $files[Factory::getFileFactory()->getModelName()];
      
      $lineCountProduct = 1;
      foreach ($files as $file) {
        $lineCount = $file->getLineCount();
        if ($lineCount !== null) {
          $lineCountProduct = $lineCountProduct * $lineCount;
        }
      }
      $aggregatedData["auxiliaryKeyspace"] = $lineCountProduct;
    }

    return $aggregatedData;
  }

  protected function getUpdateHandlers($id, $current_user): array {
    return [
      Pretask::ATTACK_CMD => fn($value) => PretaskUtils::changeAttack($id, $value),
      Pretask::COLOR => fn($value) => PretaskUtils::setColor($id, $value),
      Pretask::TASK_NAME => fn($value) => PretaskUtils::renamePretask($id, $value),
      Pretask::MAX_AGENTS => fn($value) => PretaskUtils::setMaxAgents($id, $value),
    ];
  }
  
  /**
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
    PretaskUtils::deletePretask($object->getId());
  }
}

use Slim\App;
/** @var App $app */
PreTaskAPI::register($app);
