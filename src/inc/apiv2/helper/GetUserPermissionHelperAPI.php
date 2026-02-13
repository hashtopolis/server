<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\dba\Factory;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\common\error\HttpError;
use Hashtopolis\inc\HTException;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetUserPermissionHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getUserPermission";
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
   * @throws NotFoundExceptionInterface
   * @throws ContainerExceptionInterface
   * @throws HTException
   * @throws JsonException
   */
  public function handleGet(Request $request, Response $response): Response {
    $this->preCommon($request);
    $user = $this->getCurrentUser();
    
    $rightGroup = Factory::getRightGroupFactory()->get($user->getRightGroupId());
    
    $ret = self::createJsonResponse(data: self::obj2Resource($rightGroup));
    
    $body = $response->getBody();
    $body->write($this->ret2json($ret));
    
    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json;');
  }
  
  /**
   * @throws HttpError
   */
  public function actionPost($data): object|array|null {
    throw new HttpError("GetAccessGroups has no POST");
  }
  
  static public function register($app): void {
    $baseUri = GetUserPermissionHelperAPI::getBaseUri();
    
    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, "GetUserPermissionHelperAPI:handleGet");
  }
  
  /**
   * getAccessGroups is different because it returns via another function
   */
  public static function getResponse(): array|string|null {
    return null;
  }
}

