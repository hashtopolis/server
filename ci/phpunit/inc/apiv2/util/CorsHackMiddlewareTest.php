<?php

namespace Hashtopolis\inc\apiv2\util;

use PHPUnit\Framework\TestCase;

use Hashtopolis\inc\apiv2\error\HttpForbidden;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;

use Slim\Factory\AppFactory;

class DummyRequest {
  private string $http_origin;
  private string $host = 'hashtopolis.example.com';

  public function setHeaderLine($headerLine): void {
    $this->http_origin = $headerLine;
  }
  
  public function getHeaderLine($headerLine): string {
    return $this->http_origin;
  }

  public function setHost(string $host): void {
    $this->host = $host;
  }

  /** Enough of a PSR-7 URI for the cross-site guard, which only asks for the host. */
  public function getUri(): object {
    return new class($this->host) {
      public function __construct(private string $host) {}

      public function getHost(): string {
        return $this->host;
      }
    };
  }
}

final class CorsHackMiddlewareTest extends TestCase {
  /**
   * Each test sets only the variables it exercises, so anything left behind by the previous test
   * would silently take part in the next one and make results depend on execution order.
   */
  #[Override]
  protected function setUp(): void {
    parent::setUp();

    putenv("HASHTOPOLIS_BACKEND_URL");
    putenv("HASHTOPOLIS_FRONTEND_PORT");
    putenv("HASHTOPOLIS_FRONTEND_URLS");
  }

  #[Override]
  protected function tearDown(): void {
    putenv("HASHTOPOLIS_BACKEND_URL");
    putenv("HASHTOPOLIS_FRONTEND_PORT");
    putenv("HASHTOPOLIS_FRONTEND_URLS");

    parent::tearDown();
  }

  /**
   * Tests all possible valid localhost variations with different ports.
   *
   * @return void
   * @throws HttpForbidden
   */
  public function testValidLocalhostVariations(): void {
    $this->expectNotToPerformAssertions();

    putenv("HASHTOPOLIS_BACKEND_URL=http://localhost:8080/api/v2");
    putenv("HASHTOPOLIS_FRONTEND_PORT=4200");

    $app = AppFactory::create();
    
    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    $request->setHeaderLine("http://127.0.0.1:4200");
    CorsHackMiddleware::CheckCORS($request, $response);

    $request->setHeaderLine("http://localhost:4200");
    CorsHackMiddleware::CheckCORS($request, $response);

    $request->setHeaderLine("http://[::1]:4200");
    CorsHackMiddleware::CheckCORS($request, $response);

    $request->setHeaderLine("http://127.0.0.1:8080");
    CorsHackMiddleware::CheckCORS($request, $response);

    $request->setHeaderLine("http://localhost:8080");
    CorsHackMiddleware::CheckCORS($request, $response);

    $request->setHeaderLine("http://[::1]:8080");
    CorsHackMiddleware::CheckCORS($request, $response);
  }

  /**
   * The same localhost variations over https, against an https backend.
   *
   * @return void
   * @throws HttpForbidden
   */
  public function testValidLocalhostVariationsOverHttps(): void {
    $this->expectNotToPerformAssertions();

    putenv("HASHTOPOLIS_BACKEND_URL=https://localhost:8080/api/v2");
    putenv("HASHTOPOLIS_FRONTEND_PORT=4200");

    $app = AppFactory::create();

    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    foreach (["127.0.0.1", "localhost", "[::1]"] as $host) {
      foreach ([4200, 8080] as $port) {
        $request->setHeaderLine("https://$host:$port");
        CorsHackMiddleware::CheckCORS($request, $response);
      }
    }
  }

  /**
   * Tests an invalid origin port for localhost.
   * 
   * @throws HttpForbidden
  */ 
  public function testInvalidLocalhostPort(): void {
    $this->expectException(HttpForbidden::class);

    putenv("HASHTOPOLIS_BACKEND_URL=http://localhost:8080/api/v2");
    putenv("HASHTOPOLIS_FRONTEND_PORT=4200");

    $app = AppFactory::create();
    
    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    $request->setHeaderLine("http://127.0.0.1:4201");
    
    CorsHackMiddleware::CheckCORS($request, $response);
  }

  /**
   * Tests an evil origin making requests to localhost.
   * 
   * @throws HttpForbidden
  */ 
  public function testEvilDomainForLocalhost(): void {
    $this->expectException(HttpForbidden::class);

    putenv("HASHTOPOLIS_BACKEND_URL=http://localhost:8080/api/v2");
    putenv("HASHTOPOLIS_FRONTEND_PORT=4200");

    $app = AppFactory::create();
    
    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    $request->setHeaderLine("http://evil.com:4200");
    
    CorsHackMiddleware::CheckCORS($request, $response);
  }

  /**
   * Tests an evil ip address making requests to localhost.
   * 
   * @throws HttpForbidden
  */ 
  public function testEvilIPForLocalhost(): void {
    $this->expectException(HttpForbidden::class);

    putenv("HASHTOPOLIS_BACKEND_URL=http://localhost:8080/api/v2");
    putenv("HASHTOPOLIS_FRONTEND_PORT=4200");

    $app = AppFactory::create();
    
    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    $request->setHeaderLine("http://137.137.137.1:4200");
    
    CorsHackMiddleware::CheckCORS($request, $response);
  }

  /**
   * Tests an invalid origin port on a correct hashtopolis domain.
   * 
   * @throws HttpForbidden
  */ 
  public function testInvalidDomainPort(): void {
    $this->expectException(HttpForbidden::class);

    putenv("HASHTOPOLIS_BACKEND_URL=http://hashtopolis-cluster.com:8080/api/v2");
    putenv("HASHTOPOLIS_FRONTEND_PORT=4200");

    $app = AppFactory::create();
    
    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    $request->setHeaderLine("http://hashtopolis-cluster.com:4201");
    
    CorsHackMiddleware::CheckCORS($request, $response);
  }
  
  /**
   * Tests a valid domain without port as origin.
   *
   * @throws HttpForbidden
   */
  public function testValidDomainWithoutPort(): void {
    $this->expectNotToPerformAssertions();

    putenv("HASHTOPOLIS_BACKEND_URL=http://hashtopolis-cluster.com/api/v2");
    putenv("HASHTOPOLIS_FRONTEND_PORT=4200");

    $app = AppFactory::create();
    
    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    $request->setHeaderLine("http://hashtopolis-cluster.com");
    CorsHackMiddleware::CheckCORS($request, $response);
  }

  /**
   * Tests a valid https-domain without port as origin.
   * 
  */ 
  public function testValidHttpsDomainWithoutPort(): void {
    $this->expectNotToPerformAssertions();

    putenv("HASHTOPOLIS_BACKEND_URL=https://hashtopolis-cluster.com/api/v2");
    putenv("HASHTOPOLIS_FRONTEND_PORT=4200");

    $app = AppFactory::create();
    
    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    $request->setHeaderLine("https://hashtopolis-cluster.com");
    CorsHackMiddleware::CheckCORS($request, $response);
  }
  
  /**
   * Tests an https origin against an http backend URL.
   *
   * The scheme is part of the comparison: http and https are different origins, and treating them
   * as one would let a plain-http page read responses meant for the https deployment. A deployment
   * that moved behind TLS has to say so in HASHTOPOLIS_BACKEND_URL.
   *
   * @throws HttpForbidden
   */
  public function testSchemeMismatchIsRejected(): void {
    $this->expectException(HttpForbidden::class);

    putenv("HASHTOPOLIS_BACKEND_URL=http://hashtopolis-cluster.com:8080/api/v2");
    putenv("HASHTOPOLIS_FRONTEND_PORT=4200");

    $app = AppFactory::create();
    
    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    $request->setHeaderLine("https://hashtopolis-cluster.com:8080");
    CorsHackMiddleware::CheckCORS($request, $response);
  }
  
  /**
   * Tests a valid domain with a differnt frontend port as origin.
   *
   * @throws HttpForbidden
   */
  public function testValidDomainWithoutDifferentFrontendPort(): void {
    $this->expectNotToPerformAssertions();

    putenv("HASHTOPOLIS_BACKEND_URL=http://hashtopolis-cluster.com:8080/api/v2");
    putenv("HASHTOPOLIS_FRONTEND_PORT=5000");

    $app = AppFactory::create();
    
    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    $request->setHeaderLine("http://hashtopolis-cluster.com:5000");
    CorsHackMiddleware::CheckCORS($request, $response);
  }

  /**
   * Applies the three settings and answers what the middleware decided, so a case reads as one line
   * rather than fifteen of setup.
   *
   * @return string the echoed origin, "*" for the wildcard, or "REJECTED"
   */
  private function decide(?string $backendUrl, ?string $frontendUrls, ?string $frontendPort, string $origin): string {
    foreach ([
      "HASHTOPOLIS_BACKEND_URL" => $backendUrl,
      "HASHTOPOLIS_FRONTEND_URLS" => $frontendUrls,
      "HASHTOPOLIS_FRONTEND_PORT" => $frontendPort
    ] as $name => $value) {
      putenv($value === null ? $name : "$name=$value");
    }

    $request = new DummyRequest();
    $request->setHeaderLine($origin);

    try {
      $response = CorsHackMiddleware::CheckCORS($request, AppFactory::create()->getResponseFactory()->createResponse());
    } catch (HttpForbidden) {
      return "REJECTED";
    }

    return $response->getHeaderLine("Access-Control-Allow-Origin");
  }

  public static function allowedOriginProvider(): array {
    $api = "https://api.example.com";
    $app = "https://app.example.com";

    return [
      // A frontend on an entirely different host is the case the list exists for
      "different host, listed" => [$api, $app, null, $app, $app],
      "different host, not listed" => [$api, "https://other.example.com", null, $app, "REJECTED"],
      "different scheme and port, listed" => [$api, "http://app.example.com:8081", null, "http://app.example.com:8081", "http://app.example.com:8081"],

      // The union: every source still contributes while a list is set
      "backend own origin survives a list" => [$api, $app, null, $api, $api],
      "legacy frontend port survives a list" => [$api, $app, "4200", "https://api.example.com:4200", "https://api.example.com:4200"],

      // The list alone is a complete policy
      "list without backend url allows" => [null, $app, null, $app, $app],
      "list without backend url rejects others" => [null, $app, null, "https://evil.example", "REJECTED"],
      "lonely frontend port allows nothing" => [null, $app, "4200", "https://elsewhere.example:4200", "REJECTED"],

      // Empty is the same as unset, which is what Docker Compose produces for a missing variable
      "empty backend url with a list" => ["", $app, null, $app, $app],
      "empty backend url alone" => ["", null, null, $app, "*"],
      "empty list behaves as unset" => [$api, "", "4200", "https://api.example.com:4200", "https://api.example.com:4200"],
      "empty frontend port is ignored" => [$api, $app, "", "https://api.example.com:4200", "REJECTED"],

      // Tolerated shapes
      "whitespace around entries" => [$api, "  $app ,  https://b.example.com  ", null, $app, $app],
      "trailing and doubled commas" => [$api, "$app,,", null, $app, $app],
      "entry carrying a path" => [$api, "$app/ui", null, $app, $app],
      "entry with a trailing slash" => [$api, "$app/", null, $app, $app],
      "mixed case entry" => [$api, "HTTPS://APP.Example.COM", null, $app, $app],
      "second entry of several" => [$api, "https://a.example.com,$app", null, $app, $app],

      // Nothing about a list may loosen the matching rule
      "suffix of a listed host" => [$api, $app, null, "https://app.example.com.evil.test", "REJECTED"],
      "substring of a listed host" => [$api, "https://example.com", null, "https://evil-example.com", "REJECTED"],
      "subdomain of a listed host" => [$api, "https://example.com", null, "https://sub.example.com", "REJECTED"],
      "listed host on another port" => [$api, $app, null, "https://app.example.com:8443", "REJECTED"],
      "listed host on its implied port" => [$api, $app, null, "https://app.example.com:443", "https://app.example.com:443"],
      "listed host over another scheme" => [$api, $app, null, "http://app.example.com:443", "REJECTED"],
      "literal null origin" => [$api, "$app,null", null, "null", "REJECTED"],
      "two origins in one header" => [$api, "https://a.example.com,https://b.example.com", null, "https://a.example.com https://b.example.com", "REJECTED"],

      // The loopback spellings name one machine, per entry
      "loopback synonym of an entry" => [null, "http://localhost:4200", null, "http://127.0.0.1:4200", "http://127.0.0.1:4200"],
      "ipv6 loopback entry" => [null, "http://[::1]:4200", null, "http://[::1]:4200", "http://[::1]:4200"],
      "a non loopback address is not a synonym" => [null, "http://127.0.0.2:4200", null, "http://localhost:4200", "REJECTED"],
    ];
  }

  /**
   * @throws HttpForbidden
   */
  #[DataProvider('allowedOriginProvider')]
  public function testOriginDecisions(?string $backendUrl, ?string $frontendUrls, ?string $frontendPort, string $origin, string $expected): void {
    $this->assertSame($expected, $this->decide($backendUrl, $frontendUrls, $frontendPort, $origin));
  }

  /**
   * A listed origin is trusted with credentials; the wildcard never is.
   *
   * @throws HttpForbidden
   */
  public function testListedOriginIsTrustedWithCredentials(): void {
    putenv("HASHTOPOLIS_FRONTEND_URLS=https://app.example.com");

    $request = new DummyRequest();
    $request->setHeaderLine("https://app.example.com");

    $response = CorsHackMiddleware::CheckCORS($request, AppFactory::create()->getResponseFactory()->createResponse());

    $this->assertSame("https://app.example.com", $response->getHeaderLine("Access-Control-Allow-Origin"));
    $this->assertSame("true", $response->getHeaderLine("Access-Control-Allow-Credentials"));
    $this->assertStringContainsString("Origin", $response->getHeaderLine("Vary"));
  }

  /**
   * A duplicated entry must not produce a comma joined header, which no browser accepts.
   *
   * @throws HttpForbidden
   */
  public function testDuplicateEntriesEchoASingleOrigin(): void {
    $this->assertSame(
      "https://app.example.com",
      $this->decide(null, "https://app.example.com,https://app.example.com", null, "https://app.example.com")
    );
  }

  /**
   * A list that is set but matches nothing must never fall back to the wildcard: that is the
   * fail-open direction, and it would handing out a permissive policy exactly when one was refused.
   *
   * @throws HttpForbidden
   */
  public function testAnUnmatchedListNeverFallsBackToTheWildcard(): void {
    $this->assertNotSame("*", $this->decide(null, "https://app.example.com", null, "https://evil.example"));
  }

  #[DataProvider('malformedEntryProvider')]
  public function testMalformedListEntryIsReported(string $entry): void {
    putenv("HASHTOPOLIS_FRONTEND_URLS=$entry");

    $request = new DummyRequest();
    $request->setHeaderLine("https://app.example.com");

    try {
      CorsHackMiddleware::CheckCORS($request, AppFactory::create()->getResponseFactory()->createResponse());
      $this->fail("A malformed HASHTOPOLIS_FRONTEND_URLS entry should be reported, not ignored");
    } catch (HttpForbidden $e) {
      $this->assertStringContainsString($entry, $e->getMessage(), "the message should name the offending entry");
    }
  }

  public static function malformedEntryProvider(): array {
    return [
      "bare host" => ["app.example.com"],
      "bare host and port" => ["app.example.com:4200"],
      "bare port" => ["4200"],
      "scheme relative" => ["//app.example.com"],
      "ftp" => ["ftp://app.example.com"],
      "file" => ["file:///etc/passwd"],
      "javascript" => ["javascript:alert(1)"],
      "the word null" => ["null"],
    ];
  }

  /**
   * One bad entry invalidates the whole list, including for an origin that matches a good entry
   * before it. Otherwise whether a typo is noticed depends on the order of the list.
   */
  public function testAMalformedEntryInvalidatesTheWholeList(): void {
    $this->expectException(HttpForbidden::class);

    putenv("HASHTOPOLIS_FRONTEND_URLS=https://app.example.com,not-an-origin");

    $request = new DummyRequest();
    $request->setHeaderLine("https://app.example.com");

    CorsHackMiddleware::CheckCORS($request, AppFactory::create()->getResponseFactory()->createResponse());
  }

  /**
   * A CR/LF bearing origin must not match and must not reach the response headers.
   *
   * @throws HttpForbidden
   */
  public function testOriginWithControlCharactersIsRejected(): void {
    putenv("HASHTOPOLIS_FRONTEND_URLS=https://app.example.com");

    $request = new DummyRequest();
    $request->setHeaderLine("https://app.example.com\r\nX-Evil: 1");

    $response = AppFactory::create()->getResponseFactory()->createResponse();

    try {
      $response = CorsHackMiddleware::CheckCORS($request, $response);
    } catch (HttpForbidden) {
      $this->addToAssertionCount(1);
    }

    $this->assertFalse($response->hasHeader("X-Evil"));
  }

  /**
   * The cross-site guard protects endpoints that authenticate from a cookie. A browser attaches the
   * cookie to whatever request a page makes, so without this a page elsewhere could have a visitor's
   * browser spend their refresh token or end their session.
   *
   * @throws HttpForbidden
   */
  public function testCrossSiteGuardAcceptsAConfiguredOrigin(): void {
    putenv("HASHTOPOLIS_BACKEND_URL=https://hashtopolis.example.com");
    putenv("HASHTOPOLIS_FRONTEND_URLS=https://app.example.com");

    $request = new DummyRequest();
    $request->setHeaderLine("https://app.example.com");

    CorsHackMiddleware::assertNotCrossSite($request);
    $this->addToAssertionCount(1);
  }

  public function testCrossSiteGuardRefusesAnUnconfiguredOrigin(): void {
    putenv("HASHTOPOLIS_BACKEND_URL=https://hashtopolis.example.com");
    putenv("HASHTOPOLIS_FRONTEND_URLS=https://app.example.com");

    $request = new DummyRequest();
    $request->setHeaderLine("https://evil.example");

    $this->expectException(HttpForbidden::class);
    CorsHackMiddleware::assertNotCrossSite($request);
  }

  /**
   * A client that sends no origin is not a browser, so it holds no ambient cookie to be abused and
   * must keep working: this is how curl and the python client call the API.
   *
   * @throws HttpForbidden
   */
  public function testCrossSiteGuardAllowsARequestWithoutAnOrigin(): void {
    putenv("HASHTOPOLIS_BACKEND_URL=https://hashtopolis.example.com");

    $request = new DummyRequest();
    $request->setHeaderLine("");

    CorsHackMiddleware::assertNotCrossSite($request);
    $this->addToAssertionCount(1);
  }

  /**
   * With no allow-list there is nothing to compare an origin against, and the CORS layer answers the
   * wildcard. A cookie endpoint cannot rely on that, so the guard falls back to the host the request
   * was addressed to.
   *
   * @throws HttpForbidden
   */
  public function testCrossSiteGuardFallsBackToTheRequestHost(): void {
    $request = new DummyRequest();
    $request->setHost("hashtopolis.example.com");
    $request->setHeaderLine("https://hashtopolis.example.com");

    CorsHackMiddleware::assertNotCrossSite($request);
    $this->addToAssertionCount(1);
  }

  /**
   * The case SameSite=Strict does not cover: a neighbouring host is same-site, so the browser sends
   * the cookie, but it is a different origin and has no business acting for the user.
   */
  public function testCrossSiteGuardRefusesANeighbouringHostWithNoAllowList(): void {
    $request = new DummyRequest();
    $request->setHost("hashtopolis.example.com");
    $request->setHeaderLine("https://evil.hashtopolis.example.com");

    $this->expectException(HttpForbidden::class);
    CorsHackMiddleware::assertNotCrossSite($request);
  }

  /**
   * Regression: a portless origin must not match a portless HASHTOPOLIS_BACKEND_URL.
   *
   * Slicing each URL at its last colon turned both a portless origin and a portless backend URL into
   * an empty host, which compared equal. Any site could therefore be echoed back as an allowed
   * origin, and since these responses also carry Allow-Credentials, read authenticated replies. The
   * configuration that triggered it is the ordinary one for TLS: a backend URL with no explicit port.
   *
   * @throws HttpForbidden
   */
  public function testPortlessEvilOriginDoesNotMatchPortlessBackendUrl(): void {
    $this->expectException(HttpForbidden::class);

    putenv("HASHTOPOLIS_BACKEND_URL=https://hashtopolis-cluster.com/api/v2");
    putenv("HASHTOPOLIS_FRONTEND_PORT=4200");

    $app = AppFactory::create();

    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    $request->setHeaderLine("https://evil.com");
    CorsHackMiddleware::CheckCORS($request, $response);
  }

  /**
   * A subdomain is a different origin, even though it shares a suffix with the configured host.
   *
   * @throws HttpForbidden
   */
  public function testSubdomainOfConfiguredHostIsRejected(): void {
    $this->expectException(HttpForbidden::class);

    putenv("HASHTOPOLIS_BACKEND_URL=https://hashtopolis-cluster.com/api/v2");
    putenv("HASHTOPOLIS_FRONTEND_PORT=4200");

    $app = AppFactory::create();

    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    $request->setHeaderLine("https://evil.hashtopolis-cluster.com");
    CorsHackMiddleware::CheckCORS($request, $response);
  }

  /**
   * The port a scheme implies is filled in, so the two spellings of the same origin agree.
   *
   * @throws HttpForbidden
   */
  public function testExplicitDefaultPortMatchesPortlessBackendUrl(): void {
    putenv("HASHTOPOLIS_BACKEND_URL=https://hashtopolis-cluster.com/api/v2");
    putenv("HASHTOPOLIS_FRONTEND_PORT=4200");

    $app = AppFactory::create();

    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    $request->setHeaderLine("https://hashtopolis-cluster.com:443");
    $response = CorsHackMiddleware::CheckCORS($request, $response);

    $this->assertSame("https://hashtopolis-cluster.com:443", $response->getHeaderLine("Access-Control-Allow-Origin"));
  }

  /**
   * A verified origin is the only thing credentials are granted to, and the response says it varies
   * by origin so a cache cannot hand one origin's headers to another.
   *
   * @throws HttpForbidden
   */
  public function testVerifiedOriginGetsCredentialsAndVaries(): void {
    putenv("HASHTOPOLIS_BACKEND_URL=http://localhost:8080/api/v2");
    putenv("HASHTOPOLIS_FRONTEND_PORT=4200");

    $app = AppFactory::create();

    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    $request->setHeaderLine("http://localhost:4200");
    $response = CorsHackMiddleware::CheckCORS($request, $response);

    $this->assertSame("http://localhost:4200", $response->getHeaderLine("Access-Control-Allow-Origin"));
    $this->assertSame("true", $response->getHeaderLine("Access-Control-Allow-Credentials"));
    $this->assertStringContainsString("Origin", $response->getHeaderLine("Vary"));
  }

  /**
   * Without a configured backend URL there is nothing to verify an origin against, so the API stays
   * open but never grants credentials: browsers refuse them next to a wildcard.
   *
   * @throws HttpForbidden
   */
  public function testWithoutBackendUrlTheWildcardCarriesNoCredentials(): void {
    putenv("HASHTOPOLIS_BACKEND_URL");
    putenv("HASHTOPOLIS_FRONTEND_PORT");

    $app = AppFactory::create();

    $request = new DummyRequest();

    $response = $app->getResponseFactory()->createResponse();

    $request->setHeaderLine("https://evil.com");
    $response = CorsHackMiddleware::CheckCORS($request, $response);

    $this->assertSame("*", $response->getHeaderLine("Access-Control-Allow-Origin"));
    $this->assertSame("", $response->getHeaderLine("Access-Control-Allow-Credentials"));
  }
}
