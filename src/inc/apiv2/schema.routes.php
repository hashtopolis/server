<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Factory;
use DBA\Config;
use DBA\QueryFilter;
use DBA\OrderFilter;

require_once(dirname(__FILE__) . "/shared.inc.php");

$app->group("/api/v2/ui/openapi.json", function (RouteCollectorProxy $group) use($app) { 
    /* Allow CORS preflight requests */
    $group->options('', function (Request $request, Response $response): Response {
        return $response;
    });

    $group->get('', function (Request $request, Response $response) use($app): Response {

      $routes = $app->getRouteCollector()->getRoutes();
      // And then iterate over $routes
  
      $h = new HashAPI();
      print_r($h->getFeatures());

      foreach ($routes as $route) {
        /* Quirck to receive className, since it is hidden in a protected variable */
        $reflectionOfRoute = new \ReflectionObject($route);
        $protectedCallable =  $reflectionOfRoute->getProperty('callable');
        $protectedCallable->setAccessible(true);
        $reflectionCallable = ($protectedCallable->getValue($route));

        /* Assume only one method per route call */
        assert(sizeof($route->getMethods()) == 1);

        if (is_string($reflectionCallable) == false) {
          /* OPTIONS (CORS) have an function callable, ignore for now */
          continue;
        }

        echo $route->getMethods()[0], "\n";
        /* Retrieve parameters */
        $apiClassName = array_shift(explode(':', $reflectionCallable));
        echo $reflectionCallable, "\n";
        $class = new ReflectionClass($apiClassName);
        print_r($class->newInstance()->getFeatures());

        echo $route->getPattern(), "\n";
      };

      
      return $response;
    });
});
