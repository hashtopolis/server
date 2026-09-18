<?php

namespace Hashtopolis\inc\apiv2\util;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

use Hashtopolis\inc\apiv2\error\HttpForbidden;

/* This middleware will append the response header Access-Control-Allow-Methods with all allowed methods */
class CorsHackMiddleware implements MiddlewareInterface {
  /**
   * @throws HttpForbidden
   */
  public function process(Request $request, RequestHandler $handler): Response {
    $response = $handler->handle($request);
    
    return CorsHackMiddleware::addCORSHeaders($request, $response);
  }
  
  /**
   * @throws HttpForbidden
   */
  public static function addCORSHeaders(Request $request, $response) {
    $routeContext = RouteContext::fromRequest($request);
    $routingResults = $routeContext->getRoutingResults();
    $methods = $routingResults->getAllowedMethods();
    
    $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');

    $response = CorsHackMiddleware::CheckCORS($request, $response);
    
    $response = $response->withHeader('Access-Control-Allow-Methods', implode(',', $methods));
    return $response->withHeader('Access-Control-Allow-Headers', $requestHeaders);
  }
  
  /**
   * Decides which origin, if any, the response may be shared with.
   *
   * An origin is only accepted when its scheme, host and effective port all match the deployment's
   * own origin or the frontend's port on that same host. Comparing the parts separately matters:
   * the previous string slicing reduced any portless URL to an empty host, so an arbitrary origin
   * compared equal to a portless HASHTOPOLIS_BACKEND_URL and was echoed back as trusted.
   *
   * @throws HttpForbidden when an origin is supplied that the deployment does not recognise
   */
  public static function CheckCORS($request, $response): Response {
    $requestHttpOrigin = $request->getHeaderLine('HTTP_ORIGIN');

    $envBackend = getenv('HASHTOPOLIS_BACKEND_URL');
    $envFrontendPort = getenv('HASHTOPOLIS_FRONTEND_PORT');

    /* Without a backend URL there is nothing to check an origin against, so the API stays readable
       from anywhere but never on a credentialed request: browsers reject credentials next to a
       wildcard, which is also why the refresh token cookie needs HASHTOPOLIS_BACKEND_URL set. */
    if ($envBackend === false || $requestHttpOrigin === "") {
      return $response->withHeader('Access-Control-Allow-Origin', '*');
    }

    $origin = self::parseOrigin($requestHttpOrigin);
    if ($origin === null) {
      throw new HttpForbidden("CORS error: the request Origin '$requestHttpOrigin' is not a usable http(s) origin.");
    }

    $backend = self::parseOrigin($envBackend);
    if ($backend === null) {
      throw new HttpForbidden("CORS error: HASHTOPOLIS_BACKEND_URL ('$envBackend') is not a usable http(s) URL. It should look like 'https://hashtopolis.example.com' or 'http://localhost:8080'.");
    }

    if (!self::isAllowedOrigin($origin, $backend, $envFrontendPort)) {
      $expected = $backend['scheme'] . '://' . $backend['host'] . ':' . $backend['port'];
      $frontend = ($envFrontendPort === false) ? 'unset' : $envFrontendPort;
      throw new HttpForbidden("CORS error: the request Origin '$requestHttpOrigin' does not match this deployment. Expected the scheme and host of $expected, on port {$backend['port']} or the frontend port ($frontend). Check HASHTOPOLIS_BACKEND_URL and HASHTOPOLIS_FRONTEND_PORT.");
    }

    return self::allowOrigin($requestHttpOrigin, $response);
  }

  /**
   * Splits a URL into the three parts that make up an origin, filling in the port the scheme implies
   * when the URL leaves it out, so that https://example.com and https://example.com:443 compare equal.
   *
   * @return array{scheme: string, host: string, port: int}|null null when this is not an http(s) URL
   */
  private static function parseOrigin(string $url): ?array {
    $parts = parse_url(trim($url));
    if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
      return null;
    }

    $scheme = strtolower($parts['scheme']);
    $defaultPorts = ['http' => 80, 'https' => 443];
    if (!array_key_exists($scheme, $defaultPorts)) {
      return null;
    }

    return [
      'scheme' => $scheme,
      'host' => strtolower($parts['host']),
      'port' => isset($parts['port']) ? (int)$parts['port'] : $defaultPorts[$scheme]
    ];
  }

  /**
   * @param array{scheme: string, host: string, port: int} $origin the origin the request came from
   * @param array{scheme: string, host: string, port: int} $backend this deployment's own origin
   * @param string|false $frontendPort the port the frontend is served on, when it has one of its own
   */
  private static function isAllowedOrigin(array $origin, array $backend, string|false $frontendPort): bool {
    if ($origin['scheme'] !== $backend['scheme'] || !self::isSameHost($origin['host'], $backend['host'])) {
      return false;
    }

    if ($origin['port'] === $backend['port']) {
      return true;
    }

    // The frontend is served from the same host as the API, but may sit on its own port
    return $frontendPort !== false && ctype_digit($frontendPort) && $origin['port'] === (int)$frontendPort;
  }

  /**
   * The loopback spellings all name the same machine, and a development setup routinely mixes them.
   */
  private static function isSameHost(string $origin, string $backend): bool {
    if ($origin === $backend) {
      return true;
    }

    $localhostSynonyms = ["localhost", "127.0.0.1", "[::1]"];

    return in_array($origin, $localhostSynonyms, true) && in_array($backend, $localhostSynonyms, true);
  }

  /**
   * Echoes back a single, verified origin and allows the browser to send credentials along with it.
   * Without Allow-Credentials the refresh token cookie would never reach /api/v2/auth/refresh from a
   * frontend served on another origin, and the header is only accepted next to a concrete origin.
   *
   * Vary tells caches that this response is specific to the origin that asked for it, so one origin's
   * response is never handed to another.
   *
   * @param string $origin an origin that has already been checked against this deployment
   */
  private static function allowOrigin(string $origin, Response $response): Response {
    return $response->withHeader('Access-Control-Allow-Origin', $origin)
      ->withHeader('Access-Control-Allow-Credentials', 'true')
      ->withAddedHeader('Vary', 'Origin');
  }
}
