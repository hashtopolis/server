<?php

namespace Hashtopolis\inc\apiv2\openapi;

use Slim\App;

/**
 * Resolves the registered Slim routes to the API classes and methods handling
 * them, so the spec builder can introspect those classes.
 */
class RouteIntrospector {
  /**
   * @return list<RouteTarget>
   */
  public function introspect(App $app): array {
    $targets = [];
    $routes = $app->getRouteCollector()->getRoutes();
    foreach ($routes as $route) {
      /* Quirk to receive className, since it is hidden in a protected variable */
      $reflectionOfRoute = new \ReflectionObject($route);
      $protectedCallable = $reflectionOfRoute->getProperty('callable');
      $reflectionCallable = ($protectedCallable->getValue($route));

      /* Assume only one method per route call */
      assert(sizeof($route->getMethods()) == 1, "More than 1 methods found for this route");
      /* Path relative to basePath */
      $path = $route->getPattern();
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
      $targets[] = new RouteTarget($path, $method, $apiClassName, $apiMethod);
    }
    return $targets;
  }
}
