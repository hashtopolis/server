<?php

namespace Tests\Inc {
  use Hashtopolis\inc\Util;
  use PHPUnit\Framework\TestCase;

  require_once(dirname(__FILE__) . '/TestMocks.php');
  require_once(dirname(__FILE__) . '/../../../src/inc/startup/include.php');

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

    public function testSendMailReturnsFalseAndLogsWhenMailIsNotConfigured(): void {
      $loggedMessage = null;
      \hashtopolis_set_test_mock('Hashtopolis\\inc\\is_file', static function ($path): bool {
        return false;
      });
      \hashtopolis_set_test_mock('Hashtopolis\\inc\\error_log', static function ($message) use (&$loggedMessage): bool {
        $loggedMessage = $message;
        return true;
      });

      try {
        $this->assertFalse(Util::sendMail('user@example.com', 'subject', '<p>body</p>', 'body'));
        $this->assertSame('Mail notification is not configured. No message sent.', $loggedMessage);
      }
      finally {
        \hashtopolis_clear_test_mocks(['Hashtopolis\\inc\\is_file', 'Hashtopolis\\inc\\error_log']);
      }
    }
  }

  
}
