<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\dba\models\Preprocessor;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\common\error\HttpConflict;
use Hashtopolis\inc\apiv2\common\error\HttpError;
use Hashtopolis\inc\utils\PreprocessorUtils;


class PreprocessorAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/preprocessors";
  }
  
  public static function getDBAclass(): string {
    return Preprocessor::class;
  }
  
  /**
   * @throws HttpError
   * @throws HttpConflict
   */
  protected function createObject(array $data): int {
    $preprocessor = PreprocessorUtils::addPreprocessor(
      $data[Preprocessor::NAME],
      $data[Preprocessor::BINARY_NAME],
      $data[Preprocessor::URL],
      $data[Preprocessor::KEYSPACE_COMMAND],
      $data[Preprocessor::SKIP_COMMAND],
      $data[Preprocessor::LIMIT_COMMAND]
    );
    return $preprocessor->getId();
  }
  
  protected function getUpdateHandlers($id, $current_user): array {
    return [
      Preprocessor::NAME => fn($value) => PreprocessorUtils::editName($id, $value),
      Preprocessor::BINARY_NAME => fn($value) => PreprocessorUtils::editBinaryName($id, $value),
      Preprocessor::KEYSPACE_COMMAND => fn($value) => PreprocessorUtils::editKeyspaceCommand($id, $value),
      Preprocessor::LIMIT_COMMAND => fn($value) => PreprocessorUtils::editLimitCommand($id, $value),
      Preprocessor::SKIP_COMMAND => fn($value) => PreprocessorUtils::editSkipCommand($id, $value),
    ];
  }
  
  /**
   * @throws HttpError
   */
  protected function deleteObject(object $object): void {
    PreprocessorUtils::delete($object->getId());
  }
}
