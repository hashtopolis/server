<?php

namespace inc\notifications {

  use Hashtopolis\inc\notifications\HashtopolisNotificationEmail;
  use TestBase;
  
  require_once(dirname(__FILE__) . '/../../TestBase.php');
  require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

  final class HashtopolisNotificationEmailTest extends TestBase {

    public function testSendMessageDoesNotCallSendMailWhenMailIsNotConfigured(): void {
      $mailCallCount = 0;
      $errorLogMessages = [];

      \hashtopolis_set_test_mock('Hashtopolis\\inc\\is_file', static function ($path): bool {
        return false;
      });
      \hashtopolis_set_test_mock('Hashtopolis\\inc\\mail', static function () use (&$mailCallCount): bool {
        $mailCallCount++;
        return true;
      });

      $notification = $this->createNotification();
      $notification->sendMessage('<p>html</p>##########plain', 'Subject');

      $this->assertSame(0, $mailCallCount);
    }

    public function testSendMessageCallsSendMailWhenMailIsConfigured(): void {
      $mailCalls = [];
      $errorLogMessages = [];

      \hashtopolis_set_test_mock('Hashtopolis\\inc\\is_file', static function ($path): bool {
        return true;
      });
      \hashtopolis_set_test_mock('Hashtopolis\\inc\\mail', static function ($to, $subject, $message, $additionalHeaders = null, $additionalParams = null) use (&$mailCalls): bool {
        $mailCalls[] = [$to, $subject, $message, $additionalHeaders, $additionalParams];
        return true;
      });

      $notification = $this->createNotification();
      $notification->sendMessage('<p>html</p>##########plain', 'Subject');

      $this->assertCount(1, $mailCalls);
      $this->assertSame('receiver@example.com', $mailCalls[0][0]);
      $this->assertSame('Subject', $mailCalls[0][1]);
      $this->assertStringContainsString('<p>html</p>', $mailCalls[0][2]);
      $this->assertStringContainsString('plain', $mailCalls[0][2]);
    }

    public function testSendMessageThrowsWhenConfiguredSendMailFails(): void {
      $mailCallCount = 0;

      \hashtopolis_set_test_mock('Hashtopolis\\inc\\is_file', static function ($path): bool {
        return true;
      });
      \hashtopolis_set_test_mock('Hashtopolis\\inc\\mail', static function () use (&$mailCallCount): bool {
        $mailCallCount++;
        return false;
      });

      $notification = $this->createNotification();
      $this->expectException(\Exception::class);
      try {
        $notification->sendMessage('<p>html</p>##########plain', 'Subject');
      } finally {
        $this->assertSame(1, $mailCallCount);
      }
    }

    private function createNotification(): HashtopolisNotificationEmail {
      $notification = new HashtopolisNotificationEmail();
      $receiverProperty = new \ReflectionProperty($notification, 'receiver');
      $receiverProperty->setValue($notification, 'receiver@example.com');
      return $notification;
    }
  }
}
