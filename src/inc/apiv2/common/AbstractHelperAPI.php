<?php

namespace Hashtopolis\inc\apiv2\common;

use Hashtopolis\dba\AbstractModel;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\apiv2\error\InternalError;
use Hashtopolis\inc\HTException;
use InvalidArgumentException;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Exception\HttpForbiddenException;
use Hashtopolis\inc\utils\DownloadUtils;

abstract class AbstractHelperAPI extends AbstractBaseAPI {
  abstract public function actionPost(array $data): AbstractModel|array|null;
  
  /**
   * Function in order to create swagger documentation. Should return either a map of strings that
   * describes the output ex: ["assign" => "success"] or if the endpoint returns an object it should return
   * the string representation of that object ex: File.
   */
  abstract public static function getResponse(): array|string|null;
  
  public function getParamsSwagger(): array {
    return [];
  }
  
  /**
   * Chunk API endpoint specific call to abort chunk
   * @param Request $request
   * @param Response $response
   * @param array $args
   * @return Response
   * @throws HTException
   * @throws HttpError
   * @throws HttpForbidden
   * @throws JsonException
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   * @throws InternalError
   */
  public function processPost(Request $request, Response $response, array $args): Response {
    /* Required calls for all custom requests */
    $this->preCommon($request);
    
    $data = $request->getParsedBody();
    $allFeatures = $this->getAliasedFeatures();
    
    if ($data !== null) {
      // Validate if correct parameters are sent
      $this->validateParameters($data, $allFeatures);
      
      /* Validate type of parameters */
      $this->validateData($data, $allFeatures);
    }
    else {
      $data = [];
    }
    
    /* All creation of object */
    $newObject = $this->actionPost($data);
    
    /* Successfully executed action of type update/delete */
    if ($newObject == null) {
      return $response->withStatus(204);
    }
    
    
    /* Successful executed action of create */
    if (is_object($newObject)) {
      $apiClass = new ($this->container->get('classMapper')->get($newObject::class))($this->container);
      return self::getOneResource($apiClass, $newObject, $request, $response);
      /* A meta response of a helper function */
    }
    elseif (is_array($newObject)) {
      return self::getMetaResponse($newObject, $request, $response);
    }
    else {
      throw new HttpError("Unable to process request!");
    }
  }
  
  /**
   * Override-able registering of options
   */
  static public function register(App $app): void {
    $me = static::class;
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
  
  /**
   * Streams the given file as a download response, handling ETag based caching
   * and partial content (range) requests.
   *
   * @param Request $request
   * @param Response $response
   * @param string $filename Absolute path of the file to stream
   * @return Response
   * @throws HttpForbiddenException
   */
  protected function startDownload(Request $request, Response $response, string $filename): Response {
    return DownloadUtils::startDownload($request, $response, $filename);
  }
}
