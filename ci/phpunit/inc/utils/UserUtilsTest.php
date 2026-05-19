<?php

namespace Hashtopolis\inc\templating {
  class Template {
    private string $name;

    public function __construct(string $name) {
      $this->name = $name;
    }

    public function render($data): string {
      return $this->name . ':' . json_encode($data);
    }
  }
}

namespace Tests\Inc\Utils {

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\AccessGroupUser;
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\utils\UserUtils;
use Hashtopolis\inc\apiv2\error\InternalError;
use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__) . '/../TestMocks.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class UserUtilsTest extends TestCase {
  /** @var string[] */
  private array $createdUsernames = [];

  /** @var int[] */
  private array $createdRightGroupIds = [];

  protected function setUp(): void {
    parent::setUp();

    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? 80;
    \hashtopolis_clear_test_mocks();
  }

  protected function tearDown(): void {
    foreach ($this->createdUsernames as $username) {
      $qF = new QueryFilter(User::USERNAME, $username, '=');
      $users = Factory::getUserFactory()->filter([Factory::FILTER => $qF]);
      foreach ($users as $user) {
        $memberFilter = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), '=');
        Factory::getAccessGroupUserFactory()->massDeletion([Factory::FILTER => $memberFilter]);
        Factory::getUserFactory()->delete($user);
      }
    }

    foreach ($this->createdRightGroupIds as $rightGroupId) {
      $group = Factory::getRightGroupFactory()->get($rightGroupId);
      if ($group !== null) {
        Factory::getRightGroupFactory()->delete($group);
      }
    }

    \hashtopolis_clear_test_mocks();

    parent::tearDown();
  }

  public function testCreateUserDoesNotCallSendMailWhenMailIsNotConfigured(): void {
    $mailCallCount = 0;
    $username = $this->uniqueUsername('mail_disabled');

    \hashtopolis_set_test_mock('Hashtopolis\\inc\\is_file', static function ($path): bool {
      return false;
    });
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\mail', static function () use (&$mailCallCount): bool {
      $mailCallCount++;
      return true;
    });

    $createdUser = UserUtils::createUser($username, $username . '@example.com', $this->createRightGroup()->getId(), $this->createAdminUser());
    $this->createdUsernames[] = $username;

    $this->assertSame($username, $createdUser->getUsername());
    $this->assertSame(0, $mailCallCount);
  }

  public function testCreateUserCallsSendMailWhenMailIsConfigured(): void {
    $mailCalls = [];
    $username = $this->uniqueUsername('mail_enabled');

    \hashtopolis_set_test_mock('Hashtopolis\\inc\\is_file', static function ($path): bool {
      return true;
    });
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\mail', static function ($to, $subject, $message, $additionalHeaders = null, $additionalParams = null) use (&$mailCalls): bool {
      $mailCalls[] = [$to, $subject, $message, $additionalHeaders, $additionalParams];
      return true;
    });

    UserUtils::createUser($username, $username . '@example.com', $this->createRightGroup()->getId(), $this->createAdminUser());
    $this->createdUsernames[] = $username;

    $this->assertCount(1, $mailCalls);
    $this->assertSame($username . '@example.com', $mailCalls[0][0]);
    $this->assertSame('Account at ' . APP_NAME, $mailCalls[0][1]);
  }

  public function testCreateUserThrowsWhenConfiguredSendMailFails(): void {
    $mailCallCount = 0;
    $username = $this->uniqueUsername('mail_failure');

    \hashtopolis_set_test_mock('Hashtopolis\\inc\\is_file', static function ($path): bool {
      return true;
    });
    \hashtopolis_set_test_mock('Hashtopolis\\inc\\mail', static function () use (&$mailCallCount): bool {
      $mailCallCount++;
      return false;
    });

    $this->createdUsernames[] = $username;
    
    $this->expectException(InternalError::class);
    try {
      UserUtils::createUser($username, $username . '@example.com', $this->createRightGroup()->getId(), $this->createAdminUser());
    } finally {
      $this->assertSame(1, $mailCallCount);
    }
  }

  private function createAdminUser(): User {
    return new User(1, 'admin', 'admin@example.com', 'hash', 'salt', 1, 0, 0, time(), 3600, 1, '', '', '', '', '');
  }

  private function createRightGroup(): RightGroup {
    $group = Factory::getRightGroupFactory()->save(new RightGroup(null, 'phpunit-' . uniqid('', true), '[]'));
    $this->createdRightGroupIds[] = $group->getId();
    return $group;
  }

  private function uniqueUsername(string $prefix): string {
    return $prefix . '_' . uniqid();
  }
}
}
