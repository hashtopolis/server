<?php

namespace Hashtopolis\inc\utils;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DLimits;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\defines\DAccessControl;
use Hashtopolis\inc\utils\AccessControlUtils;
use Hashtopolis\TestBase;
use Override;

final class AccessControlUtilsTest extends TestBase {
  private RightGroup $group;
  private RightGroup $otherGroup;

  #[Override]
  protected function setUp(): void {
    parent::setUp();

    $this->group = $this->createDatabaseObject(
      Factory::getRightGroupFactory(),
      new RightGroup(null, 'phpunit-' . uniqid('', true), '[]')
    );

    $this->otherGroup = $this->createDatabaseObject(
      Factory::getRightGroupFactory(),
      new RightGroup(null, 'phpunit-' . uniqid('', true), '[]')
    );
  }

  #[Override]
  protected function tearDown(): void{
    parent::tearDown();
  }

  

  public function testGetMembersOfGroupReturnsOnlyMembersOfGroup(): void {
    $firstMember = $this->createDatabaseObject(
      Factory::getUserFactory(),
      new User(null, 'phpunit_' . uniqid(), 'phpunit_' . uniqid() . '@example.com', 'hash', 'salt', 1, 0, 0, time(), 3600, $this->group->getId(), '', '', '', '', '')
    );

    $secondMember = $this->createDatabaseObject(
      Factory::getUserFactory(),
      new User(null, 'phpunit_' . uniqid(), 'phpunit_' . uniqid() . '@example.com', 'hash', 'salt', 1, 0, 0, time(), 3600, $this->group->getId(), '', '', '', '', '')
    );

    $otherMember = $this->createDatabaseObject(
      Factory::getUserFactory(),
      new User(null, 'phpunit_' . uniqid(), 'phpunit_' . uniqid() . '@example.com', 'hash', 'salt', 1, 0, 0, time(), 3600, $this->otherGroup->getId(), '', '', '', '', '')
    );

    $members = AccessControlUtils::getMembers($this->group->getId());
    $memberIds = array_map(static fn (User $user): int => $user->getId(), $members);

    $this->assertCount(2, $members);
    $this->assertContains($firstMember->getId(), $memberIds);
    $this->assertContains($secondMember->getId(), $memberIds);
    $this->assertNotContains($otherMember->getId(), $memberIds);
  }

  public function testThatAdminGroupPermissionCanNotBeAltered(): void {
    $this->expectException(HTException::class);
    AccessControlUtils::addToPermissions(
      $this->adminUser->getRightGroupId(),
      [DAccessControl::MANAGE_TASK_ACCESS => true]
    );
  }

  public function testAddPermissionsToNonExistentGroup(): void {
    $this->expectException(HTException::class);
    AccessControlUtils::addToPermissions(
      -3,
      [DAccessControl::MANAGE_TASK_ACCESS => true]
    );
  }

  public function testGetGroupLoadsExistingGroup(): void {
    $loadedGroup = AccessControlUtils::getGroup($this->group->getId());

    $this->assertInstanceOf(RightGroup::class, $loadedGroup);
    $this->assertSame($this->group->getId(), $loadedGroup->getId());
    $this->assertSame($this->group->getGroupName(), $loadedGroup->getGroupName());
  }

  public function testGetGroupThrowsForNonExistentGroup(): void {
    $this->expectException(HTException::class);
    AccessControlUtils::getGroup(-3);
  }

  public function testAddToPermissionThrowsOnNonExistentGroup(): void {
    $this->expectException(HTException::class);
    AccessControlUtils::addToPermissions(
      -3,
      [DAccessControl::MANAGE_TASK_ACCESS => true],
    );
  }

  public function testAddPermissionToGroup(): void {
     AccessControlUtils::addToPermissions(
      $this->group->getId(),
      [DAccessControl::MANAGE_TASK_ACCESS => true],
    );

    $updatedGroup = Factory::getRightGroupFactory()->get($this->group->getId());
    $permissions = json_decode($updatedGroup->getPermissions(), true);

    $this->assertIsArray($permissions);
    $this->assertArrayHasKey(DAccessControl::MANAGE_TASK_ACCESS, $permissions);
    $this->assertTrue($permissions[DAccessControl::MANAGE_TASK_ACCESS]);
  }

  //TODO: This seems off, we can write stuff in the permissions, we maybe should check that the permission is actually valid or throw.
  public function testAddNonExistentPermissionToGroup(): void {
    $nonexistentPermission = "nonexistent";
    AccessControlUtils::addToPermissions(
      $this->group->getId(),
      [$nonexistentPermission => true],
    );

    $updatedGroup = Factory::getRightGroupFactory()->get($this->group->getId());
    $permissions = json_decode($updatedGroup->getPermissions(), true);

    $this->assertIsArray($permissions);
    $this->assertArrayHasKey($nonexistentPermission, $permissions);
  }

  public function testUpdateNonexistentGroupThrowsException() {
    $this->expectException(HTException::class);
    AccessControlUtils::updateGroupPermissions(
      -3, 
      [DAccessControl::CRACKER_BINARY_ACCESS => true],
    );
  }

  public function testUpdateAdminPermissionsIsNotAllowed(): void {
    $this->expectException(HTException::class);
    AccessControlUtils::updateGroupPermissions(
      $this->adminUser->getRightGroupId(), 
      [DAccessControl::CRACKER_BINARY_ACCESS => true],
    );
  }

  public function testUpdatePermission() {
    $changed = AccessControlUtils::updateGroupPermissions(
      $this->group->getId(),
      [DAccessControl::MANAGE_TASK_ACCESS . '-1']
    );

    $updatedGroup = Factory::getRightGroupFactory()->get($this->group->getId());
    $permissions = json_decode($updatedGroup->getPermissions(), true);

    $this->assertFalse($changed);
    $this->assertIsArray($permissions);
    $this->assertArrayHasKey(DAccessControl::MANAGE_TASK_ACCESS, $permissions);
    $this->assertTrue($permissions[DAccessControl::MANAGE_TASK_ACCESS]);
  }

  public function testUpdatePermissionIgnoresValidPermissionWithInvalidInteger(): void {
    $changed = AccessControlUtils::updateGroupPermissions(
      $this->group->getId(),
      [DAccessControl::MANAGE_TASK_ACCESS . '-2']
    );

    $updatedGroup = Factory::getRightGroupFactory()->get($this->group->getId());
    $permissions = json_decode($updatedGroup->getPermissions(), true);

    $this->assertFalse($changed);
    $this->assertSame([], $permissions);
  }

  public function testUpdatePermissionIgnoresInvalidPermissionWithValidInteger(): void {
    $changed = AccessControlUtils::updateGroupPermissions(
      $this->group->getId(),
      ['nonexistentPermission-1']
    );

    $updatedGroup = Factory::getRightGroupFactory()->get($this->group->getId());
    $permissions = json_decode($updatedGroup->getPermissions(), true);

    $this->assertFalse($changed);
    $this->assertSame([], $permissions);
  }

  public function testUpdatePermissionAppliesDependencyOverride(): void {
    $changed = AccessControlUtils::updateGroupPermissions(
      $this->group->getId(),
      [
        DAccessControl::MANAGE_AGENT_ACCESS . '-1',
        DAccessControl::VIEW_AGENT_ACCESS[0] . '-0'
      ]
    );

    $updatedGroup = Factory::getRightGroupFactory()->get($this->group->getId());
    $permissions = json_decode($updatedGroup->getPermissions(), true);

    $this->assertTrue($changed);
    $this->assertIsArray($permissions);
    $this->assertArrayHasKey(DAccessControl::MANAGE_AGENT_ACCESS, $permissions);
    $this->assertTrue($permissions[DAccessControl::MANAGE_AGENT_ACCESS]);
    $this->assertArrayHasKey(DAccessControl::VIEW_AGENT_ACCESS[0], $permissions);
    $this->assertTrue($permissions[DAccessControl::VIEW_AGENT_ACCESS[0]]);
  }

  public function testCreateGroupThrowsForEmptyName(): void {
    $this->expectException(HttpError::class);

    AccessControlUtils::createGroup('');
  }

  public function testCreateGroupThrowsForNameLongerThanMaxLength(): void {
    $this->expectException(HttpError::class);

    AccessControlUtils::createGroup(str_repeat('a', DLimits::ACCESS_GROUP_MAX_LENGTH + 1));
  }

  public function testCreateGroupAllowsNameAtMaxLength(): void {
    $group = AccessControlUtils::createGroup(str_repeat('a', DLimits::ACCESS_GROUP_MAX_LENGTH));
    $this->registerDatabaseObject(Factory::getRightGroupFactory(), $group);

    $this->assertInstanceOf(RightGroup::class, $group);
    $this->assertSame(DLimits::ACCESS_GROUP_MAX_LENGTH, strlen($group->getGroupName()));
  }

  public function testCreateGroupThrowsForExistingGroupName(): void {
    $this->expectException(HttpConflict::class);

    AccessControlUtils::createGroup($this->group->getGroupName());
  }

  public function testDeleteGroupThrowsForNonExistentGroup(): void {
    $this->expectException(HTException::class);

    AccessControlUtils::deleteGroup(-3);
  }

  public function testDeleteGroupThrowsWhenGroupHasUsers(): void {
    $this->createDatabaseObject(
      Factory::getUserFactory(),
      new User(null, 'phpunit_' . uniqid(), 'phpunit_' . uniqid() . '@example.com', 'hash', 'salt', 1, 0, 0, time(), 3600, $this->group->getId(), '', '', '', '', '')
    );

    $this->expectException(HttpError::class);

    AccessControlUtils::deleteGroup($this->group->getId());
  }

  public function testDeleteGroupDeletesEmptyGroup(): void {
    $groupId = $this->group->getId();

    AccessControlUtils::deleteGroup($groupId);

    $this->expectException(HTException::class);
    AccessControlUtils::getGroup($groupId);
  }

}