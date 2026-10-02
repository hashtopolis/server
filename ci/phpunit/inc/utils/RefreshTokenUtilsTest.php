<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Config;
use Hashtopolis\dba\models\RefreshToken;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\UpdateSet;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\apiv2\error\HttpUnauthorized;
use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class RefreshTokenUtilsTest extends TestBase {
  private User $user;
  
  #[Override]
  protected function setUp(): void {
    parent::setUp();
    
    $this->user = $this->createUser('rt_user');
  }
  
  /**
   * Looks a token up the way the production code does, so the test never assumes a row id.
   */
  private function findToken(string $plain): ?RefreshToken {
    $qF = new QueryFilter(RefreshToken::TOKEN_HASH, RefreshTokenUtils::hashToken($plain), "=");
    return Factory::getRefreshTokenFactory()->filter([Factory::FILTER => $qF], true);
  }
  
  /**
   * @return RefreshToken[]
   */
  private function tokensOfUser(): array {
    $qF = new QueryFilter(RefreshToken::USER_ID, $this->user->getId(), "=");
    return Factory::getRefreshTokenFactory()->filter([Factory::FILTER => $qF]);
  }
  
  /**
   * Sets maxSessionLength for the duration of a callback and puts it back afterwards.
   *
   * @throws Exception
   */
  private function withSessionLength(string $hours, callable $body): void {
    $config = ConfigUtils::get(DConfig::MAX_SESSION_LENGTH);
    $original = $config->getValue();

    try {
      Factory::getConfigFactory()->set($config, Config::VALUE, $hours);
      SConfig::reload();
      $body();
    } finally {
      Factory::getConfigFactory()->set(ConfigUtils::get(DConfig::MAX_SESSION_LENGTH), Config::VALUE, $original);
      SConfig::reload();
    }
  }

  /**
   * How long a login lasts is a runtime setting, so an administrator can change it from the interface
   * without touching the deployment.
   *
   * @throws Exception
   */
  public function testLifetimeFollowsTheMaxSessionLengthSetting(): void {
    $this->withSessionLength('6', function (): void {
      $this->assertSame(6 * 3600, RefreshTokenUtils::lifetimeSeconds());

      $plain = RefreshTokenUtils::issue($this->user->getId());
      $token = $this->findToken($plain);
      $this->assertEqualsWithDelta(time() + 6 * 3600, $token->getEndValid(), 5);
    });
  }

  /**
   * A value that would expire a token the moment it is handed out would lock everyone out of the very
   * setting needed to fix it, so an unusable one falls back instead.
   *
   * @throws Exception
   */
  public function testAnUnusableSessionLengthFallsBack(): void {
    $this->withSessionLength('0', function (): void {
      $this->assertSame(RefreshTokenUtils::FALLBACK_LIFETIME_HOURS * 3600, RefreshTokenUtils::lifetimeSeconds());
    });
  }

  public function testIssueStoresOnlyTheHashOfTheToken(): void {
    $plain = RefreshTokenUtils::issue($this->user->getId());
    
    $token = $this->findToken($plain);
    $this->assertInstanceOf(RefreshToken::class, $token);
    $this->assertNotSame($plain, $token->getTokenHash());
    $this->assertSame(hash('sha256', $plain), $token->getTokenHash());
    $this->assertNull($token->getUsedAt());
    $this->assertEquals(0, $token->getIsRevoked());
  }
  
  public function testIssueStartsANewFamilyPerLogin(): void {
    $first = $this->findToken(RefreshTokenUtils::issue($this->user->getId()));
    $second = $this->findToken(RefreshTokenUtils::issue($this->user->getId()));
    
    $this->assertNotSame($first->getFamilyId(), $second->getFamilyId());
  }
  
  public function testRotateConsumesTheTokenAndKeepsTheFamily(): void {
    $plain = RefreshTokenUtils::issue($this->user->getId());
    $original = $this->findToken($plain);
    
    $rotated = RefreshTokenUtils::rotate($plain);
    
    $this->assertSame($this->user->getId(), $rotated['user']->getId());
    $this->assertNotSame($plain, $rotated['token']);
    
    $consumed = $this->findToken($plain);
    $this->assertNotNull($consumed->getUsedAt());
    
    $successor = $this->findToken($rotated['token']);
    $this->assertNull($successor->getUsedAt());
    $this->assertSame($original->getFamilyId(), $successor->getFamilyId());
  }
  
  public function testRotateRejectsAnUnknownToken(): void {
    $this->expectException(HttpUnauthorized::class);
    RefreshTokenUtils::rotate('not-a-token');
  }
  
  public function testRotateRejectsAnExpiredToken(): void {
    $plain = RefreshTokenUtils::issue($this->user->getId());
    Factory::getRefreshTokenFactory()->set($this->findToken($plain), RefreshToken::END_VALID, time() - 1);
    
    $this->expectException(HttpUnauthorized::class);
    RefreshTokenUtils::rotate($plain);
  }
  
  public function testReplayRevokesTheWholeFamily(): void {
    $plain = RefreshTokenUtils::issue($this->user->getId());
    $rotated = RefreshTokenUtils::rotate($plain);
    
    try {
      RefreshTokenUtils::rotate($plain);
      $this->fail('Replaying a consumed refresh token should not hand out a new one');
    } catch (HttpUnauthorized $e) {
      $this->assertStringContainsString('already been used', $e->getMessage());
    }
    
    // The successor was never touched by the attacker, but the session is gone all the same
    $this->assertEquals(1, $this->findToken($rotated['token'])->getIsRevoked());
    
    $this->expectException(HttpUnauthorized::class);
    RefreshTokenUtils::rotate($rotated['token']);
  }
  
  public function testConsumingATokenIsAnAtomicTestAndSet(): void {
    /* The mechanism replay detection rests on: consuming a token has to be the same statement that
       checks it is unconsumed, so that concurrent requests cannot both find it unused. Running the
       claim twice stands in for two requests arriving together; only one may report having made the
       transition. */
    $plain = RefreshTokenUtils::issue($this->user->getId());
    $token = $this->findToken($plain);
    
    $claim = fn(): bool => Factory::getRefreshTokenFactory()->compareAndSet(
      $token,
      [
        new QueryFilter(RefreshToken::USED_AT, null, "="),
        new QueryFilter(RefreshToken::IS_REVOKED, 0, "=")
      ],
      [new UpdateSet(RefreshToken::USED_AT, time())]
    );
    
    $this->assertTrue($claim(), 'the first caller should win the token');
    $this->assertFalse($claim(), 'a second caller must not also win the same token');
  }
  
  public function testLosingTheClaimLeavesTheWinnersTimestampAlone(): void {
    $plain = RefreshTokenUtils::issue($this->user->getId());
    $token = $this->findToken($plain);
    
    // Stand in for a request that already consumed the token a moment ago
    $consumedAt = time() - 1;
    Factory::getRefreshTokenFactory()->set($token, RefreshToken::USED_AT, $consumedAt);
    
    try {
      RefreshTokenUtils::rotate($plain);
      $this->fail('A consumed token should not be exchangeable');
    } catch (HttpUnauthorized) {
      // The conditional update has to leave a row it did not match untouched, so the record of when
      // the token was really consumed survives for anyone investigating the replay
      $this->assertSame($consumedAt, (int)$this->findToken($plain)->getUsedAt());
    }
  }
  
  public function testARevokedTokenCannotBeClaimed(): void {
    $plain = RefreshTokenUtils::issue($this->user->getId());
    RefreshTokenUtils::revoke($plain);
    
    try {
      RefreshTokenUtils::rotate($plain);
      $this->fail('A revoked refresh token should not be exchangeable');
    } catch (HttpUnauthorized $e) {
      $this->assertNull($this->findToken($plain)->getUsedAt(), 'a revoked token should not be consumed');
    }
  }
  
  /**
   * A token is single use with no window of forgiveness. An immediate second exchange is exactly what
   * a copied cookie looks like, and it cannot be told apart from a client that asked twice, so it is
   * treated as the leak it might be. Clients avoid this by serialising their own refreshes.
   */
  public function testASecondExchangeIsAReplayEvenImmediately(): void {
    $plain = RefreshTokenUtils::issue($this->user->getId());
    
    $first = RefreshTokenUtils::rotate($plain);
    
    try {
      RefreshTokenUtils::rotate($plain);
      $this->fail('A refresh token must not be exchangeable twice, however quickly the second use arrives');
    } catch (HttpUnauthorized $e) {
      $this->assertStringContainsString('already been used', $e->getMessage());
    }
    
    // ... and the successor the first exchange handed out goes with it
    $this->assertEquals(1, $this->findToken($first['token'])->getIsRevoked());
  }
  
  public function testRotateRefusesADeactivatedUser(): void {
    $plain = RefreshTokenUtils::issue($this->user->getId());
    Factory::getUserFactory()->set($this->user, User::IS_VALID, 0);
    
    try {
      RefreshTokenUtils::rotate($plain);
      $this->fail('A deactivated user should not be able to refresh');
    } catch (HttpForbidden $e) {
      $this->assertEquals(1, $this->findToken($plain)->getIsRevoked());
    } finally {
      Factory::getUserFactory()->set($this->user, User::IS_VALID, 1);
    }
  }
  
  public function testRevokeEndsOnlyItsOwnSession(): void {
    $sessionA = RefreshTokenUtils::issue($this->user->getId());
    $sessionB = RefreshTokenUtils::issue($this->user->getId());
    
    RefreshTokenUtils::revoke($sessionA);
    
    $this->assertEquals(1, $this->findToken($sessionA)->getIsRevoked());
    $this->assertEquals(0, $this->findToken($sessionB)->getIsRevoked());
  }
  
  public function testRevokeIgnoresAnUnknownToken(): void {
    RefreshTokenUtils::revoke('not-a-token');
    $this->assertCount(0, $this->tokensOfUser());
  }
  
  public function testRevokeAllForUserEndsEverySession(): void {
    $sessionA = RefreshTokenUtils::issue($this->user->getId());
    $sessionB = RefreshTokenUtils::issue($this->user->getId());
    
    RefreshTokenUtils::revokeAllForUser($this->user->getId());
    
    $this->assertEquals(1, $this->findToken($sessionA)->getIsRevoked());
    $this->assertEquals(1, $this->findToken($sessionB)->getIsRevoked());
  }
  
  public function testPurgeExpiredKeepsTokensWhichCanStillBeExchanged(): void {
    $live = RefreshTokenUtils::issue($this->user->getId());
    $stale = RefreshTokenUtils::issue($this->user->getId());
    Factory::getRefreshTokenFactory()->set($this->findToken($stale), RefreshToken::END_VALID, time() - 1);
    
    RefreshTokenUtils::purgeExpired($this->user->getId());
    
    $this->assertNull($this->findToken($stale));
    $this->assertNotNull($this->findToken($live));
  }
  
  public function testDeleteAllForUserLeavesNothingBehind(): void {
    RefreshTokenUtils::issue($this->user->getId());
    RefreshTokenUtils::issue($this->user->getId());
    
    RefreshTokenUtils::deleteAllForUser($this->user->getId());
    
    $this->assertCount(0, $this->tokensOfUser());
  }
  
  public function testDeletingAUserRemovesItsRefreshTokens(): void {
    // Deliberately not registered for teardown: the test deletes the user itself
    $name = 'rt_victim_' . uniqid();
    $victim = UserUtils::createUser($name, $name . '@example.com', $this->createRightGroup()->getId(), $this->adminUser);
    $plain = RefreshTokenUtils::issue($victim->getId());
    
    UserUtils::deleteUser($victim->getId(), $this->adminUser);
    
    $this->assertNull($this->findToken($plain));
  }
  
  public function testDisablingAUserRevokesItsRefreshTokens(): void {
    $plain = RefreshTokenUtils::issue($this->user->getId());
    
    UserUtils::disableUser($this->user->getId(), $this->adminUser);
    
    $this->assertEquals(1, $this->findToken($plain)->getIsRevoked());
    UserUtils::enableUser($this->user->getId());
  }
}
