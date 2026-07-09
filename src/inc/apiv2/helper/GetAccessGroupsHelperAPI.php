<?php

namespace Hashtopolis\inc\apiv2\helper;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\HTException;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetAccessGroupsHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getAccessGroups";
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
    $user = $this->getCurrentUser();
    
    $accessGroups = AccessUtils::getAccessGroupsOfUser($user);
    $converted = [];
    
    foreach ($accessGroups as $accessGroup) {
      $converted[] = self::obj2Resource($accessGroup);
    }
    $ret = self::createJsonResponse(data: $converted);
    
    $body = $response->getBody();
    $body->write($this->ret2json($ret));
    
    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json;');
  }
  
  /**
   * @param $data
   * @return AbstractModel|array|null
   * @throws HttpError
   */
  public function actionPost($data): AbstractModel|array|null {
    throw new HttpError("GetAccessGroups has no POST");
  }
  
  static public function register($app): void {
    $baseUri = GetAccessGroupsHelperAPI::getBaseUri();
    
    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, [self::class, 'handleGet']);
  }
  
  /**
   * getAccessGroups is different because it returns via another function
   */
  public static function getResponse(): string {
    return "AccessGroup";
  }
}
