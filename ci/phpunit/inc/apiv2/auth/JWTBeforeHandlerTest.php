<?php

namespace Hashtopolis\inc\apiv2\auth;

use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\defines\DTokenType;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

require_once(dirname(__FILE__) . '/../../../../../src/inc/startup/include.php');

/**
 * Tests the token-type gate.
 *
 * Every token this deployment issues is signed with the same key, so a valid signature proves only
 * that we minted it. The `type` claim is what keeps a credential minted for one purpose from being
 * spent on another.
 */
final class JWTBeforeHandlerTest extends TestCase {
  /**
   * @param array<string, mixed> $decoded
   */
  private function handle(array $decoded) {
    $request = (new ServerRequestFactory())->createServerRequest('GET', 'http://localhost/api/v2/ui/users');

    return (new JWTBeforeHandler())($request, ['decoded' => $decoded, 'token' => 'irrelevant']);
  }

  private function accessClaims(): array {
    return ['userId' => 1, 'scope' => 'ALL', 'aud' => 'user_hashtopolis', 'type' => DTokenType::ACCESS];
  }

  public function testAnAccessTokenIsAccepted(): void {
    $request = $this->handle($this->accessClaims());

    $this->assertSame(1, $request->getAttribute('userId'));
    $this->assertSame('ALL', $request->getAttribute('scope'));
  }

  /**
   * The gate itself: a credential minted for renewing a session must not authorise a resource request.
   */
  public function testARefreshTokenIsRefused(): void {
    $claims = $this->accessClaims();
    $claims['type'] = DTokenType::REFRESH;

    $this->expectException(HttpForbidden::class);
    $this->handle($claims);
  }

  public function testAnUnknownTypeIsRefused(): void {
    $claims = $this->accessClaims();
    $claims['type'] = 'something-else';

    $this->expectException(HttpForbidden::class);
    $this->handle($claims);
  }

  /**
   * Tokens minted before the claim existed carry no type and stay usable until they expire, so
   * deploying this does not log everyone out.
   */
  public function testATokenWithoutATypeClaimIsStillAccepted(): void {
    $claims = $this->accessClaims();
    unset($claims['type']);

    $this->assertSame(1, $this->handle($claims)->getAttribute('userId'));
  }

  /**
   * The audience is carried through for permission checks, and defaults when absent.
   */
  public function testAudienceIsExposedToTheRequest(): void {
    $this->assertSame('user_hashtopolis', $this->handle($this->accessClaims())->getAttribute('aud'));

    $claims = $this->accessClaims();
    unset($claims['aud']);
    $this->assertSame('user_hashtopolis', $this->handle($claims)->getAttribute('aud'));
  }
}
