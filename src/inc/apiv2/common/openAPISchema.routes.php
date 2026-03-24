<?php

namespace Hashtopolis\inc\apiv2\common;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use Slim\App;
/** @var App $app */
$app->group("/api/v2/openapi.json", function (RouteCollectorProxy $group) use ($app) {
  /* Allow CORS preflight requests */
  $group->options('', function (Request $request, Response $response): Response {
    return $response;
  });

  $group->get('', function (Request $request, Response $response) use ($app): Response {
    $result = OpenAPISchemaUtils::buildSpec($app);

    $body = $response->getBody();
    $body->write(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

    return $response->withStatus(200)
      ->withHeader("Content-Type", "application/json");
  });
});

$app->group("/api/v2/openapi-compliant.json", function (RouteCollectorProxy $group) use ($app) {
  /* Allow CORS preflight requests */
  $group->options('', function (Request $request, Response $response): Response {
    return $response;
  });

  $group->get('', function (Request $request, Response $response) use ($app): Response {
    $result = OpenAPISchemaUtils::buildSpec($app);
    $result = OpenAPISchemaUtils::sanitizeSpec($result);

    $body = $response->getBody();
    $body->write(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

    return $response->withStatus(200)
      ->withHeader("Content-Type", "application/json");
  });
});
