<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Chunk;
use DBA\Factory;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotImplementedException;

require_once(dirname(__FILE__) . "/shared.inc.php");


class ChunkAPI extends AbstractBaseAPI {
    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public function getFeatures(): array {
      return Chunk::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getChunkFactory();
    }

    public function getExpandables(): array {
      return ["task"];
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
      /* Dummy code to implement abstract functions */
      assert(False, "Chunks cannot be created via API");
      return -1;
    }


    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "Chunks cannot be deleted via API");
    }


    /* Chunk API endpoint specific call to abort chunk */
    public function abortOne(Request $request, Response $response, array $args): Response {
      /* Required calls for all custom requests */
      $this->preCommon($request);
      $object = $this->doFetch($request, $args['id']);

      TaskUtils::abortChunk($object->getId(), $this->getUser());

      /* Mandatory return properties */
      return $response->withStatus(204)
      ->withHeader("Content-Type", "application/json");
    }


    /* Chunk API endpoint specific call to reset chunk */
    public function resetOne(Request $request, Response $response, array $args): Response {
      /* Required calls for all custom requests */
      $this->preCommon($request);
      $object = $this->doFetch($request, $args['id']);

      TaskUtils::resetChunk($object->getId(), $this->getUser());

      /* Mandatory return properties */
      return $response->withStatus(204)
      ->withHeader("Content-Type", "application/json");
    }
}


$app->group("/api/v2/ui/chunks", function (RouteCollectorProxy $group) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', \ChunkAPI::class . ':get');
});


$app->group("/api/v2/ui/chunks/{id}", function (RouteCollectorProxy $group) {
    /* Allow preflight requests */
    $group->options('', function (Request $request, Response $response, array $args): Response {
        return $response;
    });

    $group->get('', \ChunkAPI::class . ':getOne');
});


$app->group("/api/v2/ui/chunks/{id}/abort", function (RouteCollectorProxy $group) {
  /* Allow preflight requests */
  $group->options('', function (Request $request, Response $response, array $args): Response {
      return $response;
  });

  $group->post('', \ChunkAPI::class . ':abortOne');
});


$app->group("/api/v2/ui/chunks/{id}/reset", function (RouteCollectorProxy $group) {
  /* Allow preflight requests */
  $group->options('', function (Request $request, Response $response, array $args): Response {
      return $response;
  });

  $group->post('', \ChunkAPI::class . ':resetOne');
});