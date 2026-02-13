<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\common\error\HttpError;
use Hashtopolis\inc\HTException;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Hashtopolis\inc\apiv2\model\UserAPI;

class CurrentUserHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/currentUser";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET', 'PATCH'];
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
    $userResource = self::obj2Resource($user);
    
    $ret = self::createJsonResponse(data: $userResource);
    
    $body = $response->getBody();
    $body->write($this->ret2json($ret));
    
    return $response->withStatus(200)
      ->withHeader("Content-Type", 'application/vnd.api+json;');
  }
  
  /**
   * @param $data
   * @return object|array|null
   * @throws HttpError
   */
  public function actionPost($data): object|array|null {
    throw new HttpError("GetCurrentUser has no actionPOST");
  }
  
  // PATCH endpoint in order to patch attributes of own user, even when user doesnt have permissions to alter users
  
  /**
   * @throws HTException
   */
  public function actionPatch(Request $request, Response $response, array $args): Response {
    $data = $request->getParsedBody()['data'];
    $this->preCommon($request);
    $user = $this->getCurrentUser();
    $userRoute = new UserAPI($this->container);
    $userRoute->setCurrentUser($user);
    return $userRoute->patchSingleObject($request, $response, $user, $data);
  }
  
  static public function register($app): void {
    $baseUri = currentUserHelperAPI::getBaseUri();
    
    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, "CurrentUserHelperAPI:handleGet");
    $app->patch($baseUri, "CurrentUserHelperAPI:actionPatch");
  }
  
  /**
   * getCurrentUser is different because it returns via another function
   */
  public static function getResponse(): array|string|null {
    return null;
  }
}
