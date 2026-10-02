<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\Encryption;
use Hashtopolis\inc\HTException;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class AccountUtilsTest extends TestBase {
  
  public function testSetEmailThrowsOnInvalidEmailFormat(): void {
    $user = $this->createUser('invalid_email_user');
    $this->expectException(HTException::class);
    AccountUtils::setEmail('invalid-email-address', $user);
  }
  
  public function testSetEmailUpdatesEmailOnValidAddress(): void {
    $user = $this->createUser('valid_email_user');
    $newEmail = 'updated_' . uniqid() . '@example.com';
    
    AccountUtils::setEmail($newEmail, $user);
    
    $reloadedUser = Factory::getUserFactory()->filter([
      Factory::FILTER => new QueryFilter(User::USERNAME, $user->getUsername(), '=')
    ], true);
    
    $this->assertInstanceOf(User::class, $reloadedUser);
    $this->assertSame($newEmail, $reloadedUser->getEmail());
  }
  
  public function testUpdateSessionLifetimeThrowsWhenBelowMinimum(): void {
    $user = $this->createUser('invalid_lifetime_user');
    $this->expectException(HTException::class);
    
    AccountUtils::updateSessionLifetime(59, $user);
  }
  
  public function testUpdateSessionLifetimeUpdatesPersistedValue(): void {
    $user = $this->createUser('valid_lifetime_user');
    $newLifetime = 60;
    
    AccountUtils::updateSessionLifetime($newLifetime, $user);
    
    $reloadedUser = Factory::getUserFactory()->filter([
      Factory::FILTER => new QueryFilter(User::USERNAME, $user->getUsername(), '=')
    ], true);
    
    $this->assertInstanceOf(User::class, $reloadedUser);
    $this->assertSame($newLifetime, $reloadedUser->getSessionLifetime());
  }
  
  public function testChangePasswordThrowsWhenOldPasswordIsWrong(): void {
    $user = $this->createUserWithPassword('wrong_old_password_user', 'oldpass');
    $this->expectException(HTException::class);
    $this->expectExceptionMessage('Your old password is wrong!');
    
    AccountUtils::changePassword('wrongpass', 'newpass', 'newpass', $user);
  }
  
  public function testChangePasswordThrowsWhenNewPasswordIsTooShort(): void {
    $user = $this->createUserWithPassword('short_new_password_user', 'oldpass');
    $this->expectException(HTException::class);
    $this->expectExceptionMessage('Your password is too short!');
    
    AccountUtils::changePassword('oldpass', 'abc', 'abc', $user);
  }
  
  public function testChangePasswordThrowsWhenNewPasswordsDoNotMatch(): void {
    $user = $this->createUserWithPassword('mismatch_password_user', 'oldpass');
    $this->expectException(HTException::class);
    $this->expectExceptionMessage('Your new passwords do not match!');
    
    AccountUtils::changePassword('oldpass', 'newpass', 'otherpass', $user);
  }
  
  public function testChangePasswordThrowsWhenNewPasswordMatchesOldPassword(): void {
    $user = $this->createUserWithPassword('same_password_user', 'oldpass');
    $this->expectException(HTException::class);
    $this->expectExceptionMessage('Your new password is the same as the old one!');
    
    AccountUtils::changePassword('oldpass', 'oldpass', 'oldpass', $user);
  }
  
  public function testChangePasswordUpdatesPersistedPasswordData(): void {
    $user = $this->createUserWithPassword('happy_password_user', 'oldpass');
    $oldSalt = $user->getPasswordSalt();
    $oldHash = $user->getPasswordHash();
    
    AccountUtils::changePassword('oldpass', 'newpass', 'newpass', $user);
    
    $reloadedUser = $this->reloadUser($user);
    
    $this->assertNotSame($oldSalt, $reloadedUser->getPasswordSalt());
    $this->assertNotSame($oldHash, $reloadedUser->getPasswordHash());
    $this->assertFalse(Encryption::passwordVerify('oldpass', $reloadedUser->getPasswordSalt(), $reloadedUser->getPasswordHash()));
    $this->assertTrue(Encryption::passwordVerify('newpass', $reloadedUser->getPasswordSalt(), $reloadedUser->getPasswordHash()));
    $this->assertSame(0, $reloadedUser->getIsComputedPassword());
  }
  
  /*
    Local test helpers
  */
  private function createUserWithPassword(string $prefix, string $password): User {
    $user = $this->createUser($prefix);
    UserUtils::setPassword($user->getId(), $password, $this->adminUser);
    return $this->reloadUser($user);
  }
  
  private function reloadUser(User $user): User {
    $reloadedUser = Factory::getUserFactory()->filter([
      Factory::FILTER => new QueryFilter(User::USERNAME, $user->getUsername(), '=')
    ], true);
    
    $this->assertInstanceOf(User::class, $reloadedUser);
    return $reloadedUser;
  }
}
