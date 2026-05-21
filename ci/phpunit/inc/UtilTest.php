<?php

namespace Hashtopolis\inc;

use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class UtilTest extends TestCase {
  public function testIsMailConfiguredReturnsFalseWithoutSsmtpConfig(): void {
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\is_file', static function ($path): bool {
      return false;
    });
    
    try {
      $this->assertFalse(Util::isMailConfigured());
    }
    finally {
      \hashtopolis_clear_test_mocks(['Hashtopolis\\inc\\is_file']);
    }
  }
  
  public function testIsMailConfiguredReturnsTrueWithSsmtpConfig(): void {
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\is_file', static function ($path): bool {
      return true;
    });
    
    try {
      $this->assertTrue(Util::isMailConfigured());
    }
    finally {
      \hashtopolis_clear_test_mocks(['Hashtopolis\\inc\\is_file']);
    }
  }
  
  public function testSendMailReturnsFalseWhenMailIsNotConfigured(): void {
    $loggedMessage = null;
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\is_file', static function ($path): bool {
      return false;
    });
    
    try {
      $this->assertFalse(Util::sendMail('user@example.com', 'subject', '<p>body</p>', 'body'));
    }
    finally {
      \hashtopolis_clear_test_mocks(['Hashtopolis\\inc\\is_file']);
    }
  }
}

