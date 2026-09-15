<?php

use Firebase\JWT\JWT;

use Hashtopolis\inc\apiv2\auth\RefreshTokenCookie;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpUnauthorized;
use Hashtopolis\inc\StartupConfig;
use Hashtopolis\inc\utils\RefreshTokenUtils;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Random\RandomException;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\Factory;
use Firebase\JWT\JWK;
use Hashtopolis\inc\apiv2\error\HttpForbidden;

require_once(dirname(__FILE__) . "/../../startup/include.php");

const USER_AUD = "user_hashtopolis";

/**
 * Lifetime of an access token in seconds. Access tokens are not revocable, so this is the window in
 * which a leaked one stays usable; clients are expected to keep it short and lean on the refresh
 * token at /api/v2/auth/refresh to stay logged in beyond it.
 */
const ACCESS_TOKEN_LIFETIME = 2 * 3600;

/**
 * Mints an access token for a user which has already been authenticated.
 *
 * @param User $user
 * @param int $expires unix timestamp at which the token stops being accepted
 * @return string the encoded JWT
 * @throws HttpForbidden when the user has been deactivated
 * @throws HttpError when the user has no right group
 * @throws RandomException
 * @throws Exception
 */
function generateAccessToken(User $user, int $expires): string {
  if ($user->getIsValid() !== 1) {
    throw new HttpForbidden("User is set to invalid");
  }
  
  $group = Factory::getRightGroupFactory()->get($user->getRightGroupId());
  if ($group === null) {
    throw new HttpError("No rightgroup found for this user");
  }
  
  $secret = StartupConfig::getInstance()->getPepper(0);
  $payload = [
    "iat" => time(),
    "exp" => $expires,
    "jti" => bin2hex(random_bytes(16)),
    "userId" => $user->getId(),
    "scope" => $group->getPermissions(),
    "iss" => "Hashtopolis",
    "kid" => hash("sha256", $secret),
    "aud" => USER_AUD
  ];
  
  return JWT::encode($payload, $secret, "HS256");
}

/**
 * @param string $userName
 * @return User
 * @throws HttpError when no such user exists
 * @throws Exception
 */
function findUserByName(string $userName): User {
  $filter = new QueryFilter(User::USERNAME, $userName, "=");
  $user = Factory::getUserFactory()->filter([Factory::FILTER => $filter], true);
  if ($user === null) {
    throw new HttpError("No user with this userName in the database");
  }
  
  return $user;
}

/**
 * Mints an access token for a user identified by name.
 *
 * @param string $userName
 * @param int $expires unix timestamp at which the token stops being accepted
 * @return string the encoded JWT
 * @throws HttpError when no such user exists
 * @throws HttpForbidden
 * @throws RandomException
 * @throws Exception
 */
function generateTokenForUser(string $userName, int $expires): string {
  return generateAccessToken(findUserByName($userName), $expires);
}

/**
 * Builds the response body shared by every endpoint handing out an access token. The refresh token
 * itself is deliberately absent: it only ever travels in an HttpOnly cookie.
 *
 * @param Response $response
 * @param string $token the encoded JWT
 * @param int $expires unix timestamp at which the token stops being accepted
 * @return Response
 */
function accessTokenResponse(Response $response, string $token, int $expires): Response {
  $response->getBody()->write(json_encode(["token" => $token, "expires" => $expires], JSON_UNESCAPED_SLASHES));
  
  return $response->withStatus(201)
    ->withHeader("Content-Type", "application/json");
}

function extractBearerToken(Request $request): ?string {
    $header = $request->getHeaderLine('Authorization');

    if (!$header) {
        return null;
    }

    if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
        return null;
    }

    return trim($matches[1]);
}

// Exchanges an oauth token for a application JWT token
/** @var App $app */
$app->group("/api/v2/auth/oauth-token", function (RouteCollectorProxy $group) {

  $group->post('', function (Request $request, Response $response, array $args): Response {
    $jwks_file = file_get_contents("/keys/jwks.json");
    if (!$jwks_file) {
      throw new HttpError("No jwks.json found, upload the jwks public keys to /keys/jwks.json to use OIDC authentication");
    }
    $jwks = json_decode($jwks_file, true);

    if ($jwks === null) {
      throw new HttpError("Incorrect json inside jwks.json, make sure to upload a valid json file");
    }
    $keys = JWK::parseKeySet($jwks);
    $jwt = extractBearerToken($request);
    if ($jwt === null) {
      throw new HttpError("No jwt Token found in the Bearer header");
    }
    $decoded_jwt = JWT::decode($jwt, $keys);

    if(!property_exists($decoded_jwt, "preferred_username")) {
      throw new HttpError("The OAUTH token doesnt have a 'preferred_username' claim, which is needed to validate the user");
    }
    $userName = $decoded_jwt->preferred_username;

    $user = findUserByName($userName);
    $expires = time() + ACCESS_TOKEN_LIFETIME;
    $response = accessTokenResponse($response, generateAccessToken($user, $expires), $expires);
    
    return RefreshTokenCookie::attach($request, $response, RefreshTokenUtils::issue($user->getId()));
  });
});

// This routes needs to be protected by httpbasicauthentication middleware
$app->group("/api/v2/auth/token", function (RouteCollectorProxy $group) {
  /* Allow preflight requests */
  $group->options('', function (Request $request, Response $response, array $args): Response {
    return $response;
  });
  
  $group->post('', function (Request $request, Response $response, array $args): Response {
    $userName = $request->getAttribute('user');
    
    $user = findUserByName($userName);
    $expires = time() + ACCESS_TOKEN_LIFETIME;
    $response = accessTokenResponse($response, generateAccessToken($user, $expires), $expires);
    
    /* Starts a new refresh token family, so logging in again leaves sessions on other devices alone. */
    return RefreshTokenCookie::attach($request, $response, RefreshTokenUtils::issue($user->getId()));
  });
});

/*
 * Exchanges the refresh token cookie for a fresh access token. This endpoint is exempt from the JWT
 * middleware on purpose: its whole reason to exist is to work once the access token has expired, so
 * the cookie is the only credential it looks at.
 */
$app->group("/api/v2/auth/refresh", function (RouteCollectorProxy $group) {
  /* Allow preflight requests */
  $group->options('', function (Request $request, Response $response, array $args): Response {
    return $response;
  });
  
  $group->post('', function (Request $request, Response $response, array $args): Response {
    $presented = RefreshTokenCookie::read($request);
    if ($presented === null) {
      throw new HttpUnauthorized("No refresh token supplied");
    }
    
    $rotated = RefreshTokenUtils::rotate($presented);
    
    $expires = time() + ACCESS_TOKEN_LIFETIME;
    $response = accessTokenResponse($response, generateAccessToken($rotated["user"], $expires), $expires);
    
    return RefreshTokenCookie::attach($request, $response, $rotated["token"]);
  });
  
  /* Logout: ends the session the cookie belongs to and drops the cookie. */
  $group->delete('', function (Request $request, Response $response, array $args): Response {
    $presented = RefreshTokenCookie::read($request);
    if ($presented !== null) {
      RefreshTokenUtils::revoke($presented);
    }
    
    return RefreshTokenCookie::clear($request, $response)->withStatus(204);
  });
});
