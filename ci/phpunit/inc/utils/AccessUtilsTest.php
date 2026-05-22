<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\models\AccessGroupUser;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\FileTask;
use Hashtopolis\dba\models\Hash;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\LogEntry;
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractBaseAPI;
use Hashtopolis\inc\defines\DAccessControl;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class AccessUtilsTest extends TestBase {
  #[Override]
  protected function setUp(): void {
    parent::setUp();
  }

  #[Override]
  protected function tearDown(): void {
    parent::tearDown();
  }

  public function testUserCanAccessManyHashlistsWhenSharesHashlistAccessGroups(): void {
    $group = $this->createAccessGroup('hashlist_access_group');
    $user = $this->createUser('hashlist_access_user');
    $hashType = $this->createHashType();
    $firstHashlist = $this->createHashlist($group, $hashType);
    $secondHashlist = $this->createHashlist($group, $hashType);

    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $group->getId(), $user->getId())
    );

    $this->assertTrue(AccessUtils::userCanAccessHashlists([$firstHashlist, $secondHashlist], $user));
  }

  public function testUserCanAccessSingleHashlistsWhenSharesHashlistAccessGroups(): void {
    $group = $this->createAccessGroup('hashlist_access_group');
    $user = $this->createUser('hashlist_access_user');
    $hashType = $this->createHashType();
    $firstHashlist = $this->createHashlist($group, $hashType);

    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $group->getId(), $user->getId())
    );

    $this->assertTrue(AccessUtils::userCanAccessHashlists($firstHashlist, $user));
  }

  public function testUserCanAccessTheEmptyHashlist(): void {
    $user = $this->createUser('hashlist_access_user');
    $this->assertTrue(AccessUtils::userCanAccessHashlists([], $user));
  }

  /*
  TODO: Passing null in an array will dereference it and throw an error. 
  One can fix that by filtering the array before checking, or maybe throw an Illegal arg exception.
  public function testUserCanAccessHashlistsThrowsWhenArrayContainsNull(): void {
    $user = $this->createUser('hashlist_access_user');

    //$this->expectException(\Error::class);

    AccessUtils::userCanAccessHashlists([null], $user);
  }
  */

  public function testGetPermissionArrayConvertedReturnsAllPermissionsAsTrueForAdmin(): void {
    $permissions = AccessUtils::getPermissionArrayConverted('ALL');
    $expectedPermissions = array_unique(array_merge(...array_values(AbstractBaseAPI::$acl_mapping)));
    
    sort($expectedPermissions);

    $this->assertSame($expectedPermissions, array_keys($permissions));
    $this->assertNotEmpty($permissions);
    foreach ($permissions as $permission => $isAllowed) {
      $this->assertIsString($permission);
      $this->assertTrue($isAllowed);
    }
  }

  /* 
    Sampled some cases to verify the actual mapping function, not that every permission is mapped correctly 
  */
  public function testGetPermissionArrayConvertedForUserPermissions(): void {
    $cases = [
      'view hashlists' => [
        'legacyPermission' => DAccessControl::VIEW_HASHLIST_ACCESS[0],
        'expectedTrue' => [Hashlist::PERM_READ],
        'expectedFalse' => [Hashlist::PERM_CREATE, Hashlist::PERM_UPDATE, Hashlist::PERM_DELETE],
      ],
      'create hashlists' => [
        'legacyPermission' => DAccessControl::CREATE_HASHLIST_ACCESS,
        'expectedTrue' => [Hashlist::PERM_CREATE, Hash::PERM_CREATE],
        'expectedFalse' => [Hashlist::PERM_READ, Hash::PERM_READ],
      ],
      'manage files' => [
        'legacyPermission' => DAccessControl::MANAGE_FILE_ACCESS,
        'expectedTrue' => [File::PERM_READ, File::PERM_UPDATE, File::PERM_DELETE],
        'expectedFalse' => [File::PERM_CREATE],
      ],
      'public access' => [
        'legacyPermission' => DAccessControl::PUBLIC_ACCESS,
        'expectedTrue' => [LogEntry::PERM_READ],
        'expectedFalse' => [LogEntry::PERM_CREATE, LogEntry::PERM_UPDATE, LogEntry::PERM_DELETE],
      ],
    ];

    foreach ($cases as $label => $case) {
      $permissions = AccessUtils::getPermissionArrayConverted(json_encode([$case['legacyPermission'] => true]));

      foreach ($case['expectedTrue'] as $crudPermission) {
        $this->assertArrayHasKey($crudPermission, $permissions, $label);
        $this->assertTrue($permissions[$crudPermission], sprintf('%s should enable %s.', $label, $crudPermission));
      }

      foreach ($case['expectedFalse'] as $crudPermission) {
        $this->assertArrayHasKey($crudPermission, $permissions, $label);
        $this->assertFalse($permissions[$crudPermission], sprintf('%s should not enable %s.', $label, $crudPermission));
      }
    }
  }

  public function testUserCannotAccessManyHashlistsWhenOneHashlistIsInDifferentAccessGroup(): void {
    $allowedGroup = $this->createAccessGroup('hashlist_access_group_allowed');
    $deniedGroup = $this->createAccessGroup('hashlist_access_group_denied');
    $user = $this->createUser('hashlist_access_user');
    $hashType = $this->createHashType();
    $allowedHashlist = $this->createHashlist($allowedGroup, $hashType);
    $deniedHashlist = $this->createHashlist($deniedGroup, $hashType);

    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $allowedGroup->getId(), $user->getId())
    );

    $this->assertFalse(AccessUtils::userCanAccessHashlists([$allowedHashlist, $deniedHashlist], $user));
  }

  public function testUserCanAccessAgentWhenTheyShareAnAccessGroup(): void {
    $group = $this->createAccessGroup('agent_access_group');
    $user = $this->createUser('agent_access_user');
    $agent = $this->createAgent('shared_access_agent');

    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $group->getId(), $user->getId())
    );

    $this->createDatabaseObject(
      Factory::getAccessGroupAgentFactory(),
      new AccessGroupAgent(null, $group->getId(), $agent->getId())
    );

    $this->assertTrue(AccessUtils::userCanAccessAgent($agent, $user));
  }

  public function testUserCannotAccessAgentWhenTheyDoNotShareAnAccessGroup(): void {
    $userGroup = $this->createAccessGroup('user_access_group');
    $agentGroup = $this->createAccessGroup('agent_access_group');
    $user = $this->createUser('agent_access_user');
    $agent = $this->createAgent('isolated_access_agent');

    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $userGroup->getId(), $user->getId())
    );

    $this->createDatabaseObject(
      Factory::getAccessGroupAgentFactory(),
      new AccessGroupAgent(null, $agentGroup->getId(), $agent->getId())
    );

    $this->assertFalse(AccessUtils::userCanAccessAgent($agent, $user));
  }

  public function testUserCanAccessTaskWhenTheyShareAnAccessGroup(): void {
    $group = $this->createAccessGroup('task_access_group');
    $user = $this->createUser('task_access_user');
    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($group, $hashType);
    $taskWrapper = $this->createTaskWrapper($group, $hashlist);

    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $group->getId(), $user->getId())
    );

    $this->assertTrue(AccessUtils::userCanAccessTask($taskWrapper, $user));
  }

  public function testUserCannotAccessTaskWhenTheyDoNotShareAnAccessGroup(): void {
    $userGroup = $this->createAccessGroup('user_task_access_group');
    $taskGroup = $this->createAccessGroup('wrapper_access_group');
    $user = $this->createUser('task_access_user');
    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($taskGroup, $hashType);
    $taskWrapper = $this->createTaskWrapper($taskGroup, $hashlist);

    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $userGroup->getId(), $user->getId())
    );

    $this->assertFalse(AccessUtils::userCanAccessTask($taskWrapper, $user));
  }

  public function testUserCanAccessFileWhenTheyShareAnAccessGroup(): void {
    $group = $this->createAccessGroup('file_access_group');
    $user = $this->createUser('file_access_user');
    $file = $this->createFile($group);

    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $group->getId(), $user->getId())
    );

    $this->assertTrue(AccessUtils::userCanAccessFile($file, $user));
  }

  public function testUserCannotAccessFileWhenTheyDoNotShareAnAccessGroup(): void {
    $userGroup = $this->createAccessGroup('user_file_access_group');
    $fileGroup = $this->createAccessGroup('file_access_group');
    $user = $this->createUser('file_access_user');
    $file = $this->createFile($fileGroup);

    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $userGroup->getId(), $user->getId())
    );

    $this->assertFalse(AccessUtils::userCanAccessFile($file, $user));
  }

  public function testIntersectionReturnsSharedAccessGroups(): void {
    $firstOnlyGroup = $this->createAccessGroup('first_only_group');
    $sharedGroup = $this->createAccessGroup('shared_group');
    $secondOnlyGroup = $this->createAccessGroup('second_only_group');

    $intersection = AccessUtils::intersection(
      [$firstOnlyGroup, $sharedGroup],
      [$sharedGroup, $secondOnlyGroup]
    );

    $this->assertSame([$sharedGroup], $intersection);
  }

  public function testIntersectionReturnsEmptyArrayWhenOneSideIsEmpty(): void {
    $group = $this->createAccessGroup('non_empty_group');

    $this->assertSame([], AccessUtils::intersection([], [$group]));
    $this->assertSame([], AccessUtils::intersection([$group], []));
  }

  public function testGetAccessGroupsOfUserReturnsAssignedGroups(): void {
    $firstGroup = $this->createAccessGroup('user_group_one');
    $secondGroup = $this->createAccessGroup('user_group_two');
    $user = $this->createUser('grouped_user');
    $defaultGroup = AccessUtils::getOrCreateDefaultAccessGroup();

    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $firstGroup->getId(), $user->getId())
    );
    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $secondGroup->getId(), $user->getId())
    );

    $groups = AccessUtils::getAccessGroupsOfUser($user);

    $this->assertEqualsCanonicalizing(
      [$defaultGroup->getId(), $firstGroup->getId(), $secondGroup->getId()],
      array_map(static fn (AccessGroup $group): ?int => $group->getId(), $groups)
    );
  }

  public function testGetAccessGroupsOfUserReturnsDefaultGroupWhenUserHasNoAdditionalAssignments(): void {
    $user = $this->createUser('ungrouped_user');
    $defaultGroup = AccessUtils::getOrCreateDefaultAccessGroup();
    $groups = AccessUtils::getAccessGroupsOfUser($user);

    $this->assertEqualsCanonicalizing(
      [$defaultGroup->getId()],
      array_map(static fn (AccessGroup $group): ?int => $group->getId(), $groups)
    );
  }

  public function testGetAccessGroupsOfAgentReturnsAssignedGroups(): void {
    $firstGroup = $this->createAccessGroup('agent_group_one');
    $secondGroup = $this->createAccessGroup('agent_group_two');
    $agent = $this->createAgent('grouped_agent');

    $this->createDatabaseObject(
      Factory::getAccessGroupAgentFactory(),
      new AccessGroupAgent(null, $firstGroup->getId(), $agent->getId())
    );
    $this->createDatabaseObject(
      Factory::getAccessGroupAgentFactory(),
      new AccessGroupAgent(null, $secondGroup->getId(), $agent->getId())
    );

    $groups = AccessUtils::getAccessGroupsOfAgent($agent);

    $this->assertEqualsCanonicalizing(
      [$firstGroup->getId(), $secondGroup->getId()],
      array_map(static fn (AccessGroup $group): ?int => $group->getId(), $groups)
    );
  }

  public function testGetAccessGroupsOfAgentReturnsEmptyArrayWhenAgentHasNoAssignments(): void {
    $agent = $this->createAgent('ungrouped_agent');
    $this->assertSame([], AccessUtils::getAccessGroupsOfAgent($agent));
  }

  public function testGetOrCreateDefaultAccessGroupReturnsExistingDefaultGroup(): void {
    $defaultGroup = AccessUtils::getOrCreateDefaultAccessGroup();

    $this->assertInstanceOf(AccessGroup::class, $defaultGroup);
    $this->assertSame(1, $defaultGroup->getId());
    $this->assertNotNull(Factory::getAccessGroupFactory()->get(1));
  }

  public function testAgentCannotAccessTaskWhenItCannotAccessTaskWrapper(): void {
    $agentGroup = $this->createAccessGroup('agent_task_group');
    $taskGroup = $this->createAccessGroup('task_wrapper_group');
    $agent = $this->createAgent('restricted_agent');
    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($taskGroup, $hashType);
    $taskWrapper = $this->createTaskWrapper($taskGroup, $hashlist);
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $task = $this->createTask($taskWrapper, $crackerBinary, $crackerBinaryType);

    $this->createDatabaseObject(
      Factory::getAccessGroupAgentFactory(),
      new AccessGroupAgent(null, $agentGroup->getId(), $agent->getId())
    );

    $this->assertFalse(AccessUtils::agentCanAccessTask($agent, $task));
  }

  public function testAgentCannotAccessTaskWhenHashlistIsSecretAndAgentIsNotTrusted(): void {
    $group = $this->createAccessGroup('secret_hashlist_group');
    $agent = $this->createAgent('untrusted_agent', 0);
    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($group, $hashType, 1);
    $taskWrapper = $this->createTaskWrapper($group, $hashlist);
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $task = $this->createTask($taskWrapper, $crackerBinary, $crackerBinaryType);

    $this->createDatabaseObject(
      Factory::getAccessGroupAgentFactory(),
      new AccessGroupAgent(null, $group->getId(), $agent->getId())
    );

    $this->assertFalse(AccessUtils::agentCanAccessTask($agent, $task));
  }

  public function testAgentCannotAccessTaskWhenHashlistIsInDifferentAccessGroup(): void {
    $sharedTaskGroup = $this->createAccessGroup('shared_task_group');
    $otherHashlistGroup = $this->createAccessGroup('other_hashlist_group');
    $agent = $this->createAgent('untrusted_but_allowed_agent', 0);
    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($otherHashlistGroup, $hashType);
    $taskWrapper = $this->createTaskWrapper($sharedTaskGroup, $hashlist);
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $task = $this->createTask($taskWrapper, $crackerBinary, $crackerBinaryType);

    $this->createDatabaseObject(
      Factory::getAccessGroupAgentFactory(),
      new AccessGroupAgent(null, $sharedTaskGroup->getId(), $agent->getId())
    );

    $this->assertFalse(AccessUtils::agentCanAccessTask($agent, $task));
  }

  public function testAgentCannotAccessTaskWhenFileIsSecretAndAgentIsNotTrusted(): void {
    $group = $this->createAccessGroup('secret_file_group');
    $agent = $this->createAgent('untrusted_file_agent', 0);
    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($group, $hashType);
    $taskWrapper = $this->createTaskWrapper($group, $hashlist);
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $task = $this->createTask($taskWrapper, $crackerBinary, $crackerBinaryType);
    $file = $this->createFile($group, 1);

    $this->createDatabaseObject(
      Factory::getAccessGroupAgentFactory(),
      new AccessGroupAgent(null, $group->getId(), $agent->getId())
    );
    $this->createFileTask($file, $task);

    $this->assertFalse(AccessUtils::agentCanAccessTask($agent, $task));
  }

  public function testAgentCanAccessTaskWhenWrapperHashlistAndFilesAreAllowed(): void {
    $group = $this->createAccessGroup('allowed_task_group');
    $agent = $this->createAgent('allowed_agent', 0);
    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($group, $hashType);
    $taskWrapper = $this->createTaskWrapper($group, $hashlist);
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $task = $this->createTask($taskWrapper, $crackerBinary, $crackerBinaryType);
    $file = $this->createFile($group);

    $this->createDatabaseObject(
      Factory::getAccessGroupAgentFactory(),
      new AccessGroupAgent(null, $group->getId(), $agent->getId())
    );
    $this->createFileTask($file, $task);

    $this->assertTrue(AccessUtils::agentCanAccessTask($agent, $task));
  }
  
  /*
   * Local test helpers
   */
  //TODO: Should we try refactor common methods to base?
  private function createAccessGroup(string $prefix): AccessGroup {
    $group = $this->createDatabaseObject(
      Factory::getAccessGroupFactory(),
      new AccessGroup(null, $prefix . '_' . uniqid())
    );
    $this->assertTrue($group instanceof AccessGroup);
    return $group;
  }

  private function createRightGroup(): RightGroup {
    $group = $this->createDatabaseObject(
      Factory::getRightGroupFactory(),
      new RightGroup(null, 'phpunit-' . uniqid('', true), '[]')
    );
    $this->assertTrue($group instanceof RightGroup);
    return $group;
  }

  private function createUser(string $prefix): User {
    $username = $prefix . '_' . uniqid();
    $user = UserUtils::createUser($username, $username . '@example.com', $this->createRightGroup()->getId(), $this->adminUser);
    $this->registerDatabaseObject(Factory::getUserFactory(), $user);
    return $user;
  }

  private function createHashType(): HashType {
    $hashType = $this->createDatabaseObject(
      Factory::getHashTypeFactory(),
      new HashType(null, 'hash_type_' . uniqid(), 0, 0)
    );
    $this->assertTrue($hashType instanceof HashType);
    return $hashType;
  }

  private function createHashlist(AccessGroup $group, HashType $hashType, int $isSecret = 0): Hashlist {
    $hashlist = $this->createDatabaseObject(
      Factory::getHashlistFactory(),
      new Hashlist(null, 'hashlist_' . uniqid(), DHashlistFormat::PLAIN, $hashType->getId(), 1, ':', 0, $isSecret, 0, 0, $group->getId(), '', 0, 0, 0)
    );
    $this->assertTrue($hashlist instanceof Hashlist);
    return $hashlist;
  }

  private function createTaskWrapper(AccessGroup $group, Hashlist $hashlist): TaskWrapper {
    $taskWrapper = $this->createDatabaseObject(
      Factory::getTaskWrapperFactory(),
      new TaskWrapper(null, 1, 1, 0, $hashlist->getId(), $group->getId(), 'wrapper_' . uniqid(), 0, 0)
    );
    $this->assertTrue($taskWrapper instanceof TaskWrapper);
    return $taskWrapper;
  }

  private function createCrackerBinaryType(): CrackerBinaryType {
    $crackerBinaryType = $this->createDatabaseObject(
      Factory::getCrackerBinaryTypeFactory(),
      new CrackerBinaryType(null, 'type_' . uniqid(), 1)
    );
    $this->assertTrue($crackerBinaryType instanceof CrackerBinaryType);
    return $crackerBinaryType;
  }

  private function createCrackerBinary(CrackerBinaryType $crackerBinaryType): CrackerBinary {
    $crackerBinary = $this->createDatabaseObject(
      Factory::getCrackerBinaryFactory(),
      new CrackerBinary(null, $crackerBinaryType->getId(), '1.0.' . uniqid(), 'https://example.invalid/' . uniqid(), 'binary_' . uniqid())
    );
    $this->assertTrue($crackerBinary instanceof CrackerBinary);
    return $crackerBinary;
  }

  private function createTask(TaskWrapper $taskWrapper, CrackerBinary $crackerBinary, CrackerBinaryType $crackerBinaryType): Task {
    $task = $this->createDatabaseObject(
      Factory::getTaskFactory(),
      new Task(null, 'task_' . uniqid(), '--attack-mode 0', 60, 30, 0, 0, 1, 1, '#ffffff', 0, 0, 0, 0, $crackerBinary->getId(), $crackerBinaryType->getId(), $taskWrapper->getId(), 0, '', 0, 0, 0, 0, '')
    );
    $this->assertTrue($task instanceof Task);
    return $task;
  }

  private function createFile(AccessGroup $group, int $isSecret = 0): File {
    $file = $this->createDatabaseObject(
      Factory::getFileFactory(),
      new File(null, 'file_' . uniqid(), 0, $isSecret, 0, $group->getId(), 0)
    );
    $this->assertTrue($file instanceof File);
    return $file;
  }

  private function createFileTask(File $file, Task $task): FileTask {
    $fileTask = $this->createDatabaseObject(
      Factory::getFileTaskFactory(),
      new FileTask(null, $file->getId(), $task->getId())
    );
    $this->assertTrue($fileTask instanceof FileTask);
    return $fileTask;
  }

  private function createAgent(string $prefix, int $isTrusted = 1): Agent {
    $suffix = uniqid();
    $agent = $this->createDatabaseObject(
      Factory::getAgentFactory(),
      new Agent(null, $prefix . '_' . $suffix, 'uid_' . $suffix, 0, '[]', '', 0, 1, $isTrusted, 'token_' . $suffix, 'idle', time(), '127.0.0.1', null, 0, 'sig_' . $suffix)
    );
    $this->assertTrue($agent instanceof Agent);
    return $agent;
  }
}