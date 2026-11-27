<?php

namespace Hashtopolis\inc\apiv2\auth;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\apiv2\model\ApiTokenAPI;
use Hashtopolis\inc\defines\DTokenType;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\UserUtils;
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
    $decoded = $arguments["decoded"];
    $aud = $decoded["aud"] ?? "user_hashtopolis";
    $user = self::resolveUser($decoded, $aud);
    $identity = new RequestIdentity($user, $decoded["scope"], $aud, self::resolveIsAdmin($user, $decoded, $aud));
    
    // adds the decoded userId, scope and aud to the request attributes, plus the identity built from them
    return $request->withAttribute("userId", $decoded["userId"])->withAttribute("scope", $decoded["scope"])
                    ->withAttribute("aud", $aud)->withAttribute("identity", $identity);
  }
  
  /**
   * Whether the token carries the user's fields itself. Only login tokens do; API tokens live for
   * months and are always resolved against the database, as are login tokens minted before the claims
   * existed.
   *
   * @param array<string, mixed> $decoded
   */
  private static function carriesUserClaims(array $decoded, string $aud): bool {
    return $aud !== ApiTokenAPI::API_AUD && isset($decoded["username"], $decoded["rightGroupId"]);
  }
  
  /**
   * @param array<string, mixed> $decoded
   * @throws HttpForbidden when the user is deactivated (only checked when the user is read from the database)
   * @throws HTException when the user no longer exists
   */
  private static function resolveUser(array $decoded, string $aud): User {
    if (self::carriesUserClaims($decoded, $aud)) {
      // A deactivated user cannot refresh, so a token in hand means the user was valid when it was minted
      return new User((int)$decoded["userId"], (string)$decoded["username"], null, null, null, 1, null, null, null, null, (int)$decoded["rightGroupId"]);
    }
    $user = UserUtils::getUser((int)$decoded["userId"]);
    if ($user->getIsValid() != 1) {
      throw new HttpForbidden("User is set to invalid");
    }
    return $user;
  }
  
  /**
   * A login token's scope is the user's right-group permissions, so it answers this directly; an API
   * token's scope is its own, so the right group is looked up.
   *
   * @param array<string, mixed> $decoded
   * @throws Exception
   */
  private static function resolveIsAdmin(User $user, array $decoded, string $aud): bool {
    if ($aud !== ApiTokenAPI::API_AUD) {
      return $decoded["scope"] === "ALL";
    }
    $group = Factory::getRightGroupFactory()->get($user->getRightGroupId());
    return $group !== null && $group->getPermissions() === "ALL";
  }
}