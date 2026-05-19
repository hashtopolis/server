<?php

namespace inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\utils\UserUtils;
use Hashtopolis\inc\apiv2\error\InternalError;
use TestBase;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class UserUtilsTest extends TestBase {
  protected function setUp(): void {
    parent::setUp();
    
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? 80;
    \hashtopolis_clear_test_mocks();
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
    
    $createdUser = UserUtils::createUser($username, $username . '@example.com', $this->createRightGroup()->getId(), $this->adminUser);
    $this->registerDatabaseObject(Factory::getUserFactory(), $createdUser);
    
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
    
    $createdUser = UserUtils::createUser($username, $username . '@example.com', $this->createRightGroup()->getId(), $this->adminUser);
    $this->registerDatabaseObject(Factory::getUserFactory(), $createdUser);
    
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
    
    $this->expectException(InternalError::class);
    try {
      $createdUser = UserUtils::createUser($username, $username . '@example.com', $this->createRightGroup()->getId(), $this->adminUser);
      $this->registerDatabaseObject(Factory::getUserFactory(), $createdUser);
    }
    finally {
      $this->assertSame(1, $mailCallCount);
      $this->registerDatabaseObject(Factory::getUserFactory(), Factory::getUserFactory()->filter([Factory::FILTER => new QueryFilter(User::USERNAME, $username, "=")], true));
    }
  }
  
  private function createRightGroup(): RightGroup {
    $group = $this->createDatabaseObject(Factory::getRightGroupFactory(), new RightGroup(null, 'phpunit-' . uniqid('', true), '[]'));
    $this->assertTrue($group instanceof RightGroup);
    return $group;
  }
  
  private function uniqueUsername(string $prefix): string {
    return $prefix . '_' . uniqid();
  }
}
