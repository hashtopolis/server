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
    public static function getBaseUri(): string {
      return "/api/v2/ui/chunks";
    }

    public static function getAvailableMethods(): array {
      return ['GET'];
    }

    public static function getDBAclass(): string {
      return Chunk::class;
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

    /* Override since we have custom functions to add */
    static public function register($app): void {
      parent::register($app);

      $me = get_called_class();
      $baseUri = $me::getBaseUri();
      $baseUriOne = $baseUri . '/{id}';

      $app->group($baseUriOne . "/abort", function (RouteCollectorProxy $group) {
        /* Allow preflight requests */
        $group->options('', function (Request $request, Response $response, array $args): Response {
            return $response;
        });
      
        $group->post('', self::class . ':abortOne');
      });
      
      
      $app->group($baseUriOne . "/reset", function (RouteCollectorProxy $group) {
        /* Allow preflight requests */
        $group->options('', function (Request $request, Response $response, array $args): Response {
            return $response;
        });
      
        $group->post('', self::class . ':resetOne');
      });
    }
}

ChunkAPI::register($app);