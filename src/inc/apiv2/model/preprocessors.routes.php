<?php
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

use DBA\Preprocessor;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class PreprocessorAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/preprocessors";
    }

    public static function getDBAclass(): string {
      return Preprocessor::class;
    }

    protected function createObject(array $data): int {
      PreprocessorUtils::addPreprocessor(
        $data[Preprocessor::NAME],
        $data[Preprocessor::BINARY_NAME],
        $data[Preprocessor::URL],
        $data[Preprocessor::KEYSPACE_COMMAND],
        $data[Preprocessor::SKIP_COMMAND],
        $data[Preprocessor::LIMIT_COMMAND]
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(Preprocessor::NAME, $data[Preprocessor::NAME], '='),
        new QueryFilter(Preprocessor::BINARY_NAME, $data[Preprocessor::BINARY_NAME], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(Preprocessor::PREPROCESSOR_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) == 1);

      return $objects[0]->getId();      
    }

    protected function getUpdateHandlers($id, $current_user): array {
      return [
        Preprocessor::NAME => fn ($value) => PreprocessorUtils::editName($id, $value),
        Preprocessor::BINARY_NAME => fn ($value) => PreprocessorUtils::editBinaryName($id, $value),
        Preprocessor::KEYSPACE_COMMAND => fn ($value) => PreprocessorUtils::editKeyspaceCommand($id, $value),
        Preprocessor::LIMIT_COMMAND => fn ($value) => PreprocessorUtils::editLimitCommand($id, $value),
        Preprocessor::SKIP_COMMAND => fn ($value) => PreprocessorUtils::editSkipCommand($id, $value),
      ];
    }

    protected function deleteObject(object $object): void {
      PreprocessorUtils::delete($object->getId());
    }
}

PreprocessorAPI::register($app);