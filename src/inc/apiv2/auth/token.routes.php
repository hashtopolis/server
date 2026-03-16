<?php

use Firebase\JWT\JWT;

use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\StartupConfig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\Factory;
use Firebase\JWT\JWK;
use Hashtopolis\dba\JoinFilter;
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\inc\apiv2\error\HttpForbidden;

require_once(dirname(__FILE__) . "/../../startup/include.php");

const USER_AUD = "user_hashtopolis";
function generateTokenForUser(Request $request, string $userName, int $expires) {
  $jti = bin2hex(random_bytes(16));
  
  $filter = new QueryFilter(User::USERNAME, $userName, "=");
  $jF = new JoinFilter(Factory::getRightGroupFactory(), User::RIGHT_GROUP_ID, RightGroup::RIGHT_GROUP_ID);
  $joined = Factory::getUserFactory()->filter([Factory::FILTER => $filter, Factory::JOIN => $jF]);
  /** @var User[] $check  */
  $check = $joined[Factory::getUserFactory()->getModelName()];
  if (count($check) === 0) {
    throw new HttpError("No user with this userName in the database");
  }
  $user = $check[0];
  if ($user->getIsValid() !== 1) {
    throw new HttpForbidden("User is set to invalid");
  }

  /** @var RightGroup[] $groupArray */
  $groupArray = $joined[Factory::getRightGroupFactory()->getModelName()];
  if (count($groupArray) === 0) {
    throw new HttpError("No rightgroup found for this user");
  }
  $group = $groupArray[0];
  $scopes = $group->getPermissions();
  
  // $requested_scopes = $request->getParsedBody() ?: ["todo.all"];
  // $valid_scopes = [
  //   "todo.create",
  //   "todo.read",
  //   "todo.update",
  //   "todo.delete",
  //   "todo.list",
  //   "todo.all"
  // ];
  
  // $scopes = array_filter($requested_scopes, function ($needle) use ($valid_scopes) {
  //   return in_array($needle, $valid_scopes);
  // });

  $secret = StartupConfig::getInstance()->getPepper(0);
  $now = new DateTime();

  $payload = [
    "iat" => $now->getTimeStamp(),
    "exp" => $expires,
    "jti" => $jti,
    "userId" => $user->getId(),
    "scope" => $scopes,
    "iss" => "Hashtopolis",
    "kid" =>  hash("sha256", $secret),
    "aud" => USER_AUD
  ];

  $token = JWT::encode($payload, $secret, "HS256");

  return $token;
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
/** @var \Slim\App $app */
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

    $future = new DateTime("now +2 hours");
    $token = generateTokenForUser($request, $userName, $future->getTimestamp());
    $data["token"] = $token;
    $data["expires"] = $future->getTimestamp();
    
    $body = $response->getBody();
    $body->write(json_encode($data, JSON_UNESCAPED_SLASHES));
    
    return $response->withStatus(201)
      ->withHeader("Content-Type", "application/json");
  });
});

// This routes needs to be protected by httpbasicauthentication middleware
$app->group("/api/v2/auth/token", function (RouteCollectorProxy $group) {
  /* Allow preflight requests */
  $group->options('', function (Request $request, Response $response, array $args): Response {
    return $response;
  });
  
  $group->post('', function (Request $request, Response $response, array $args): Response {
    
    $future = new DateTime("now +2 hours");
    $token = generateTokenForUser($request, $request->getAttribute('user'), $future->getTimestamp());
    
    $data["token"] = $token;
    $data["expires"] = $future->getTimestamp();
    
    $body = $response->getBody();
    $body->write(json_encode($data, JSON_UNESCAPED_SLASHES));
    
    return $response->withStatus(201)
      ->withHeader("Content-Type", "application/json");
  });
});

$app->group("/api/v2/auth/refresh", function (RouteCollectorProxy $group) {
  /* Allow preflight requests */
  $group->options('', function (Request $request, Response $response, array $args): Response {
    return $response;
  });
  
  $group->post('', function (Request $request, Response $response, array $args): Response {
    include(dirname(__FILE__) . '/../conf.php');
    
    $now = new DateTime();
    $future = new DateTime("now +2 hours");
    
    $jti = bin2hex(random_bytes(16));
    
    $secret = StartupConfig::getInstance()->getPepper(0);
    $payload = [
      "iat" => $now->getTimeStamp(),
      "exp" => $future->getTimeStamp(),
      "jti" => $jti,
      "userId" => $request->getAttribute(('userId')),
      "scope" => $request->getAttribute("scope"),
      "iss" => "Hashtopolis",
      "kid" =>  hash("sha256", $secret),
      "aud" => USER_AUD
    ];
    
    $token = JWT::encode($payload, $secret, "HS256");
    
    $data["token"] = $token;
    $data["expires"] = $future->getTimeStamp();
    
    $body = $response->getBody();
    $body->write(json_encode($data, JSON_UNESCAPED_SLASHES));
    
    return $response->withStatus(201)
      ->withHeader("Content-Type", "application/json");
  });
});
