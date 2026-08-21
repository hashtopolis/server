<?php

namespace Hashtopolis\inc\apiv2\openapi;

/**
 * A single Slim route resolved to the API class and method that handles it.
 *
 * The route is carried in two forms. $pathTemplate is the OpenAPI path
 * template and is what the spec is keyed by. $pattern is the raw Slim
 * pattern, kept because the placeholder constraints encode more than a
 * validation rule: a relationship route names its relation in the constraint
 * of its "relation" placeholder (see AbstractModelAPI::register).
 */
final readonly class RouteTarget {
  public function __construct(
    public string $pattern,
    public string $pathTemplate,
    public string $httpMethod,
    public string $className,
    public string $methodName
  ) {
  }
}
