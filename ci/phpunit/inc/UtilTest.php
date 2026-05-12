<?php

namespace Hashtopolis\inc {
	function is_file($path) {
		if (isset($GLOBALS['util_is_file_mock']) && is_callable($GLOBALS['util_is_file_mock'])) {
			return $GLOBALS['util_is_file_mock']($path);
		}
		return \is_file($path);
	}

  function error_log($message, $messageType = 0, $destination = null, $additionalHeaders = null) {
    if (isset($GLOBALS['util_error_log_mock']) && is_callable($GLOBALS['util_error_log_mock'])) {
      return $GLOBALS['util_error_log_mock']($message, $messageType, $destination, $additionalHeaders);
    }
    return \error_log($message, $messageType, $destination, $additionalHeaders);
  }
}

namespace Tests\Inc {
  use Hashtopolis\inc\Util;
  use PHPUnit\Framework\TestCase;

  require_once(dirname(__FILE__) . '/../../../src/inc/startup/include.php');

  final class UtilTest extends TestCase {
    public function testIsMailConfiguredReturnsFalseWithoutSsmtpConfig(): void {
      $GLOBALS['util_is_file_mock'] = static function ($path): bool {
        return false;
      };

      try {
        $this->assertFalse(Util::isMailConfigured());
      }
      finally {
        unset($GLOBALS['util_is_file_mock']);
      }
    }

    public function testIsMailConfiguredReturnsTrueWithSsmtpConfig(): void {
      $GLOBALS['util_is_file_mock'] = static function ($path): bool {
        return true;
      };

      try {
        $this->assertTrue(Util::isMailConfigured());
      }
      finally {
        unset($GLOBALS['util_is_file_mock']);
      }
    }

    public function testSendMailReturnsFalseAndLogsWhenMailIsNotConfigured(): void {
      $loggedMessage = null;
      $GLOBALS['util_is_file_mock'] = static function ($path): bool {
        return false;
      };
      $GLOBALS['util_error_log_mock'] = static function ($message) use (&$loggedMessage): bool {
        $loggedMessage = $message;
        return true;
      };

      try {
        $this->assertFalse(Util::sendMail('user@example.com', 'subject', '<p>body</p>', 'body'));
        $this->assertSame('Mail notification is not configured. No message sent.', $loggedMessage);
      }
      finally {
        unset($GLOBALS['util_is_file_mock'], $GLOBALS['util_error_log_mock']);
      }
    }
  }

  
}
