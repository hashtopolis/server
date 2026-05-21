<?php

namespace inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\models\AccessGroupUser;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DHashcatStatus;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\defines\DLimits;
use Hashtopolis\inc\defines\DTaskTypes;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\inc\utils\AccessGroupUtils;
use Hashtopolis\inc\utils\UserUtils;
use Override;
use TestBase;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class AccessGroupUtilsTest extends TestBase {
  private AccessGroup $firstGroup;
  private AccessGroup $secondGroup;
  private User $firstUser;
  private User $secondUser;
  private Agent $firstAgent;
  private Agent $secondAgent;

  #[Override]
  protected function setUp(): void {
    parent::setUp();

    $this->firstGroup = $this->createAccessGroup('group_one');
    $this->secondGroup = $this->createAccessGroup('group_two');
    $this->firstUser = $this->createUser('user_one');
    $this->secondUser = $this->createUser('user_two');
    $this->firstAgent = $this->createAgent('agent_one');
    $this->secondAgent = $this->createAgent('agent_two');

    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $this->firstGroup->getId(), $this->firstUser->getId())
    );
    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $this->secondGroup->getId(), $this->secondUser->getId())
    );
    $this->createDatabaseObject(
      Factory::getAccessGroupAgentFactory(),
      new AccessGroupAgent(null, $this->firstGroup->getId(), $this->firstAgent->getId())
    );
    $this->createDatabaseObject(
      Factory::getAccessGroupAgentFactory(),
      new AccessGroupAgent(null, $this->secondGroup->getId(), $this->secondAgent->getId())
    );
  }

  #[Override]
  protected function tearDown(): void {
    parent::tearDown();
  }

  public function testGetUsersReturnsUsersAssignedToRequestedGroup(): void {
    $users = AccessGroupUtils::getUsers($this->firstGroup->getId());

    $this->assertCount(1, $users);
    $this->assertSame($this->firstGroup->getId(), $users[0]->getAccessGroupId());
    $this->assertSame($this->firstUser->getId(), $users[0]->getUserId());
  }

  public function testGetAgentsReturnsAgentsAssignedToRequestedGroup(): void {
    $agents = AccessGroupUtils::getAgents($this->firstGroup->getId());

    $this->assertCount(1, $agents);
    $this->assertSame($this->firstGroup->getId(), $agents[0]->getAccessGroupId());
    $this->assertSame($this->firstAgent->getId(), $agents[0]->getAgentId());
  }

  public function testGetGroupsReturnsAtLeastCreatedGroups(): void {
    $groups = AccessGroupUtils::getGroups();
    $groupIds = array_map(
      fn (AccessGroup $group) => $group->getId(),
      $groups
    );

    $this->assertContains($this->firstGroup->getId(), $groupIds);
    $this->assertContains($this->secondGroup->getId(), $groupIds);
  }

  public function testCreateGroupThrowsForEmptyName(): void {
    $this->expectException(HttpError::class);
    AccessGroupUtils::createGroup('');
  }

  public function testCreateGroupThrowsForNameLongerThanMaxLength(): void {
    $this->expectException(HttpError::class);
    AccessGroupUtils::createGroup(str_repeat('a', DLimits::ACCESS_GROUP_MAX_LENGTH + 1));
  }

  public function testCreateGroupThrowsForExistingGroupName(): void {
    $this->expectException(HttpConflict::class);
    AccessGroupUtils::createGroup($this->firstGroup->getGroupName());
  }

  public function testCreateGroupCreatesGroupWithValidUniqueName(): void {
    $groupName = 'created_group_' . uniqid();

    $group = AccessGroupUtils::createGroup($groupName);
    $this->registerDatabaseObject(Factory::getAccessGroupFactory(), $group);

    $this->assertInstanceOf(AccessGroup::class, $group);
    $this->assertSame($groupName, $group->getGroupName());
    $this->assertNotNull($group->getId());
    $this->assertSame($groupName, Factory::getAccessGroupFactory()->get($group->getId())->getGroupName());
  }

  public function testRenameThrowsForNonExistentGroup(): void {
    $this->expectException(HTException::class);
    AccessGroupUtils::rename(-1, 'renamed_group');
  }

  public function testRenameThrowsForEmptyName(): void {
    $this->expectException(HTException::class);
    AccessGroupUtils::rename($this->firstGroup->getId(), '');
  }

  public function testAbortChunksGroupThrowsForNonExistentGroup(): void {
    $this->expectException(HTException::class);
    AccessGroupUtils::abortChunksGroup(-1, $this->firstUser);
  }

  public function testAbortChunksGroupOnlyAbortsInitAndRunningChunks(): void {
    $hashlist = $this->createHashlist($this->firstGroup);
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $taskWrapper = $this->createTaskWrapper($this->firstGroup, $hashlist);
    $task = $this->createTask($taskWrapper, $crackerBinary, $crackerBinaryType);
    $statusCases = [
      DHashcatStatus::INIT => DHashcatStatus::ABORTED,
      DHashcatStatus::AUTOTUNE => DHashcatStatus::AUTOTUNE,
      DHashcatStatus::RUNNING => DHashcatStatus::ABORTED,
      DHashcatStatus::PAUSED => DHashcatStatus::PAUSED,
      DHashcatStatus::EXHAUSTED => DHashcatStatus::EXHAUSTED,
      DHashcatStatus::CRACKED => DHashcatStatus::CRACKED,
      DHashcatStatus::ABORTED => DHashcatStatus::ABORTED,
      DHashcatStatus::QUIT => DHashcatStatus::QUIT,
      DHashcatStatus::BYPASS => DHashcatStatus::BYPASS,
      DHashcatStatus::ABORTED_CHECKPOINT => DHashcatStatus::ABORTED_CHECKPOINT,
      DHashcatStatus::STATUS_ABORTED_RUNTIME => DHashcatStatus::STATUS_ABORTED_RUNTIME,
    ];


    $chunksByState = [];
    foreach ($statusCases as $initialState => $expectedState) {
      $chunksByState[$initialState] = $this->createChunk($task, $this->firstAgent, $initialState);
    }

    AccessGroupUtils::abortChunksGroup($this->firstGroup->getId(), $this->firstUser);

    foreach ($statusCases as $initialState => $expectedState) {
      $updatedChunk = Factory::getChunkFactory()->get($chunksByState[$initialState]->getId());

      $this->assertInstanceOf(Chunk::class, $updatedChunk);
      $this->assertSame($expectedState, $updatedChunk->getState());
    }
  }

  public function testAddAgentAddsAgentToGroup(): void {
    AccessGroupUtils::addAgent($this->secondAgent->getId(), $this->firstGroup->getId());

    $qF1 = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $this->firstGroup->getId(), '=');
    $qF2 = new QueryFilter(AccessGroupAgent::AGENT_ID, $this->secondAgent->getId(), '=');
    $addedMembership = Factory::getAccessGroupAgentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);

    $this->assertInstanceOf(AccessGroupAgent::class, $addedMembership);
    $this->assertSame($this->firstGroup->getId(), $addedMembership->getAccessGroupId());
    $this->assertSame($this->secondAgent->getId(), $addedMembership->getAgentId());
    $this->registerDatabaseObject(Factory::getAccessGroupAgentFactory(), $addedMembership);
  }

  public function testAddAgentThrowsWhenAgentAlreadyInGroup(): void {
    $this->expectException(HTException::class);
    AccessGroupUtils::addAgent($this->firstAgent->getId(), $this->firstGroup->getId());
  }

  public function testAddUserAddsUserToGroup(): void {
    AccessGroupUtils::addUser($this->secondUser->getId(), $this->firstGroup->getId());

    $qF1 = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $this->firstGroup->getId(), '=');
    $qF2 = new QueryFilter(AccessGroupUser::USER_ID, $this->secondUser->getId(), '=');
    $addedMembership = Factory::getAccessGroupUserFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);

    $this->assertInstanceOf(AccessGroupUser::class, $addedMembership);
    $this->assertSame($this->firstGroup->getId(), $addedMembership->getAccessGroupId());
    $this->assertSame($this->secondUser->getId(), $addedMembership->getUserId());
    $this->registerDatabaseObject(Factory::getAccessGroupUserFactory(), $addedMembership);
  }

  public function testAddUserThrowsWhenUserAlreadyInGroup(): void {
    $this->expectException(HTException::class);
    AccessGroupUtils::addUser($this->firstUser->getId(), $this->firstGroup->getId());
  }

  public function testRemoveAgentRemovesAgentFromGroup(): void {
    AccessGroupUtils::removeAgent($this->firstAgent->getId(), $this->firstGroup->getId());

    $qF1 = new QueryFilter(AccessGroupAgent::ACCESS_GROUP_ID, $this->firstGroup->getId(), '=');
    $qF2 = new QueryFilter(AccessGroupAgent::AGENT_ID, $this->firstAgent->getId(), '=');
    $removedMembership = Factory::getAccessGroupAgentFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);

    $this->assertNull($removedMembership);
  }

  public function testRemoveAgentThrowsWhenAgentIsNotInGroup(): void {
    $this->expectException(HTException::class);
    AccessGroupUtils::removeAgent($this->secondAgent->getId(), $this->firstGroup->getId());
  }

  public function testRemoveUserRemovesUserFromGroup(): void {
    AccessGroupUtils::removeUser($this->firstUser->getId(), $this->firstGroup->getId());

    $qF1 = new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $this->firstGroup->getId(), '=');
    $qF2 = new QueryFilter(AccessGroupUser::USER_ID, $this->firstUser->getId(), '=');
    $removedMembership = Factory::getAccessGroupUserFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);

    $this->assertNull($removedMembership);
  }

  public function testRemoveUserThrowsWhenUserIsNotInGroup(): void {
    $this->expectException(HTException::class);
    AccessGroupUtils::removeUser($this->secondUser->getId(), $this->firstGroup->getId());
  }

  public function testDeleteGroupThrowsForDefaultGroup(): void {
    $defaultGroup = AccessUtils::getOrCreateDefaultAccessGroup();

    $this->expectException(HTException::class);
    AccessGroupUtils::deleteGroup($defaultGroup->getId());
  }

  
  

  

  
  /*
   * Local test helpers
   */
  private function createAccessGroup(string $prefix): AccessGroup {
    $group = $this->createDatabaseObject(
      Factory::getAccessGroupFactory(),
      new AccessGroup(null, $prefix . '_' . uniqid())
    );
    $this->assertTrue($group instanceof AccessGroup);
    return $group;
  }

  private function createAgent(string $prefix): Agent {
    $suffix = uniqid('', true);
    $agent = $this->createDatabaseObject(
      Factory::getAgentFactory(),
      new Agent(null, $prefix . '_' . $suffix, 'uid_' . $suffix, 0, '[]', '', 0, 1, 1, 'token_' . uniqid(), 'idle', time(), '127.0.0.1', null, 0, 'sig_' . uniqid())
    );
    $this->assertTrue($agent instanceof Agent);
    return $agent;
  }

  private function createRightGroup(): RightGroup {
    $group = $this->createDatabaseObject(Factory::getRightGroupFactory(), new RightGroup(null, 'phpunit-' . uniqid('', true), '[]'));
    $this->assertTrue($group instanceof RightGroup);
    return $group;
  }

  private function createUser(string $prefix): User {
    $username = $prefix . '_' . uniqid();
    $user = UserUtils::createUser($username, $username . '@example.com', $this->createRightGroup()->getId(), $this->adminUser);
    $this->registerDatabaseObject(Factory::getUserFactory(), $user);
    return $user;
  }

  private function createHashlist(AccessGroup $group): Hashlist {
    $hashlist = $this->createDatabaseObject(
      Factory::getHashlistFactory(),
      new Hashlist(null, 'hashlist_' . uniqid(), DHashlistFormat::PLAIN, 0, 1, ':', 0, 0, 0, 0, $group->getId(), '', 0, 0, 0)
    );
    $this->assertTrue($hashlist instanceof Hashlist);
    return $hashlist;
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

  private function createTaskWrapper(AccessGroup $group, Hashlist $hashlist): TaskWrapper {
    $taskWrapper = $this->createDatabaseObject(
      Factory::getTaskWrapperFactory(),
      new TaskWrapper(null, 1, 1, DTaskTypes::NORMAL, $hashlist->getId(), $group->getId(), 'wrapper_' . uniqid(), 0, 0)
    );
    $this->assertTrue($taskWrapper instanceof TaskWrapper);
    return $taskWrapper;
  }

  private function createTask(TaskWrapper $taskWrapper, CrackerBinary $crackerBinary, CrackerBinaryType $crackerBinaryType): Task {
    $task = $this->createDatabaseObject(
      Factory::getTaskFactory(),
      new Task(null, 'task_' . uniqid(), '--attack-mode 0', 60, 30, 0, 0, 1, 1, '#ffffff', 0, 0, 0, 0, $crackerBinary->getId(), $crackerBinaryType->getId(), $taskWrapper->getId(), 0, '', 0, 0, 0, 0, '')
    );
    $this->assertTrue($task instanceof Task);
    return $task;
  }

  private function createChunk(Task $task, Agent $agent, int $state): Chunk {
    $chunk = $this->createDatabaseObject(
      Factory::getChunkFactory(),
      new Chunk(null, $task->getId(), 0, 100, $agent->getId(), time(), 0, 0, 0, $state, 0, 0)
    );
    $this->assertTrue($chunk instanceof Chunk);
    return $chunk;
  }
}