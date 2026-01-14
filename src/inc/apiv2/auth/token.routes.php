<?php

use Firebase\JWT\JWT;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\QueryFilter;
use DBA\User;
use DBA\Factory;

require_once(dirname(__FILE__) . "/../../startup/include.php");

$app->group("/api/v2/auth/token", function (RouteCollectorProxy $group) {
  /* Allow preflight requests */
  $group->options('', function (Request $request, Response $response, array $args): Response {
    return $response;
  });
  
  $group->post('', function (Request $request, Response $response, array $args): Response {
    include(dirname(__FILE__) . '/../../confv2.php');
    
    $requested_scopes = $request->getParsedBody() ?: ["todo.all"];
    
    $valid_scopes = [
      "todo.create",
      "todo.read",
      "todo.update",
      "todo.delete",
      "todo.list",
      "todo.all"
    ];
    
    $scopes = array_filter($requested_scopes, function ($needle) use ($valid_scopes) {
      return in_array($needle, $valid_scopes);
    });
    
    $now = new DateTime();
    $future = new DateTime("now +2 hours");
    $server = $request->getServerParams();
    
    $jti = bin2hex(random_bytes(16));
    
    // FIXME: This is duplicated and should be passed by HttpBasicMiddleware
    $filter = new QueryFilter(User::USERNAME, $request->getAttribute('user'), "=");
    $check = Factory::getUserFactory()->filter([Factory::FILTER => $filter]);
    $user = $check[0];
    
    $payload = [
      "iat" => $now->getTimeStamp(),
      "exp" => $future->getTimeStamp(),
      "jti" => $jti,
      "userId" => $user->getId(),
      "scope" => $scopes
    ];
    
    $secret = $PEPPER[0];
    $token = JWT::encode($payload, $secret, "HS256");
    
    $data["token"] = $token;
    $data["expires"] = $future->getTimeStamp();
    
    $body = $response->getBody();
    $body->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    
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
    
    $payload = [
      "iat" => $now->getTimeStamp(),
      "exp" => $future->getTimeStamp(),
      "jti" => $jti,
      "userId" => $request->getAttribute(('userId')),
      "scope" => $request->getAttribute("scope")
    ];
    
    $secret = $PEPPER[0];
    $token = JWT::encode($payload, $secret, "HS256");
    
    $data["token"] = $token;
    $data["expires"] = $future->getTimeStamp();
    
    $body = $response->getBody();
    $body->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    
    return $response->withStatus(201)
      ->withHeader("Content-Type", "application/json");
  });
});
