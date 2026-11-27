<?php

namespace Hashtopolis\inc\apiv2\auth;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\apiv2\model\ApiTokenAPI;
use Hashtopolis\inc\defines\DTokenType;
use Hashtopolis\TestBase;
use Slim\Psr7\Factory\ServerRequestFactory;

require_once(dirname(__FILE__) . '/../../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../../src/inc/startup/include.php');

/**
 * Tests the token-type gate and how the identity of the request is established.
 *
 * Every token this deployment issues is signed with the same key, so a valid signature proves only
 * that we minted it. The `type` claim is what keeps a credential minted for one purpose from being
 * spent on another.
 *
 * A login token carries the user's fields, so the user is not read from the database on every request;
 * API tokens and login tokens minted before those claims existed are resolved against the database.
 */
final class JWTBeforeHandlerTest extends TestBase {
  /** An id no user has, so any attempt to read this user from the database fails loudly */
  private const UNKNOWN_USER_ID = 2147483000;

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
  
  /**
   * @param array<string, mixed> $decoded
   */
  private function identity(array $decoded): RequestIdentity {
    $identity = $this->handle($decoded)->getAttribute('identity');
    $this->assertInstanceOf(RequestIdentity::class, $identity);
    return $identity;
  }
  
  /**
   * The claims a login token is minted with since the user's fields moved into it.
   */
  private function userClaims(int $userId, string $scope = '{"viewTaskAccess":true}'): array {
    return [
      'userId' => $userId,
      'username' => 'from_token',
      'rightGroupId' => 42,
      'scope' => $scope,
      'aud' => 'user_hashtopolis',
      'type' => DTokenType::ACCESS,
    ];
  }
  
  private function apiTokenClaims(User $user, int $jti): array {
    return [
      'userId' => $user->getId(),
      'jti' => $jti,
      'scope' => json_encode([Hashlist::PERM_READ => true]),
      'aud' => ApiTokenAPI::API_AUD,
      'type' => DTokenType::ACCESS,
    ];
  }
  
  /**
   * The point of the claims: the user does not exist in the database, so the identity can only have
   * come from the token.
   */
  public function testALoginTokenWithClaimsIsResolvedWithoutTheDatabase(): void {
    $identity = $this->identity($this->userClaims(self::UNKNOWN_USER_ID));
    
    $this->assertSame(self::UNKNOWN_USER_ID, $identity->user->getId());
    $this->assertSame('from_token', $identity->user->getUsername());
    $this->assertSame(42, $identity->user->getRightGroupId());
    $this->assertSame(1, $identity->user->getIsValid());
    $this->assertSame('user_hashtopolis', $identity->aud);
    $this->assertFalse($identity->isAdmin);
  }
  
  /**
   * Memberships are not taken from the token, so a user added to a group sees it with the token in hand.
   */
  public function testAccessGroupsAreReadFromTheDatabaseWhenNeeded(): void {
    $user = $this->createUser('jwt_groups');
    $identity = $this->identity($this->userClaims($user->getId()));
    
    $group = $this->createAccessGroup('jwt_groups');
    $this->createAccessGroupUser($user, $group);
    
    $this->assertContains($group->getId(), $identity->accessGroupIds());
  }
  
  public function testALoginTokenWithTheAllScopeIsAnAdmin(): void {
    $this->assertTrue($this->identity($this->userClaims(self::UNKNOWN_USER_ID, 'ALL'))->isAdmin);
  }
  
  /**
   * Tokens minted before the claims existed keep working until they expire: the user and the access
   * groups are read from the database, as before.
   */
  public function testALoginTokenWithoutClaimsFallsBackToTheDatabase(): void {
    $user = $this->createUser('jwt_legacy');
    $group = $this->createAccessGroup('jwt_legacy');
    $this->createAccessGroupUser($user, $group);
    
    $identity = $this->identity(['userId' => $user->getId(), 'scope' => '[]', 'aud' => 'user_hashtopolis']);
    
    $this->assertSame($user->getUsername(), $identity->user->getUsername());
    $this->assertSame($user->getEmail(), $identity->user->getEmail());
    $this->assertContains($group->getId(), $identity->accessGroupIds());
  }
  
  public function testALoginTokenWithoutClaimsOfADisabledUserIsRefused(): void {
    $user = $this->createUser('jwt_disabled');
    Factory::getUserFactory()->set($user, User::IS_VALID, 0);
    
    $this->expectException(HttpForbidden::class);
    $this->handle(['userId' => $user->getId(), 'scope' => '[]', 'aud' => 'user_hashtopolis']);
  }
  
  /**
   * API tokens live for months, so whatever user fields they might carry are ignored and the user is
   * always read from the database.
   */
  public function testAnApiTokenIsResolvedAgainstTheDatabase(): void {
    $user = $this->createUser('jwt_api');
    $group = $this->createAccessGroup('jwt_api');
    $this->createAccessGroupUser($user, $group);
    $key = $this->createJwtApiKey($user);
    
    $claims = $this->apiTokenClaims($user, $key->getId()) + [
        'username' => 'stale_name',
        'rightGroupId' => 42,
      ];
    $identity = $this->identity($claims);
    
    $this->assertSame($user->getUsername(), $identity->user->getUsername());
    $this->assertSame($user->getRightGroupId(), $identity->user->getRightGroupId());
    $this->assertContains($group->getId(), $identity->accessGroupIds());
    $this->assertSame(ApiTokenAPI::API_AUD, $identity->aud);
    // The user's right group is not 'ALL', whatever the token's scope says
    $this->assertFalse($identity->isAdmin);
  }
  
  /**
   * An API token's scope is a permission dict of its own, so whether its owner is an administrator
   * comes from the owner's right group.
   */
  public function testAnApiTokenOfAnAdministratorIsAnAdmin(): void {
    $user = $this->createUser('jwt_api_admin');
    $group = Factory::getRightGroupFactory()->get($user->getRightGroupId());
    Factory::getRightGroupFactory()->set($group, RightGroup::PERMISSIONS, 'ALL');
    $key = $this->createJwtApiKey($user);
    
    $this->assertTrue($this->identity($this->apiTokenClaims($user, $key->getId()))->isAdmin);
  }
  
  public function testAnApiTokenOfADisabledUserIsRefused(): void {
    $user = $this->createUser('jwt_api_disabled');
    $key = $this->createJwtApiKey($user);
    Factory::getUserFactory()->set($user, User::IS_VALID, 0);
    
    $this->expectException(HttpForbidden::class);
    $this->handle($this->apiTokenClaims($user, $key->getId()));
  }
}
