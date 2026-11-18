<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\HTException;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Hashtopolis\inc\utils\AccountUtils;

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
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws ContainerExceptionInterface
   * @throws HTException
   * @throws HttpError
   * @throws JsonException
   * @throws NotFoundExceptionInterface
   * @throws HttpForbidden
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
   * @return AbstractModel|array|null
   * @throws HttpError
   */
  public function actionPost($data): AbstractModel|array|null {
    throw new HttpError("GetCurrentUser has no actionPOST");
  }
  
  // PATCH endpoint in order to patch attributes of own user, even when user doesnt have permissions to alter users
  
  /**
   * Updates the email and/or session lifetime (in seconds) of the current user. Attributes left out
   * of the request stay unchanged.
   *
   * @throws HTException
   * @throws HttpError
   * @throws HttpForbidden
   */
  public function actionPatch(Request $request, Response $response, array $args): Response {
    $this->preCommon($request);
    $user = $this->getCurrentUser();
    $attributes = $request->getParsedBody()['data']['attributes'] ?? [];

    if (!is_array($attributes)) {
      throw new HttpError("attributes must be an object!");
    }

    $hasLifetime = array_key_exists(User::SESSION_LIFETIME, $attributes);
    $hasEmail = array_key_exists(User::EMAIL, $attributes);

    if ($hasLifetime && !is_int($attributes[User::SESSION_LIFETIME])) {
      throw new HttpError("sessionLifetime must be an integer number of seconds!");
    }

    if ($hasEmail && !is_string($attributes[User::EMAIL])) {
      throw new HttpError("email must be a string!");
    }

    /* setEmail validates before it writes, so checking the lifetime up front leaves the user untouched
       when either attribute is rejected. */
    if ($hasLifetime) {
      AccountUtils::validateSessionLifetime($attributes[User::SESSION_LIFETIME]);
    }

    if ($hasEmail) {
      AccountUtils::setEmail($attributes[User::EMAIL], $user);
    }

    if ($hasLifetime) {
      AccountUtils::updateSessionLifetime($attributes[User::SESSION_LIFETIME], $user);
    }

    return $response->withStatus(204);
  }
  
  static public function register($app): void {
    $baseUri = CurrentUserHelperAPI::getBaseUri();
    
    /* Allow CORS preflight requests */
    $app->options($baseUri, function (Request $request, Response $response): Response {
      return $response;
    });
    $app->get($baseUri, [self::class, 'handleGet']);
    $app->patch($baseUri, [self::class, 'actionPatch']);
  }
  
  public static function getResponse(): array|string|null {
    return "User";
  }

  /* actionPatch returns 204 No Content, so there is no response body to document for PATCH. */
  public static function getNoContentMethods(): array {
    return ["PATCH"];
  }
}
