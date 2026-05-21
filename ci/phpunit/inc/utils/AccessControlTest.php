<?php

namespace Hashtopolis\inc\utils;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\defines\DAccessControl;
use Hashtopolis\inc\utils\AccessControl;
use Hashtopolis\TestBase;
use ReflectionClass;

final class AccessControlTest extends TestBase {
  protected function setUp(): void {
    parent::setUp();
    $this->resetAccessControlInstance();
    $this->resetLoginInstance();
  }

  protected function tearDown(): void {
    parent::tearDown();
  }

  public function testGetInstanceWithoutArgsReusesSameObject(): void {
    $first = AccessControl::getInstance();
    $second = AccessControl::getInstance();

    $this->assertInstanceOf(AccessControl::class, $first);
    $this->assertNull($first->getUser());

    $this->assertSame($first, $second);
  }

  public function testGetInstanceWithGroupIdOverwritesInstance(): void {
    $first = AccessControl::getInstance();
    $second = AccessControl::getInstance(null, 1);

    $this->assertInstanceOf(AccessControl::class, $first);
    $this->assertNull($first->getUser());

    $this->assertInstanceOf(AccessControl::class, $second);
    $this->assertNull($second->getUser());
    
    $this->assertNotSame($first, $second);
  }

  public function testGetInstanceWithUserOverwritesInstance(): void {
    $first = AccessControl::getInstance();
    $second = AccessControl::getInstance($this->adminUser);

    $this->assertInstanceOf(AccessControl::class, $first);
    $this->assertNull($first->getUser());

    $this->assertInstanceOf(AccessControl::class, $second);
    $this->assertEquals($this->adminUser, $second->getUser());
    
    $this->assertNotSame($first, $second);
  }

  public function testReloadReloadsTheRightsGroupForUser(): void {
    $group = $this->createDatabaseObject(
      Factory::getRightGroupFactory(),
      new RightGroup(null, 'phpunit-' . uniqid('', true), '{}')
    );

    $user = $this->createDatabaseObject(
      Factory::getUserFactory(),
      new User(null, 'phpunit_' . uniqid(), 'phpunit_' . uniqid() . '@example.com', 'hash', 'salt', 1, 0, 0, time(), 3600, $group->getId(), '', '', '', '', '')
    );

    $accessControl = AccessControl::getInstance($user);
    $this->assertFalse($accessControl->hasPermission(DAccessControl::MANAGE_TASK_ACCESS));
    Factory::getRightGroupFactory()->set(
      $group,
      RightGroup::PERMISSIONS,
      json_encode([DAccessControl::MANAGE_TASK_ACCESS => true])
    );
    $this->assertFalse($accessControl->hasPermission(DAccessControl::MANAGE_TASK_ACCESS));

    $accessControl->reload();
    $this->assertTrue($accessControl->hasPermission(DAccessControl::MANAGE_TASK_ACCESS));
  }

  public function testReloadDoesNotReloadTheRightsGroupWithoutUser(): void {
    $group = $this->createDatabaseObject(
      Factory::getRightGroupFactory(),
      new RightGroup(null, 'phpunit-' . uniqid('', true), '{}')
    );

    $accessControl = AccessControl::getInstance(null, $group->getId());
    $this->assertFalse($accessControl->hasPermission(DAccessControl::MANAGE_TASK_ACCESS));

    Factory::getRightGroupFactory()->set(
      $group,
      RightGroup::PERMISSIONS,
      json_encode([DAccessControl::MANAGE_TASK_ACCESS => true])
    );

    $this->assertFalse($accessControl->hasPermission(DAccessControl::MANAGE_TASK_ACCESS));

    $accessControl->reload();

    // TODO: Check if this is the desired behavour, ie not reloading if a groupId only.
    $this->assertFalse($accessControl->hasPermission(DAccessControl::MANAGE_TASK_ACCESS));
  }

  public function testPermissionPublicAccessAlwaysPermit(): void {
    $accessControl = AccessControl::getInstance();
    $this->assertTrue($accessControl->hasPermission(DAccessControl::PUBLIC_ACCESS));
  }

  public function testPermissionLoginWithLoggedInUserPermit(): void {
    $this->setLoginState(true, $this->adminUser);
    $accessControl = AccessControl::getInstance();
    $this->assertTrue($accessControl->hasPermission(DAccessControl::LOGIN_ACCESS));
  }

  public function testPermissionLoginWithoutLoggedInUserDenies(): void {
    $accessControl = AccessControl::getInstance();
    $this->assertFalse($accessControl->hasPermission(DAccessControl::LOGIN_ACCESS));
  }

  public function testUninitializedAccessControlDenies() {
    $accessControl = AccessControl::getInstance();
    foreach(DAccessControl::getConstants() as $constant) {
      $permission = is_array($constant) ? $constant[0] : $constant;
      if ($permission != DAccessControl::PUBLIC_ACCESS) {
        $this->assertFalse($accessControl->hasPermission($permission));
      }
    }
  }

  public function testRegularUserWithPermissionPermits(): void {
    $group = $this->createDatabaseObject(
      Factory::getRightGroupFactory(),
      new RightGroup(null, 'phpunit-' . uniqid('', true), json_encode([DAccessControl::MANAGE_TASK_ACCESS => true]))
    );

    $user = $this->createDatabaseObject(
      Factory::getUserFactory(),
      new User(null, 'phpunit_' . uniqid(), 'phpunit_' . uniqid() . '@example.com', 'hash', 'salt', 1, 0, 0, time(), 3600, $group->getId(), '', '', '', '', '')
    );

    $accessControl = AccessControl::getInstance($user);
    $this->assertTrue($accessControl->hasPermission(DAccessControl::MANAGE_TASK_ACCESS));
  }

  public function testRegularUserWithoutPermissionDenies(): void {
    $group = $this->createDatabaseObject(
      Factory::getRightGroupFactory(),
      new RightGroup(null, 'phpunit-' . uniqid('', true), json_encode([DAccessControl::VIEW_HASHES_ACCESS => true]))
    );

    $user = $this->createDatabaseObject(
      Factory::getUserFactory(),
      new User(null, 'phpunit_' . uniqid(), 'phpunit_' . uniqid() . '@example.com', 'hash', 'salt', 1, 0, 0, time(), 3600, $group->getId(), '', '', '', '', '')
    );

    $accessControl = AccessControl::getInstance($user);
    $this->assertFalse($accessControl->hasPermission(DAccessControl::MANAGE_TASK_ACCESS));
  }

  public function testALLUserPermissionPermitsAllPermissions(): void {
    $accessControl = AccessControl::getInstance($this->adminUser);
    foreach(DAccessControl::getConstants() as $constant) {
      $permission = is_array($constant) ? $constant[0] : $constant;
      $this->assertTrue($accessControl->hasPermission($permission));
    }
  }

  public function testGivenByDependencyImplied(): void {
    $group = $this->createDatabaseObject(
      Factory::getRightGroupFactory(),
      new RightGroup(null, 'phpunit-' . uniqid('', true), json_encode([DAccessControl::MANAGE_AGENT_ACCESS => true]))
    );

    $accessControl = AccessControl::getInstance(null, $group->getId());

    $this->assertTrue($accessControl->givenByDependency(DAccessControl::VIEW_AGENT_ACCESS[0]));
  }

  public function testGivenByDependencyDirect(): void {
    $group = $this->createDatabaseObject(
      Factory::getRightGroupFactory(),
      new RightGroup(
        null,
        'phpunit-' . uniqid('', true),
        json_encode([DAccessControl::MANAGE_TASK_ACCESS => true])
      )
    );

    $accessControl = AccessControl::getInstance(null, $group->getId());

    $this->assertTrue($accessControl->givenByDependency(DAccessControl::MANAGE_TASK_ACCESS));
  }

  /*
    Local test helpers
  */
  private function resetAccessControlInstance(): void {
    $reflection = new ReflectionClass(AccessControl::class);
    $instanceProperty = $reflection->getProperty('instance');
    $instanceProperty->setValue(null, null);
  }
  
  private function setLoginState(bool $valid, ?User $user = null): void {
    $reflection = new ReflectionClass(\Hashtopolis\inc\Login::class);
    $instanceProperty = $reflection->getProperty('instance');
    $instance = $instanceProperty->getValue();

    if ($instance === null) {
      \Hashtopolis\inc\Login::getInstance();
      $instance = $instanceProperty->getValue();
    }

    $validProperty = $reflection->getProperty('valid');
    $validProperty->setValue($instance, $valid);

    $userProperty = $reflection->getProperty('user');
    $userProperty->setValue($instance, $user);
  }

  private function resetLoginInstance(): void {
    $reflection = new ReflectionClass(\Hashtopolis\inc\Login::class);
    $instanceProperty = $reflection->getProperty('instance');
    $instanceProperty->setValue(null, null);
  }
}