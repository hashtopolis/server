<?php

namespace Hashtopolis\inc {
	function is_file($path) {
		if (isset($GLOBALS['notification_email_is_file_mock']) && is_callable($GLOBALS['notification_email_is_file_mock'])) {
			return $GLOBALS['notification_email_is_file_mock']($path);
		}
		return \is_file($path);
	}

	function mail($to, $subject, $message, $additionalHeaders = null, $additionalParams = null) {
		if (isset($GLOBALS['notification_email_mail_mock']) && is_callable($GLOBALS['notification_email_mail_mock'])) {
			return $GLOBALS['notification_email_mail_mock']($to, $subject, $message, $additionalHeaders, $additionalParams);
		}
		if ($additionalParams === null) {
			return \mail($to, $subject, $message, $additionalHeaders ?? '');
		}
		return \mail($to, $subject, $message, $additionalHeaders ?? '', $additionalParams);
	}

	function error_log($message, $messageType = 0, $destination = null, $additionalHeaders = null) {
		if (isset($GLOBALS['notification_email_error_log_mock']) && is_callable($GLOBALS['notification_email_error_log_mock'])) {
			return $GLOBALS['notification_email_error_log_mock']($message, $messageType, $destination, $additionalHeaders);
		}
		if ($destination === null) {
			return \error_log($message, $messageType);
		}
		if ($additionalHeaders === null) {
			return \error_log($message, $messageType, $destination);
		}
		return \error_log($message, $messageType, $destination, $additionalHeaders);
	}
}

namespace Hashtopolis\inc\notifications {
	function error_log($message, $messageType = 0, $destination = null, $additionalHeaders = null) {
		if (isset($GLOBALS['notification_email_error_log_mock']) && is_callable($GLOBALS['notification_email_error_log_mock'])) {
			return $GLOBALS['notification_email_error_log_mock']($message, $messageType, $destination, $additionalHeaders);
		}
		if ($destination === null) {
			return \error_log($message, $messageType);
		}
		if ($additionalHeaders === null) {
			return \error_log($message, $messageType, $destination);
		}
		return \error_log($message, $messageType, $destination, $additionalHeaders);
	}
}

namespace Tests\Inc\Notifications {

  use Hashtopolis\inc\notifications\HashtopolisNotificationEmail;
  use PHPUnit\Framework\TestCase;

  require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

  final class HashtopolisNotificationEmailTest extends TestCase {
    protected function setUp(): void {
      parent::setUp();

      unset(
        $GLOBALS['notification_email_is_file_mock'],
        $GLOBALS['notification_email_mail_mock'],
        $GLOBALS['notification_email_error_log_mock']
      );
    }

    protected function tearDown(): void {
      unset(
        $GLOBALS['notification_email_is_file_mock'],
        $GLOBALS['notification_email_mail_mock'],
        $GLOBALS['notification_email_error_log_mock']
      );

      parent::tearDown();
    }

    public function testSendMessageDoesNotCallSendMailWhenMailIsNotConfigured(): void {
      $mailCallCount = 0;
      $errorLogMessages = [];

      $GLOBALS['notification_email_is_file_mock'] = static function ($path): bool {
        return false;
      };
      $GLOBALS['notification_email_mail_mock'] = static function () use (&$mailCallCount): bool {
        $mailCallCount++;
        return true;
      };
      $GLOBALS['notification_email_error_log_mock'] = static function ($message) use (&$errorLogMessages): bool {
        $errorLogMessages[] = $message;
        return true;
      };

      $notification = $this->createNotification();
      $notification->sendMessage('<p>html</p>##########plain', 'Subject');

      $this->assertSame(0, $mailCallCount);
      $this->assertSame([], $errorLogMessages);
    }

    public function testSendMessageCallsSendMailWhenMailIsConfigured(): void {
      $mailCalls = [];
      $errorLogMessages = [];

      $GLOBALS['notification_email_is_file_mock'] = static function ($path): bool {
        return true;
      };
      $GLOBALS['notification_email_mail_mock'] = static function ($to, $subject, $message, $additionalHeaders = null, $additionalParams = null) use (&$mailCalls): bool {
        $mailCalls[] = [$to, $subject, $message, $additionalHeaders, $additionalParams];
        return true;
      };
      $GLOBALS['notification_email_error_log_mock'] = static function ($message) use (&$errorLogMessages): bool {
        $errorLogMessages[] = $message;
        return true;
      };

      $notification = $this->createNotification();
      $notification->sendMessage('<p>html</p>##########plain', 'Subject');

      $this->assertCount(1, $mailCalls);
      $this->assertSame('receiver@example.com', $mailCalls[0][0]);
      $this->assertSame('Subject', $mailCalls[0][1]);
      $this->assertStringContainsString('<p>html</p>', $mailCalls[0][2]);
      $this->assertStringContainsString('plain', $mailCalls[0][2]);
    }

    public function testSendMessageLogsWhenConfiguredSendMailFails(): void {
      $mailCallCount = 0;
      $errorLogMessages = [];

      $GLOBALS['notification_email_is_file_mock'] = static function ($path): bool {
        return true;
      };
      $GLOBALS['notification_email_mail_mock'] = static function () use (&$mailCallCount): bool {
        $mailCallCount++;
        return false;
      };
      $GLOBALS['notification_email_error_log_mock'] = static function ($message) use (&$errorLogMessages): bool {
        $errorLogMessages[] = $message;
        return true;
      };

      $notification = $this->createNotification();
      $notification->sendMessage('<p>html</p>##########plain', 'Subject');

      $this->assertSame(1, $mailCallCount);
      $this->assertSame([
        'Unable to send notification mail with subject: Subject',
      ], $errorLogMessages);
    }

    private function createNotification(): HashtopolisNotificationEmail {
      $notification = new HashtopolisNotificationEmail();
      $receiverProperty = new \ReflectionProperty($notification, 'receiver');
      $receiverProperty->setValue($notification, 'receiver@example.com');
      return $notification;
    }
  }
}
