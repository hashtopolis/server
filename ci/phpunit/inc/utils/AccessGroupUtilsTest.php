<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\AccessGroupAgent;
use Hashtopolis\dba\models\AccessGroupUser;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DHashcatStatus;
use Hashtopolis\inc\defines\DLimits;
use Hashtopolis\inc\HTException;
use Hashtopolis\TestBase;
use Override;

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
    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($this->firstGroup, $hashType);
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

  public function testDeleteGroupReassignsDependentEntitiesToDefaultGroup(): void {
    $defaultGroup = AccessUtils::getOrCreateDefaultAccessGroup();
    $groupToDelete = $this->createAccessGroup('delete_group_');

    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $groupToDelete->getId(), $this->firstUser->getId()),
    );
    $this->createDatabaseObject(
      Factory::getAccessGroupAgentFactory(),
      new AccessGroupAgent(null, $groupToDelete->getId(), $this->firstAgent->getId()),
    );

    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($groupToDelete, $hashType);
    $taskWrapper = $this->createTaskWrapper($groupToDelete, $hashlist);
    $file = $this->createFile($groupToDelete);

    AccessGroupUtils::deleteGroup($groupToDelete->getId());

    $updatedHashlist = Factory::getHashlistFactory()->get($hashlist->getId());
    $updatedTaskWrapper = Factory::getTaskWrapperFactory()->get($taskWrapper->getId());
    $updatedFile = Factory::getFileFactory()->get($file->getId());
    $deletedGroup = Factory::getAccessGroupFactory()->get($groupToDelete->getId());
    $remainingUsers = AccessGroupUtils::getUsers($groupToDelete->getId());
    $remainingAgents = AccessGroupUtils::getAgents($groupToDelete->getId());

    $this->assertInstanceOf(Hashlist::class, $updatedHashlist);
    $this->assertSame($defaultGroup->getId(), $updatedHashlist->getAccessGroupId());
    $this->assertInstanceOf(TaskWrapper::class, $updatedTaskWrapper);
    $this->assertSame($defaultGroup->getId(), $updatedTaskWrapper->getAccessGroupId());
    $this->assertInstanceOf(File::class, $updatedFile);
    $this->assertSame($defaultGroup->getId(), $updatedFile->getAccessGroupId());
    $this->assertNull($deletedGroup);
    $this->assertSame([], $remainingUsers);
    $this->assertSame([], $remainingAgents);
  }
}