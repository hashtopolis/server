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
    protected function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    protected function getFeatures(): array {
      return Preprocessor::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getPreprocessorFactory();
    }

    protected function getExpandables(): array {
      return [];
    }

    protected function getFilterACL(): array {
      return [];
    }

    protected function getFormFields(): array {
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
      $this->getFactory()->delete($object);
    }
}


$app->group("/api/v2/ui/preprocessors", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \PreprocessorAPI::class . ':get');
    $group->post('', \PreprocessorAPI::class . ':post');
});


$app->group("/api/v2/ui/preprocessors/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \PreprocessorAPI::class . ':getOne');
    $group->patch('', \PreprocessorAPI::class . ':patchOne');
    $group->delete('', \PreprocessorAPI::class . ':deleteOne');
});