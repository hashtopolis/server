<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\inc\Encryption;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\defines\DLogEntry;
use Hashtopolis\inc\defines\DLogEntryIssuer;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\Util;

class AccountUtils {
  /**
   * @param string $email
   * @param User $user
   * @throws HTException
   * @throws Exception
   */
  public static function setEmail(string $email, User $user): void {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new HTException("Invalid email address!");
    }
    
    $user = Factory::getUserFactory()->set($user, User::EMAIL, $email);
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "User changed email!");
  }
  
  /**
   * Rejects a session lifetime (in seconds) a user may not have.
   *
   * @param int $lifetime
   * @throws HTException
   */
  public static function validateSessionLifetime(int $lifetime): void {
    $min = RefreshTokenUtils::MIN_LIFETIME_SECONDS;
    $max = RefreshTokenUtils::maxLifetimeSeconds();

    if ($lifetime < $min || $lifetime > $max) {
      throw new HTException("Lifetime must be at least " . ($min / 3600) . " hours and at most " . ($max / 3600) . " hours!");
    }
  }

  /**
   * @param int $lifetime
   * @param User $user
   * @throws HTException
   * @throws Exception
   */
  public static function updateSessionLifetime(int $lifetime, User $user): void {
    self::validateSessionLifetime($lifetime);
    
    Factory::getUserFactory()->set($user, User::SESSION_LIFETIME, $lifetime);
  }
  
  /**
   * @param string $oldPassword
   * @param string $newPassword
   * @param string $repeatedPassword
   * @param User $user
   * @throws HTException
   * @throws Exception
   */
  public static function changePassword(string $oldPassword, string $newPassword, string $repeatedPassword, User $user): void {
    if (!Encryption::passwordVerify($oldPassword, $user->getPasswordSalt(), $user->getPasswordHash())) {
      throw new HTException("Your old password is wrong!");
    }
    else if (strlen($newPassword) < 4) {
      throw new HTException("Your password is too short!");
    }
    else if ($newPassword != $repeatedPassword) {
      throw new HTException("Your new passwords do not match!");
    }
    else if ($newPassword == $oldPassword) {
      throw new HTException("Your new password is the same as the old one!");
    }
    
    $newSalt = Util::randomString(20);
    $newHash = Encryption::passwordHash($newPassword, $newSalt);
    
    $user = Factory::getUserFactory()->mset($user, [User::PASSWORD_HASH => $newHash, User::PASSWORD_SALT => $newSalt, USer::IS_COMPUTED_PASSWORD => 0]);
    RefreshTokenUtils::revokeAllForUser($user->getId());
    
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "User changed password!");
  }
}