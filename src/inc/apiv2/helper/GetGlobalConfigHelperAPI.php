<?php

namespace Hashtopolis\inc\apiv2\helper;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\HTException;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * This helper is only needed, until we can granularly work with the CRUD permissions on Config.
 *
 */
class GetGlobalConfigHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getGlobalConfig";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [];
  }
  
  public function getFormFields(): array {
    return [];
  }
  
  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws ContainerExceptionInterface
   * @throws HTException
   * @throws HttpError
   * @throws JsonException
   * @throws NotFoundExceptionInterface
   * @throws HttpForbidden
   * @throws Exception
   */
  public function handleGet(Request $request, Response $response): Response {
    $this->preCommon($request);
    
    $configValues = Factory::getConfigFactory()->filter([]);
    $values = [];
    foreach($configValues as $configValue) {
      $values[] = self::obj2Resource($configValue);
    }
    
    $ret = self::createJsonResponse(data: $values);
    
    $body = $response->getBody();
    $body->write($this->ret2json($ret));
    
    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json;');
  }
  
  /**
   * @param array $data
   * @return AbstractModel|array|null
   * @throws HttpError
   */
  public function actionPost(array $data): AbstractModel|array|null {
    throw new HttpError("getGlobalConfig has no POST");
  }
  
  static public function register($app): void {
    $baseUri = GetGlobalConfigHelperAPI::getBaseUri();
    
    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, [self::class, 'handleGet']);
  }
  
  public static function getResponse(): string {
    return "Config";
  }
}

