<?php

namespace Hashtopolis\inc\apiv2\openapi;

use ReflectionException;
use ReflectionObject;
use Slim\App;

/**
 * Resolves the registered Slim routes to the API classes and methods handling
 * them, so the spec builder can introspect those classes. The Slim route
 * pattern is translated into an OpenAPI path template here, so that everything
 * downstream only ever sees the template.
 */
class RouteIntrospector {
  /**
   * @return list<RouteTarget>
   * @throws ReflectionException
   */
  public function introspect(App $app): array {
    $targets = [];
    $routes = $app->getRouteCollector()->getRoutes();
    foreach ($routes as $route) {
      /* Quirk to receive className, since it is hidden in a protected variable */
      $reflectionOfRoute = new ReflectionObject($route);
      $protectedCallable = $reflectionOfRoute->getProperty('callable');
      $reflectionCallable = ($protectedCallable->getValue($route));

      /* Assume only one method per route call */
      assert(sizeof($route->getMethods()) == 1, "More than 1 methods found for this route");
      /* Path relative to basePath */
      $pattern = $route->getPattern();
      $method = strtolower($route->getMethods()[0]);

      /* Retrieve parameters. Model API routes register array callables
         [Class::class, 'method']; helper API routes register string
         callables "Class:method". OPTIONS (CORS) use Closures (ignored). */
      if (is_array($reflectionCallable)) {
        $apiClassName = is_object($reflectionCallable[0]) ? get_class($reflectionCallable[0]) : $reflectionCallable[0];
        $apiMethod = $reflectionCallable[1];
      } elseif (is_string($reflectionCallable)) {
        $explodedCallable = explode(':', $reflectionCallable);
        $apiClassName = $explodedCallable[0];
        $apiMethod = $explodedCallable[1];
      } else {
        continue;
      }
      $targets[] = new RouteTarget($pattern, $this->cleanPathTemplate($pattern), $method, $apiClassName, $apiMethod);
    }
    return $targets;
  }

  /**
   * Turns a Slim route pattern into an OpenAPI path template, dropping the
   * regex constraint of every placeholder, e.g.
   * "/importFile/{id:[0-9]{14}-[0-9a-f]{32}}" becomes "/importFile/{id}".
   * Such a constraint contains balanced braces of its own, so the brace
   * closing the placeholder is found by counting depth rather than by
   * matching up to the first "}".
   */
  private function cleanPathTemplate(string $path): string {
    $clean = '';
    $length = strlen($path);

    for ($i = 0; $i < $length; $i++) {
      if ($path[$i] !== '{') {
        $clean .= $path[$i];
        continue;
      }

      $depth = 0;
      $end = -1;
      for ($j = $i; $j < $length; $j++) {
        if ($path[$j] === '{') {
          $depth++;
        } elseif ($path[$j] === '}') {
          $depth--;
          if ($depth === 0) {
            $end = $j;
            break;
          }
        }
      }
      /* Unbalanced braces, keep the remainder as-is instead of mangling it */
      if ($end === -1) {
        $clean .= substr($path, $i);
        break;
      }

      $placeholder = substr($path, $i + 1, $end - $i - 1);
      $name = strstr($placeholder, ':', true);
      $clean .= '{' . ($name === false ? $placeholder : $name) . '}';
      $i = $end;
    }

    return $clean;
  }
}
