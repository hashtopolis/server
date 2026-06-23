<?php

namespace Hashtopolis\inc;

use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class EncryptionTest extends TestBase {
  protected function tearDown(): void {
    $p = (new \ReflectionClass(StartupConfig::class))->getProperty('instance');
    $p->setValue(null, null);
    parent::tearDown();
  }
  
  /**
   * validPassword returns false when the input is shorter than 8 characters.
   */
  public function testValidPasswordTooShort(): void {
    $this->assertFalse(Encryption::validPassword("Ab1!"));
  }
  
  /**
   * validPassword returns false when the input contains no uppercase letter.
   */
  public function testValidPasswordMissingUppercase(): void {
    $this->assertFalse(Encryption::validPassword("abc1!defg"));
  }
  
  /**
   * validPassword returns false when the input contains no lowercase letter.
   */
  public function testValidPasswordMissingLowercase(): void {
    $this->assertFalse(Encryption::validPassword("ABC1!DEFG"));
  }
  
  /**
   * validPassword returns false when the input contains no digit.
   */
  public function testValidPasswordMissingDigit(): void {
    $this->assertFalse(Encryption::validPassword("Abc!defgh"));
  }
  
  /**
   * validPassword returns false when the input contains no special character.
   */
  public function testValidPasswordMissingSpecial(): void {
    $this->assertFalse(Encryption::validPassword("Abc1defgh"));
  }
  
  /**
   * validPassword returns true when the input meets all complexity requirements.
   */
  public function testValidPasswordValid(): void {
    $this->assertTrue(Encryption::validPassword("Abc1!defg"));
  }
  
  /**
   * validPassword returns true with exactly 8 characters containing all required types.
   */
  public function testValidPasswordMinLength(): void {
    $this->assertTrue(Encryption::validPassword("Ab1!xxxx"));
  }
  
  /**
   * passwordHash produces a bcrypt hash that passwordVerify accepts with matching password and salt.
   */
  public function testPasswordHashAndVerify(): void {
    $hash = Encryption::passwordHash("MySecureP@ss1", "salt123");
    $this->assertTrue(Encryption::passwordVerify("MySecureP@ss1", "salt123", $hash));
  }
  
  /**
   * passwordVerify returns false when the wrong password is supplied.
   */
  public function testPasswordVerifyWrongPassword(): void {
    $hash = Encryption::passwordHash("RealP@ss1", "salt123");
    $this->assertFalse(Encryption::passwordVerify("WrongP@ss1", "salt123", $hash));
  }
  
  /**
   * passwordVerify returns false when the wrong salt is supplied, even if the password is correct.
   */
  public function testPasswordVerifyWrongSalt(): void {
    $hash = Encryption::passwordHash("RealP@ss1", "salt123");
    $this->assertFalse(Encryption::passwordVerify("RealP@ss1", "wrongsalt", $hash));
  }
  
  /**
   * passwordVerify returns false when given a malformed or corrupt hash string.
   */
  public function testPasswordVerifyCorruptHash(): void {
    $this->assertFalse(Encryption::passwordVerify("any", "salt", '$2y$12$notarealhash'));
  }
  
  /**
   * sessionHash is deterministic: identical inputs produce the same output.
   * Requires bcmath extension for bcpowmod in getCount().
   */
  public function testSessionHashDeterministic(): void {
    if (!extension_loaded('bcmath')) {
      $this->markTestSkipped('bcmath extension required for sessionHash');
    }
    $h1 = Encryption::sessionHash(1, 1000000, "admin");
    $h2 = Encryption::sessionHash(1, 1000000, "admin");
    $this->assertSame($h1, $h2);
  }
  
  /**
   * sessionHash produces different output when the session ID changes.
   * Requires bcmath extension for bcpowmod in getCount().
   */
  public function testSessionHashChangesOnDifferentId(): void {
    if (!extension_loaded('bcmath')) {
      $this->markTestSkipped('bcmath extension required for sessionHash');
    }
    $h1 = Encryption::sessionHash(1, 1000000, "admin");
    $h2 = Encryption::sessionHash(2, 1000000, "admin");
    $this->assertNotSame($h1, $h2);
  }
  
  /**
   * sessionHash produces different output when the username changes.
   * Requires bcmath extension for bcpowmod in getCount().
   */
  public function testSessionHashChangesOnDifferentUsername(): void {
    if (!extension_loaded('bcmath')) {
      $this->markTestSkipped('bcmath extension required for sessionHash');
    }
    $h1 = Encryption::sessionHash(1, 1000000, "admin");
    $h2 = Encryption::sessionHash(1, 1000000, "user2");
    $this->assertNotSame($h1, $h2);
  }
  
  /**
   * validationHash is deterministic: identical inputs produce the same output.
   * Requires bcmath extension for bcpowmod in getCount().
   */
  public function testValidationHashDeterministic(): void {
    if (!extension_loaded('bcmath')) {
      $this->markTestSkipped('bcmath extension required for validationHash');
    }
    $h1 = Encryption::validationHash(1, "admin");
    $h2 = Encryption::validationHash(1, "admin");
    $this->assertSame($h1, $h2);
  }
  
  /**
   * validationHash produces different output when the user ID changes.
   * Requires bcmath extension for bcpowmod in getCount().
   */
  public function testValidationHashChangesOnDifferentId(): void {
    if (!extension_loaded('bcmath')) {
      $this->markTestSkipped('bcmath extension required for validationHash');
    }
    $h1 = Encryption::validationHash(1, "admin");
    $h2 = Encryption::validationHash(2, "admin");
    $this->assertNotSame($h1, $h2);
  }
  
  /**
   * validationHash produces different output when the username changes.
   * Requires bcmath extension for bcpowmod in getCount().
   */
  public function testValidationHashChangesOnDifferentUsername(): void {
    if (!extension_loaded('bcmath')) {
      $this->markTestSkipped('bcmath extension required for validationHash');
    }
    $h1 = Encryption::validationHash(1, "admin");
    $h2 = Encryption::validationHash(1, "user2");
    $this->assertNotSame($h1, $h2);
  }
}
