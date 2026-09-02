<?php

namespace Hashtopolis\agentapi;

use Hashtopolis\inc\agent\PResponse;
use Hashtopolis\inc\agent\PResponseErrorMessage;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\agentapi\error\AgentErrorHandler;
use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__) . '/../TestBase.php');

/**
 * Unit tests for {@see AgentErrorHandler} — verifies the PSR-7 responses
 * produced for unknown/missing actions (INV) and per-action errors.
 */
final class AgentErrorHandlerTest extends TestCase {

  /**
   * invResponse() returns a 200 PSR-7 response (the agent API always uses HTTP 200).
   */
  public function testInvResponseReturnsHttp200(): void {
    $response = AgentErrorHandler::invResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
   * invResponse() sets Content-Type to application/json.
   */
  public function testInvResponseContentTypeIsJson(): void {
    $response = AgentErrorHandler::invResponse();
    $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
  }

  /**
   * invResponse() with no arguments uses the default message 'Invalid query!'.
   */
  public function testInvResponseDefaultMessage(): void {
    $response = AgentErrorHandler::invResponse();
    $body = json_decode((string)$response->getBody(), true);
    $this->assertEquals('INV', $body[PResponse::ACTION]);
    $this->assertEquals(PValues::ERROR, $body[PResponse::RESPONSE]);
    $this->assertEquals('Invalid query!', $body[PResponseErrorMessage::MESSAGE]);
  }

  /**
   * invResponse() with a custom message includes it in the envelope.
   */
  public function testInvResponseCustomMessage(): void {
    $response = AgentErrorHandler::invResponse('Something went wrong');
    $body = json_decode((string)$response->getBody(), true);
    $this->assertEquals('INV', $body[PResponse::ACTION]);
    $this->assertEquals(PValues::ERROR, $body[PResponse::RESPONSE]);
    $this->assertEquals('Something went wrong', $body[PResponseErrorMessage::MESSAGE]);
  }

  /**
   * errorResponse() returns a 200 PSR-7 response.
   */
  public function testErrorResponseReturnsHttp200(): void {
    $response = AgentErrorHandler::errorResponse('login', 'Invalid token!');
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
   * errorResponse() sets Content-Type to application/json.
   */
  public function testErrorResponseContentTypeIsJson(): void {
    $response = AgentErrorHandler::errorResponse('login', 'Invalid token!');
    $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
  }

  /**
   * errorResponse() echoes the action string and includes the message.
   */
  public function testErrorResponseShape(): void {
    $response = AgentErrorHandler::errorResponse('login', 'Invalid token!');
    $body = json_decode((string)$response->getBody(), true);
    $this->assertEquals('login', $body[PResponse::ACTION]);
    $this->assertEquals(PValues::ERROR, $body[PResponse::RESPONSE]);
    $this->assertEquals('Invalid token!', $body[PResponseErrorMessage::MESSAGE]);
    $this->assertCount(3, $body);
  }

  /**
   * The INV response body is valid JSON that decodes to the expected envelope.
   */
  public function testInvResponseBodyIsValidJson(): void {
    $response = AgentErrorHandler::invResponse();
    $raw = (string)$response->getBody();
    $decoded = json_decode($raw, true);
    $this->assertNotNull($decoded, "Response body should be valid JSON: $raw");
    $this->assertIsArray($decoded);
  }

  /**
   * The error response body is valid JSON that decodes to the expected envelope.
   */
  public function testErrorResponseBodyIsValidJson(): void {
    $response = AgentErrorHandler::errorResponse('getTask', 'Invalid token!');
    $raw = (string)$response->getBody();
    $decoded = json_decode($raw, true);
    $this->assertNotNull($decoded, "Response body should be valid JSON: $raw");
    $this->assertIsArray($decoded);
  }
}
