<?php
require_once(dirname(__FILE__) . "/AbstractBaseAPI.class.php");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DBA\Factory;

use DBA\AbstractModelFactory;

use DBA\Chunk;
use DBA\CrackerBinary;
use DBA\Hashlist;
use DBA\RightGroup;
use DBA\Supertask;
use DBA\Task;
use DBA\User;

abstract class AbstractHelperAPI extends AbstractBaseAPI {
  abstract public function actionPost(array $data): array|null;

  private static function getFactory(string $model): object {
    switch($model) {
      case Chunk::class:
        return Factory::getChunkFactory();
      case CrackerBinary::class;
        return Factory::getCrackerBinaryFactory();
      case Hashlist::class:
        return Factory::getHashlistFactory();
      case RightGroup::class:
        return Factory::getRightGroupFactory();
      case Supertask::class:
        return Factory::getSupertaskFactory();
      case Task::class:
        return Factory::getTaskFactory();
      case User::class:
        return Factory::getUserFactory();
      }
    assert(False, "Model '$model' cannot be mapped to Factory");
  }

  final protected static function fetchOne(string $model, int $pk): object
  {
    $factory = self::getFactory($model);
    $object = $factory->get($pk);
    if ($object === null) {
      throw new HTException("$model '$pk' not found!", 400);
    }
    return $object;
  }

  final protected static function getChunk(int $pk): Chunk
  {
    return self::fetchOne(Chunk::class, $pk);
  }

  final protected static function getCrackerBinary(int $pk): CrackerBinary
  {
    return self::fetchOne(CrackerBinary::class, $pk);
  }

  final protected static function getHashlist(int $pk): Hashlist
  {
    return self::fetchOne(Hashlist::class, $pk);
  }

  final protected static function getRightGroup(int $pk): RightGroup
  {
    return self::fetchOne(RightGroup::class, $pk);
  }

  final protected static function getSupertask(int $pk): Supertask
  {
    return self::fetchOne(Supertask::class, $pk);
  }

  final protected static function getTask(int $pk): Task
  {
    return self::fetchOne(Task::class, $pk);
  }

  final protected static function getUser(int $pk): User
  {
    return self::fetchOne(User::class, $pk);
  }

  /* Chunk API endpoint specific call to abort chunk */
  public function processPost(Request $request, Response $response, array $args): Response 
  {
    /* Required calls for all custom requests */
    $this->preCommon($request);

    $data = $request->getParsedBody();
    $allFeatures = $this->getAliasedFeatures();

    // Validate if correct parameters are sent
    $this->validateParameters($data, $allFeatures);

    /* Validate type of parameters */
    $this->validateData($data, $allFeatures);

    /* All creation of object */
    try {
      // TODO: Validate data is compliant with https://jsonapi.org/format/#document-top-level 'Primary data'
      $returnData = $this->actionPost($data);
      $status = ($returnData) ? 200 : 204;
      $retval['data'] = $returnData;
    } catch (Exception $e) {
      // https://jsonapi.org/format/#error-objects
      $status = $e->getCode();
      $retval['errors'] = [
        'status' => $e->getCode(),
        'source' => $e->getFile() . ':' . $e->getLine(),
        'title' => $e->getMessage(),
      ];
    } finally {
      if ($status == 204) {
        return $response->withStatus($status);        
      } else {
        $response->getBody()->write($this->ret2json($retval));     
        return $response->withStatus($status)
        ->withHeader("Content-Type", "application/json");
      }
    } 
  }  

  /**
   * Override-able registering of options
   */
  static public function register($app): void
  {
    $me = get_called_class();
    $baseUri = $me::getBaseUri();

    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });

    $available_methods = $me::getAvailableMethods();

    if (in_array("GET", $available_methods)) {
      $app->get($baseUri, $me . ':actionGet')->setname($me . ':actionGet');
    }

    if (in_array("POST", $available_methods)) {
      $app->post($baseUri, $me . ':processPost')->setname($me . ':processPost');
    }

    if (in_array("PATCH", $available_methods)) {
      $app->patch($baseUri, $me . ':actionPatch')->setName($me . ':actionPatch');
    }

    if (in_array("DELETE", $available_methods)) {
      $app->delete($baseUri, $me . ':actionDelete')->setName($me . ':actionDelete');
    }
  }
}
