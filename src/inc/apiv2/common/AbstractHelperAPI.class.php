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
use DBA\TaskWrapper;
use DBA\User;

abstract class AbstractHelperAPI extends AbstractBaseAPI {
  abstract public function actionPost(array $data): object|null;
  

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
    $newObject = $this->actionPost($data);

    /* Successfully executed action of type update/delete */
    if ($newObject == null) {
      return $response->withStatus(204);
    }

    /* Succesful executed action of create */
    $apiClass = new ($this->container->get('classMapper')->get($newObject::class))($this->container);
    return self::getOneResource($apiClass, $newObject, $request, $response);
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
