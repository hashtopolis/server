<?php
require_once(dirname(__FILE__) . "/AbstractBaseAPI.class.php");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class AbstractHelperAPI extends AbstractBaseAPI {
  abstract public function actionPost(array $data): object|array|null;
  
  /**
   * Function in order to create swagger documentation. SHould return either a map of strings that
   * describes the output ex: ["assign" => "succes"] or if the endpoint returns an object it should return
   * the string representation of that object ex: File.
   */
  abstract public static function getResponse(): array|string|null;

  public function getParamsSwagger(): array {
    return [];
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
    $newObject = $this->actionPost($data);

    /* Successfully executed action of type update/delete */
    if ($newObject == null) {
      return $response->withStatus(204);
    }


    /* Succesful executed action of create */
    if (is_object($newObject)) {
      $apiClass = new ($this->container->get('classMapper')->get($newObject::class))($this->container);
      return self::getOneResource($apiClass, $newObject, $request, $response);
    /* A meta response of a helper function */
    } elseif (is_array($newObject)) {
      return self::getMetaResponse($newObject, $request, $response);
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
