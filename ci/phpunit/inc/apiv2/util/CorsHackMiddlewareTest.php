<?php

namespace Tests\inc\apiv2\util;

use PHPUnit\Framework\TestCase;

use Exception;
use Hashtopolis\inc\apiv2\error\HttpForbidden;

use Slim\Factory\AppFactory;
use Slim\Psr7\Request;
use Hashtopolis\inc\apiv2\util\CorsHackMiddleware;

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

    //Test the same but with https:
    $request->setHeaderLine("https://127.0.0.1:4200");
    CorsHackMiddleware::CheckCORS($request, $response);

    $request->setHeaderLine("https://localhost:4200");
    CorsHackMiddleware::CheckCORS($request, $response);

    $request->setHeaderLine("https://[::1]:4200");
    CorsHackMiddleware::CheckCORS($request, $response);

    $request->setHeaderLine("https://127.0.0.1:8080");
    CorsHackMiddleware::CheckCORS($request, $response);

    $request->setHeaderLine("https://localhost:8080");
    CorsHackMiddleware::CheckCORS($request, $response);

    $request->setHeaderLine("https://[::1]:8080");
    CorsHackMiddleware::CheckCORS($request, $response);
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
    $this->expectException(CorsHackMiddleware::CheckCORS($request, $response));
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
    $this->expectException(CorsHackMiddleware::CheckCORS($request, $response));
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
    $this->expectException(CorsHackMiddleware::CheckCORS($request, $response));
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
    $this->expectException(CorsHackMiddleware::CheckCORS($request, $response));
  }
}
