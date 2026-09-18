<?php

namespace Hashtopolis\inc\apiv2\util;

use PHPUnit\Framework\TestCase;

use Hashtopolis\inc\apiv2\error\HttpForbidden;

use Slim\Factory\AppFactory;

class DummyRequest {
  private string $http_origin;

  public function setHeaderLine($headerLine): void {
    $this->http_origin = $headerLine;
  }
  
  public function getHeaderLine($headerLine): string {
    return $this->http_origin;
  }
}

final class CorsHackMiddlewareTest extends TestCase {
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
