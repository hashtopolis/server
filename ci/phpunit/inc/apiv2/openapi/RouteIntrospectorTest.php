<?php

namespace Hashtopolis\inc\apiv2\openapi;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

/**
 * The introspector is where a Slim route pattern becomes an OpenAPI path
 * template, so that no consumer of the spec ever sees a regex constraint.
 */
final class RouteIntrospectorTest extends TestCase {
  /**
   * @return array<string, RouteTarget> targets by path template
   */
  private function introspect(callable $registerRoutes): array {
    $app = AppFactory::create();
    $registerRoutes($app);

    $targets = [];
    foreach ((new RouteIntrospector())->introspect($app) as $target) {
      $targets[$target->pathTemplate] = $target;
    }
    return $targets;
  }

  public function testDropsTheRegexConstraintOfAPlaceholder(): void {
    $targets = $this->introspect(function ($app) {
      $app->delete('/api/v2/ui/things/{id:[0-9]+}', [RouteIntrospectorTestStub::class, 'handle']);
    });

    $this->assertSame(['/api/v2/ui/things/{id}'], array_keys($targets));
    $this->assertSame('delete', $targets['/api/v2/ui/things/{id}']->httpMethod);
  }

  /**
   * A constraint carries balanced braces of its own, which must neither end
   * the placeholder early nor be mistaken for a second one.
   */
  public function testDropsAConstraintContainingBraces(): void {
    $targets = $this->introspect(function ($app) {
      $app->patch('/api/v2/helper/importFile/{id:[0-9]{14}-[0-9a-f]{32}}', [RouteIntrospectorTestStub::class, 'handle']);
    });

    $this->assertSame(['/api/v2/helper/importFile/{id}'], array_keys($targets));
  }

  public function testKeepsAPlaceholderWithoutAConstraintAndPlainSegments(): void {
    $targets = $this->introspect(function ($app) {
      $app->get('/api/v2/ui/things/{id}/count', [RouteIntrospectorTestStub::class, 'handle']);
      $app->get('/api/v2/helper/abortChunk', [RouteIntrospectorTestStub::class, 'handle']);
    });

    $this->assertSame(
      ['/api/v2/ui/things/{id}/count', '/api/v2/helper/abortChunk'],
      array_keys($targets)
    );
  }

  /**
   * The raw pattern stays available because its constraints say more than how
   * to match: a relationship route names its relation in the constraint of the
   * "relation" placeholder, which ModelApiPathBuilder reads back out.
   */
  public function testKeepsTheRawPatternNextToTheTemplate(): void {
    $pattern = '/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:userMembers}';
    $targets = $this->introspect(function ($app) use ($pattern) {
      $app->get($pattern, [RouteIntrospectorTestStub::class, 'handle']);
    });

    $target = $targets['/api/v2/ui/accessgroups/{id}/relationships/{relation}'];
    $this->assertSame($pattern, $target->pattern);
    $this->assertSame(RouteIntrospectorTestStub::class, $target->className);
    $this->assertSame('handle', $target->methodName);
  }

  /**
   * OPTIONS routes are registered as closures for CORS and carry no API class
   * to introspect.
   */
  public function testSkipsRoutesWithoutAnApiClass(): void {
    $targets = $this->introspect(function ($app) {
      $app->options('/api/v2/ui/things', function (Request $request, Response $response): Response {
        return $response;
      });
    });

    $this->assertSame([], $targets);
  }
}

/**
 * Stand-in for an API class: the introspector only resolves the callable to a
 * class and method name, it never invokes it.
 */
final class RouteIntrospectorTestStub {
  public function handle(Request $request, Response $response): Response {
    return $response;
  }
}
