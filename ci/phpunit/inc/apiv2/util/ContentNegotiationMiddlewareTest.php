<?php

namespace Hashtopolis\inc\apiv2\util;

use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;

/**
 * Content negotiation of the JSON:API media type as JSON:API 1.1 requires it
 * from a server: an unusable Content-Type is answered with 415, an Accept header
 * that only asks for unusable instances with 406, and everything else is passed
 * on untouched.
 */
final class ContentNegotiationMiddlewareTest extends TestCase {
  private const ATOMIC = AbstractModelAPI::ATOMIC_MEDIA_TYPE;

  private function handle(array $headers): ResponseInterface {
    $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://localhost/api/v2/ui/agents');
    foreach ($headers as $name => $value) {
      $request = $request->withHeader($name, $value);
    }

    $handler = new class implements RequestHandlerInterface {
      public function handle(ServerRequestInterface $request): ResponseInterface {
        return (new Response())->withStatus(299);
      }
    };

    return (new ContentNegotiationMiddleware())->process($request, $handler);
  }

  /** Status 299 means the request reached the route behind the middleware. */
  private function assertPassedOn(array $headers, string $message): void {
    $this->assertSame(299, $this->handle($headers)->getStatusCode(), $message);
  }

  public function testPassesOnUsableMediaTypes(): void {
    $this->assertPassedOn([], 'a request without either header');
    $this->assertPassedOn(['Content-Type' => 'application/vnd.api+json'], 'the plain JSON:API media type');
    $this->assertPassedOn(['Content-Type' => 'application/json; charset=utf-8'], 'another media type with parameters');
    $this->assertPassedOn(['Content-Type' => 'application/offset+octet-stream'], 'a TUS upload');
    $this->assertPassedOn(['Content-Type' => self::ATOMIC], 'the atomic operations extension');
    $this->assertPassedOn(['Accept' => '*/*'], 'accepting anything');
    $this->assertPassedOn(['Accept' => 'application/json, application/vnd.api+json'], 'accepting the media type');
    $this->assertPassedOn(['Accept' => 'application/vnd.api+json;q=0.9'], 'a quality weight is no media type parameter');
    $this->assertPassedOn(['Accept' => 'application/vnd.api+json;profile="https://example.com/p"'], 'a requested profile');
    $this->assertPassedOn(['Accept' => self::ATOMIC], 'accepting the atomic operations extension');
    /* One usable instance is enough, even next to unusable ones */
    $this->assertPassedOn(
      ['Accept' => 'application/vnd.api+json;charset=utf-8, application/vnd.api+json'],
      'one usable instance among unusable ones'
    );
  }

  public function testAnswers415OnAnUnusableContentType(): void {
    $unusable = [
      'application/vnd.api+json; charset=utf-8' => 'a parameter the specification does not define',
      'application/vnd.api+json;ext="https://example.com/ext/unknown"' => 'an extension that is not implemented',
      'application/vnd.api+json;ext="https://jsonapi.org/ext/atomic https://example.com/ext/other"' => 'one unsupported extension among supported ones',
    ];

    foreach ($unusable as $contentType => $case) {
      $response = $this->handle(['Content-Type' => $contentType]);
      $this->assertSame(415, $response->getStatusCode(), $case);
      $this->assertSame('application/vnd.api+json', $response->getHeaderLine('Content-type'), $case);
    }
  }

  public function testAnswers406WhenNoAcceptedInstanceCanBeServed(): void {
    $unusable = [
      'application/vnd.api+json;charset=utf-8' => 'the only instance carries an undefined parameter',
      'application/vnd.api+json;ext="https://example.com/ext/unknown"' => 'the only instance requires an unsupported extension',
      'application/vnd.api+json;charset=utf-8, application/vnd.api+json;ext="https://example.com/ext/unknown"' => 'no instance is usable',
    ];

    foreach ($unusable as $accept => $case) {
      $response = $this->handle(['Accept' => $accept]);
      $this->assertSame(406, $response->getStatusCode(), $case);
      $this->assertSame('application/vnd.api+json', $response->getHeaderLine('Content-type'), $case);
    }
  }

  /** The Content-Type is checked first: it decides whether the body can be read at all. */
  public function testReportsTheContentTypeBeforeTheAcceptHeader(): void {
    $response = $this->handle([
      'Content-Type' => 'application/vnd.api+json;charset=utf-8',
      'Accept' => 'application/vnd.api+json;charset=utf-8'
    ]);

    $this->assertSame(415, $response->getStatusCode());
  }
}
