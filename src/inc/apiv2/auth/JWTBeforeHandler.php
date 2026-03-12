<?php

namespace Hashtopolis\inc\apiv2\auth;

use Hashtopolis\dba\Factory;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\apiv2\model\ApiTokenAPI;
use JimTools\JwtAuth\Handlers\BeforeHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class JWTBeforeHandler implements BeforeHandlerInterface {
  /**
   * @param array{decoded: array<string, mixed>, token: string} $arguments
   */
  public function __invoke(ServerRequestInterface $request, array $arguments): ServerRequestInterface {
    if (isset ($arguments["decoded"]["aud"]) && $arguments["decoded"]["aud"] == ApiTokenAPI::API_AUD) {
      $apiTokenId = $arguments["decoded"]["jti"];
      $token = Factory::getJwtApiKeyFactory()->get($apiTokenId);
      if ($token === null) {
        // Should not happen
        throw new HttpError("Token doesn't exists in the database");
      }
      if ($token->getIsRevoked() === 1) {
        throw new HttpForbidden("Token is revoked");
      }
    }
    // adds the decoded userId and scope to the request attributes
    return $request->withAttribute("userId", $arguments["decoded"]["userId"])->withAttribute("scope", $arguments["decoded"]["scope"]);
  }
}