<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\RefreshToken;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
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
    
    // Age the consumed token past the window in which a second exchange counts as a client-side race
    $consumed = $this->findToken($plain);
    Factory::getRefreshTokenFactory()->set(
      $consumed,
      RefreshToken::USED_AT,
      time() - RefreshTokenUtils::REPLAY_GRACE_SECONDS - 1
    );
    
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
  
  public function testConcurrentRotationWithinTheGraceWindowIsNotTreatedAsReplay(): void {
    $plain = RefreshTokenUtils::issue($this->user->getId());
    
    $first = RefreshTokenUtils::rotate($plain);
    $second = RefreshTokenUtils::rotate($plain);
    
    $this->assertNotSame($first['token'], $second['token']);
    $this->assertEquals(0, $this->findToken($first['token'])->getIsRevoked());
    $this->assertEquals(0, $this->findToken($second['token'])->getIsRevoked());
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
