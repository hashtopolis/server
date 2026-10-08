<?php

namespace Hashtopolis\inc\apiv2\auth;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\apiv2\model\ApiTokenAPI;
use Hashtopolis\inc\defines\DTokenType;
use JimTools\JwtAuth\Handlers\BeforeHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class JWTBeforeHandler implements BeforeHandlerInterface {
  /**
   * @param array{decoded: array<string, mixed>, token: string} $arguments
   * @throws HttpError
   * @throws HttpForbidden
   * @throws Exception
   */
  public function __invoke(ServerRequestInterface $request, array $arguments): ServerRequestInterface {
    /* Every token this deployment issues is signed with the same key, so a valid signature says only
       that we minted it, not what we minted it for. Refuse anything whose declared purpose is not
       authorising a resource request, so a credential issued for some other endpoint cannot be spent
       here. Tokens issued before the claim existed carry no type and are still accepted; they age
       out on their own. */
    $type = $arguments["decoded"]["type"] ?? DTokenType::ACCESS;
    if ($type !== DTokenType::ACCESS) {
      throw new HttpForbidden("This endpoint needs an access token, but a token of type '$type' was supplied.");
    }

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
    // adds the decoded userId, scope and aud to the request attributes
    $aud = $arguments["decoded"]["aud"] ?? "user_hashtopolis";
    return $request->withAttribute("userId", $arguments["decoded"]["userId"])->withAttribute("scope", $arguments["decoded"]["scope"])
                    ->withAttribute("aud", $aud);
  }
}