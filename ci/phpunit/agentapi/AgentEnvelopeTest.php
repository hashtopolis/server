<?php

namespace Hashtopolis\agentapi;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PResponse;
use Hashtopolis\inc\agent\PResponseErrorMessage;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\agentapi\common\AgentEnvelope;
use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__) . '/../TestBase.php');

/**
 * Unit tests for {@see AgentEnvelope} — verifies the wire-level shape of the
 * success and error envelopes produced by the agent API.
 */
final class AgentEnvelopeTest extends TestCase {

  /**
   * A success envelope contains the action string and response='SUCCESS'.
   */
  public function testSuccessEnvelopeHasActionAndResponse(): void {
    $envelope = AgentEnvelope::success(PActions::TEST_CONNECTION);
    $this->assertEquals(PActions::TEST_CONNECTION, $envelope[PResponse::ACTION]);
    $this->assertEquals(PValues::SUCCESS, $envelope[PResponse::RESPONSE]);
  }

  /**
   * A success envelope with no extra fields has exactly 2 keys.
   */
  public function testSuccessEnvelopeNoExtraFields(): void {
    $envelope = AgentEnvelope::success(PActions::TEST_CONNECTION);
    $this->assertCount(2, $envelope);
  }

  /**
   * A success envelope with extra fields includes them alongside the base keys.
   */
  public function testSuccessEnvelopeWithExtraFields(): void {
    $envelope = AgentEnvelope::success(PActions::REGISTER, ['token' => 'abc123']);
    $this->assertEquals(PActions::REGISTER, $envelope[PResponse::ACTION]);
    $this->assertEquals(PValues::SUCCESS, $envelope[PResponse::RESPONSE]);
    $this->assertEquals('abc123', $envelope['token']);
    $this->assertCount(3, $envelope);
  }

  /**
   * An error envelope has action, response='ERROR', and a message.
   */
  public function testErrorEnvelopeShape(): void {
    $envelope = AgentEnvelope::error(PActions::LOGIN, 'Invalid token!');
    $this->assertEquals(PActions::LOGIN, $envelope[PResponse::ACTION]);
    $this->assertEquals(PValues::ERROR, $envelope[PResponse::RESPONSE]);
    $this->assertEquals('Invalid token!', $envelope[PResponseErrorMessage::MESSAGE]);
    $this->assertCount(3, $envelope);
  }

  /**
   * The 'INV' action string is used for unknown/missing actions.
   */
  public function testErrorEnvelopeWithInvAction(): void {
    $envelope = AgentEnvelope::error('INV', 'Invalid query!');
    $this->assertEquals('INV', $envelope[PResponse::ACTION]);
    $this->assertEquals(PValues::ERROR, $envelope[PResponse::RESPONSE]);
    $this->assertEquals('Invalid query!', $envelope[PResponseErrorMessage::MESSAGE]);
  }
}
