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
   * An origin is accepted only when its scheme, host and effective port all match one of the origins
   * this deployment is configured to trust. Comparing the parts separately matters: the previous
   * string slicing reduced any portless URL to an empty host, so an arbitrary origin compared equal
   * to a portless HASHTOPOLIS_BACKEND_URL and was echoed back as trusted.
   *
   * Matching is deliberately exact. There is no wildcard and no suffix matching, because a trusted
   * origin is handed credentials, and `https://app.example.com` must not admit
   * `https://app.example.com.evil.test`.
   *
   * @throws HttpForbidden when an origin is supplied that the deployment does not recognise
   */
  public static function CheckCORS($request, $response): Response {
    $requestHttpOrigin = $request->getHeaderLine('HTTP_ORIGIN');

    $allowed = self::allowedOrigins();

    /* With nothing configured there is nothing to check an origin against, so the API stays readable
       from anywhere but never on a credentialed request: browsers reject credentials next to a
       wildcard, which is also why the refresh token cookie needs one of the settings below. */
    if (count($allowed) === 0 || $requestHttpOrigin === "") {
      return $response->withHeader('Access-Control-Allow-Origin', '*');
    }

    $origin = self::parseOrigin($requestHttpOrigin);
    if ($origin === null) {
      throw new HttpForbidden("CORS error: the request Origin '$requestHttpOrigin' is not a usable http(s) origin.");
    }

    foreach ($allowed as $candidate) {
      if (self::originsMatch($origin, $candidate)) {
        return self::allowOrigin($requestHttpOrigin, $response);
      }
    }

    $expected = implode(', ', array_map(self::describeOrigin(...), $allowed));
    throw new HttpForbidden("CORS error: the request Origin '$requestHttpOrigin' does not match this deployment. Allowed origins are: $expected. Check HASHTOPOLIS_BACKEND_URL, HASHTOPOLIS_FRONTEND_URLS and HASHTOPOLIS_FRONTEND_PORT.");
  }

  /**
   * Resolves every origin this deployment trusts, from the three settings that can name one.
   *
   * HASHTOPOLIS_FRONTEND_PORT is folded in as a derived origin rather than handled as a special case
   * further down, so there is a single matching rule and the legacy setting cannot drift from the
   * list. It names a port on the API's own host, so it only contributes when the backend URL is
   * known.
   *
   * @return list<array{scheme: string, host: string, port: int}>
   * @throws HttpForbidden when a setting names something that is not an http(s) origin
   */
  private static function allowedOrigins(): array {
    $envBackend = self::readSetting('HASHTOPOLIS_BACKEND_URL');
    $envFrontendUrls = self::readSetting('HASHTOPOLIS_FRONTEND_URLS');
    $envFrontendPort = self::readSetting('HASHTOPOLIS_FRONTEND_PORT');

    $allowed = [];
    $backend = null;

    if ($envBackend !== null) {
      $backend = self::parseOrigin($envBackend);
      if ($backend === null) {
        throw new HttpForbidden("CORS error: HASHTOPOLIS_BACKEND_URL ('$envBackend') is not a usable http(s) URL. It should look like 'https://hashtopolis.example.com' or 'http://localhost:8080'.");
      }
      $allowed[] = $backend;
    }

    if ($envFrontendUrls !== null) {
      foreach (explode(',', $envFrontendUrls) as $entry) {
        $entry = trim($entry);
        // A trailing comma carries no intent and names nothing that could be reported
        if ($entry === "") {
          continue;
        }

        $parsed = self::parseOrigin($entry);
        if ($parsed === null) {
          throw new HttpForbidden("CORS error: HASHTOPOLIS_FRONTEND_URLS contains '$entry', which is not a usable http(s) origin. Each entry should look like 'https://app.example.com' or 'http://localhost:4200'.");
        }
        $allowed[] = $parsed;
      }
    }

    if ($envFrontendPort !== null && ctype_digit($envFrontendPort)) {
      if ($backend === null) {
        /* Without a backend URL there is no host to attach the port to. The shipped compose files
           hardcode this setting, so refusing the request would break every deployment that adopts
           HASHTOPOLIS_FRONTEND_URLS; say so in the log and carry on with the origins we do have. */
        error_log("HASHTOPOLIS_FRONTEND_PORT is set but HASHTOPOLIS_BACKEND_URL is not, so there is no host to apply the port to. Name the frontend in HASHTOPOLIS_FRONTEND_URLS instead.");
      }
      else {
        $allowed[] = ['scheme' => $backend['scheme'], 'host' => $backend['host'], 'port' => (int)$envFrontendPort];
      }
    }

    return $allowed;
  }

  /**
   * Reads a deployment setting, treating a variable that is present but empty as one that was never
   * set.
   *
   * Docker Compose writes an empty value for every `FOO: $FOO` entry whose variable is missing from
   * the .env file, and getenv() answers "" for that rather than false. Without this, blanking a
   * setting turns into a configuration error reported on every request, naming a variable the
   * operator deliberately left empty.
   *
   * @param string $name
   * @return string|null the trimmed value, or null when unset, empty or only whitespace
   */
  private static function readSetting(string $name): ?string {
    $value = getenv($name);
    if ($value === false) {
      return null;
    }
    $value = trim($value);

    return $value === "" ? null : $value;
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
   * @param array{scheme: string, host: string, port: int} $candidate an origin this deployment trusts
   */
  private static function originsMatch(array $origin, array $candidate): bool {
    return $origin['scheme'] === $candidate['scheme']
      && $origin['port'] === $candidate['port']
      && self::isSameHost($origin['host'], $candidate['host']);
  }

  /**
   * @param array{scheme: string, host: string, port: int} $origin
   */
  private static function describeOrigin(array $origin): string {
    return $origin['scheme'] . '://' . $origin['host'] . ':' . $origin['port'];
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
