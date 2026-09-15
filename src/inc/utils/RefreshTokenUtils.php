<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\RefreshToken;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\UpdateSet;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\apiv2\error\HttpUnauthorized;
use Hashtopolis\inc\defines\DLogEntry;
use Hashtopolis\inc\defines\DLogEntryIssuer;
use Hashtopolis\inc\StartupConfig;
use Hashtopolis\inc\Util;
use Random\RandomException;

/**
 * Issuing, validating and rotating refresh tokens.
 *
 * A refresh token is a high-entropy random string handed to the client after a successful login. It
 * carries no claims of its own; its only purpose is to be exchanged at /api/v2/auth/refresh for a
 * fresh short-lived access token (JWT). Only the SHA-256 hash of the string is stored, so the
 * database never holds a usable credential.
 *
 * Tokens are single use. Every successful exchange marks the presented token as consumed and issues
 * a replacement carrying the same familyId, so one family represents one login session across all of
 * its rotations. Presenting a token that was already consumed means two parties hold the same token,
 * which is only possible if it leaked, so the entire family is revoked and the user has to log in
 * again.
 *
 * The one exception is REPLAY_GRACE_SECONDS: browsers routinely fire several requests in parallel
 * and can therefore exchange the same token twice within the same moment. Re-using a token that was
 * consumed less than that many seconds ago is treated as such a race and simply issues another
 * token in the family instead of tearing the session down.
 */
class RefreshTokenUtils {
  /** Number of random bytes in a token; the token string is this hex-encoded, so twice as long. */
  const TOKEN_BYTES = 32;
  
  /**
   * Window in which re-using a consumed token counts as a client-side race rather than a replay.
   * Keep this as small as tolerable: within it, a leaked token is still usable.
   */
  const REPLAY_GRACE_SECONDS = 10;
  
  /**
   * @param string $plain the token string as presented by the client
   * @return string hex encoded SHA-256 digest, as stored in the database
   */
  public static function hashToken(string $plain): string {
    return hash("sha256", $plain);
  }
  
  /**
   * Issues a new refresh token for a user.
   *
   * @param int $userId
   * @param string|null $familyId continues an existing session when given, starts a new one otherwise
   * @return string the token string; this is the only moment it exists in plaintext
   * @throws RandomException
   * @throws Exception
   */
  public static function issue(int $userId, ?string $familyId = null): string {
    self::purgeExpired($userId);
    
    $plain = bin2hex(random_bytes(self::TOKEN_BYTES));
    $now = time();
    $token = new RefreshToken(
      null,
      $userId,
      self::hashToken($plain),
      $familyId ?? bin2hex(random_bytes(16)),
      $now,
      $now + StartupConfig::getInstance()->getRefreshTokenLifetime(),
      null,
      0
    );
    Factory::getRefreshTokenFactory()->save($token);
    
    return $plain;
  }
  
  /**
   * Exchanges a refresh token for its successor.
   *
   * The presented token is consumed, so it cannot be exchanged a second time. The caller is expected
   * to mint a new access token for the returned user and hand the returned token string back to the
   * client.
   *
   * @param string $plain the token string as presented by the client
   * @return array{user: User, token: string} the owning user and the replacement token string
   * @throws HttpUnauthorized when the token is unknown, expired, revoked or replayed
   * @throws HttpForbidden when the owning user has been deactivated
   * @throws Exception
   */
  public static function rotate(string $plain): array {
    $token = self::findByPlain($plain);
    if ($token === null) {
      throw new HttpUnauthorized("Refresh token is not valid");
    }
    
    if ($token->getIsRevoked() == 1) {
      throw new HttpUnauthorized("Refresh token has been revoked");
    }
    
    $now = time();
    if ($token->getUsedAt() !== null && $token->getUsedAt() < $now - self::REPLAY_GRACE_SECONDS) {
      /* The token was already exchanged a while ago, so somebody else is holding a copy of it. There
         is no way to tell the legitimate holder from the attacker, so the whole session goes. */
      self::revokeFamily($token->getFamilyId());
      Util::createLogEntry(
        DLogEntryIssuer::USER,
        (string)$token->getUserId(),
        DLogEntry::WARN,
        "Refresh token replay detected, all sessions of this login have been revoked!"
      );
      throw new HttpUnauthorized("Refresh token has already been used");
    }
    
    if ($token->getEndValid() < $now) {
      throw new HttpUnauthorized("Refresh token has expired");
    }
    
    $user = Factory::getUserFactory()->get($token->getUserId());
    if ($user === null) {
      self::revokeFamily($token->getFamilyId());
      throw new HttpUnauthorized("Refresh token is not valid");
    }
    if ($user->getIsValid() != 1) {
      self::revokeFamily($token->getFamilyId());
      throw new HttpForbidden("Cannot log in. Please contact your administrator for further information");
    }
    
    if ($token->getUsedAt() === null) {
      Factory::getRefreshTokenFactory()->set($token, RefreshToken::USED_AT, $now);
    }
    
    return [
      "user" => $user,
      "token" => self::issue($token->getUserId(), $token->getFamilyId()),
    ];
  }
  
  /**
   * Ends the session a token belongs to. Used on logout; unknown tokens are ignored so that logging
   * out twice is not an error.
   *
   * @param string $plain the token string as presented by the client
   * @throws Exception
   */
  public static function revoke(string $plain): void {
    $token = self::findByPlain($plain);
    if ($token === null) {
      return;
    }
    self::revokeFamily($token->getFamilyId());
  }
  
  /**
   * Revokes every token belonging to one login session, current and past rotations alike.
   *
   * @param string $familyId
   * @throws Exception
   */
  public static function revokeFamily(string $familyId): void {
    Factory::getRefreshTokenFactory()->massUpdate([
      Factory::FILTER => [new QueryFilter(RefreshToken::FAMILY_ID, $familyId, "=")],
      Factory::UPDATE => [new UpdateSet(RefreshToken::IS_REVOKED, 1)]
    ]);
  }
  
  /**
   * Revokes all sessions of a user, logging them out everywhere. Call this whenever the user's
   * credentials or validity change.
   *
   * @param int $userId
   * @throws Exception
   */
  public static function revokeAllForUser(int $userId): void {
    Factory::getRefreshTokenFactory()->massUpdate([
      Factory::FILTER => [new QueryFilter(RefreshToken::USER_ID, $userId, "=")],
      Factory::UPDATE => [new UpdateSet(RefreshToken::IS_REVOKED, 1)]
    ]);
  }
  
  /**
   * Removes every token of a user. Unlike revoking, this leaves nothing behind, which is what user
   * deletion needs since the tokens reference the user row.
   *
   * @param int $userId
   * @throws Exception
   */
  public static function deleteAllForUser(int $userId): void {
    Factory::getRefreshTokenFactory()->massDeletion([
      Factory::FILTER => [new QueryFilter(RefreshToken::USER_ID, $userId, "=")]
    ]);
  }
  
  /**
   * Drops tokens which can no longer be exchanged, keeping the table from growing without bound.
   * Revoked tokens are kept until they expire so that a replay is still recognised as such.
   *
   * @param int|null $userId limits the cleanup to one user when given
   * @throws Exception
   */
  public static function purgeExpired(?int $userId = null): void {
    $filters = [new QueryFilter(RefreshToken::END_VALID, time(), "<")];
    if ($userId !== null) {
      $filters[] = new QueryFilter(RefreshToken::USER_ID, $userId, "=");
    }
    Factory::getRefreshTokenFactory()->massDeletion([Factory::FILTER => $filters]);
  }
  
  /**
   * @param string $plain the token string as presented by the client
   * @return RefreshToken|null
   * @throws Exception
   */
  private static function findByPlain(string $plain): ?RefreshToken {
    if ($plain === "") {
      return null;
    }
    $filter = new QueryFilter(RefreshToken::TOKEN_HASH, self::hashToken($plain), "=");
    return Factory::getRefreshTokenFactory()->filter([Factory::FILTER => $filter], true);
  }
}
