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
 * There is no window in which a second use is forgiven. A stolen cookie is byte for byte the token
 * the legitimate holder has, presented over a request that looks the same, so any allowance made for
 * a client that asked twice is an allowance made for the thief as well. Clients that might otherwise
 * race are expected to serialise their own refreshes; the frontend holds a cross-tab lock for this.
 */
class RefreshTokenUtils {
  /** Number of random bytes in a token; the token string is this hex-encoded, so twice as long. */
  const TOKEN_BYTES = 32;
  
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
    
    $now = time();
    if ($token->getEndValid() < $now) {
      throw new HttpUnauthorized("Refresh token has expired");
    }
    
    if (!self::claim($token, $now)) {
      // Somebody else consumed the token first, so this is the second use of a single-use token
      self::resolveLostClaim($plain);
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
    
    return [
      "user" => $user,
      "token" => self::issue($token->getUserId(), $token->getFamilyId()),
    ];
  }
  
  /**
   * Marks a token as consumed, but only while it is still unused and unrevoked.
   *
   * The condition lives in the UPDATE rather than in a preceding read, so the database elects exactly
   * one winner when requests arrive together. Reading usedAt first would let two requests both see
   * null and both go on to issue a successor, which is precisely the case replay detection exists to
   * catch: a stolen token racing a legitimate request would never be seen as a second use.
   *
   * @param RefreshToken $token
   * @param int $now
   * @return bool whether this caller is the one that consumed the token
   * @throws Exception
   */
  private static function claim(RefreshToken $token, int $now): bool {
    return Factory::getRefreshTokenFactory()->compareAndSet(
      $token,
      [RefreshToken::USED_AT => null, RefreshToken::IS_REVOKED => 0],
      [RefreshToken::USED_AT => $now]
    );
  }
  
  /**
   * Decides what losing the claim means, from the state the winner left behind.
   *
   * Returning means the loss was a client firing two refreshes at once and the caller may carry on;
   * Losing it always ends the session: either the token was revoked, or it has already been
   * exchanged, and a token exchanged twice is a token two parties hold.
   *
   * @param string $plain the token string as presented by the client
   * @throws HttpUnauthorized always; which error it is depends on what the winner left behind
   * @throws Exception
   */
  private static function resolveLostClaim(string $plain): void {
    $current = self::findByPlain($plain);
    if ($current === null || $current->getIsRevoked() == 1) {
      throw new HttpUnauthorized("Refresh token has been revoked");
    }
    
    /* The token was already exchanged, so somebody else is holding a copy of it. There is no way to
       tell the legitimate holder from the attacker, so the whole session goes. */
    self::revokeFamily($current->getFamilyId());
    Util::createLogEntry(
      DLogEntryIssuer::USER,
      (string)$current->getUserId(),
      DLogEntry::WARN,
      "Refresh token replay detected, all sessions of this login have been revoked!"
    );
    throw new HttpUnauthorized("Refresh token has already been used");
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
