<?php

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

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
   * @throws NotFoundExceptionInterface
   * @throws HttpForbidden
   * @throws ContainerExceptionInterface
   * @throws HTException
   * @throws JsonException
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
   * @return object|array|null
   */
  public function actionPost($data): object|array|null {
    throw new HttpError("GetAccessGroups has no POST");
  }
  
  static public function register($app): void {
    $baseUri = GetAccessGroupsHelperAPI::getBaseUri();
    
    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, "GetAccessGroupsHelperAPI:handleGet");
  }
  
  /**
   * getAccessGroups is different because it returns via another function
   */
  public static function getResponse(): array|string|null {
    return null;
  }
}

use Slim\App;
/** @var App $app */
GetAccessGroupsHelperAPI::register($app);
