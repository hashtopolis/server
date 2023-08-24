<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Preprocessor;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

require_once(dirname(__FILE__) . "/shared.inc.php");


class PreprocessorAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/preprocessors";
    }

    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public static function getDBAclass(): string {
      return Preprocessor::class;
    }

    protected function getFactory(): object {
      return Factory::getPreprocessorFactory();
    }

    public function getExpandables(): array {
      return [];
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }

    protected function checkPermission(object $object): bool
    {
      return true;
    }
    

    protected function createObject($QUERY): int {
      PreprocessorUtils::addPreprocessor(
        $QUERY[Preprocessor::NAME],
        $QUERY[Preprocessor::BINARY_NAME],
        $QUERY[Preprocessor::URL],
        $QUERY[Preprocessor::KEYSPACE_COMMAND],
        $QUERY[Preprocessor::SKIP_COMMAND],
        $QUERY[Preprocessor::LIMIT_COMMAND]
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(Preprocessor::NAME, $QUERY[Preprocessor::NAME], '='),
        new QueryFilter(Preprocessor::BINARY_NAME, $QUERY[Preprocessor::BINARY_NAME], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(Preprocessor::PREPROCESSOR_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) == 1);

      return $objects[0]->getId();      
    }

    protected function deleteObject(object $object): void {
      PreprocessorUtils::delete($object->getId());
    }
}

PreprocessorAPI::register($app);