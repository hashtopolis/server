<?php

namespace Hashtopolis\inc\apiv2\openapi;

/**
 * A single Slim route resolved to the API class and method that handles it.
 */
final readonly class RouteTarget {
  public function __construct(
    public string $pattern,
    public string $httpMethod,
    public string $className,
    public string $methodName
  ) {
  }
}
