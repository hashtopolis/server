<?php

use DBA\Factory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class GetUserPermissionHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/getUserPermission";
  }

  public static function getAvailableMethods(): array {
    return ['GET'];
  }

  public function getRequiredPermissions(string $method): array
  {
    return [];
  }

  public function getFormFields(): array 
  {
    return [];
  }

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

  public function actionPost($data): object|array|null {
    assert(False, "GetAccessGroups has no POST");
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

GetUserPermissionHelperAPI::register($app);
