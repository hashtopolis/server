<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

/**
 * Tests that a hashlist cannot be moved to another access group if one of its tasks
 * would then use a cracker binary of a different access group than the hashlist.
 */
final class HashlistUtilsTest extends TestBase {
  private AccessGroup $oldGroup;
  private AccessGroup $newGroup;
  private User $user;
  private CrackerBinaryType $type;
  private Hashlist $hashlist;

  protected function setUp(): void {
    parent::setUp();
    $this->oldGroup = $this->createAccessGroup('hashlist_move_old');
    $this->newGroup = $this->createAccessGroup('hashlist_move_new');
    $this->user = $this->createUser('hashlist_move_user');
    $this->createAccessGroupUser($this->user, $this->oldGroup);
    $this->createAccessGroupUser($this->user, $this->newGroup);
    $this->type = $this->createCrackerBinaryType();
    $this->hashlist = $this->createHashlist($this->oldGroup, $this->createHashType());
  }

  private function createTaskWithBinaryOfGroup(AccessGroup $group): void {
    $binary = $this->createCrackerBinary($this->type, $group->getId());
    $this->createTask($this->createTaskWrapper($this->oldGroup, $this->hashlist), $binary, $this->type);
  }

  public function testChangeAccessGroupWithoutTasks(): void {
    HashlistUtils::changeAccessGroup($this->hashlist->getId(), $this->newGroup->getId(), $this->user);

    $this->assertSame($this->newGroup->getId(), Factory::getHashlistFactory()->get($this->hashlist->getId())->getAccessGroupId());
  }

  public function testChangeAccessGroupWhenTaskBinaryIsInNewGroup(): void {
    $this->createTaskWithBinaryOfGroup($this->newGroup);

    HashlistUtils::changeAccessGroup($this->hashlist->getId(), $this->newGroup->getId(), $this->user);

    $this->assertSame($this->newGroup->getId(), Factory::getHashlistFactory()->get($this->hashlist->getId())->getAccessGroupId());
  }

  public function testChangeAccessGroupFailsWhenTaskBinaryIsInOtherGroup(): void {
    $this->createTaskWithBinaryOfGroup($this->oldGroup);

    try {
      HashlistUtils::changeAccessGroup($this->hashlist->getId(), $this->newGroup->getId(), $this->user);
      $this->fail('Expected HttpConflict when a task uses a binary of another access group');
    }
    catch (HttpConflict $e) {
      $this->assertStringContainsString('cracker binary of another access group', $e->getMessage());
    }

    // neither the hashlist nor its task wrapper were moved
    $this->assertSame($this->oldGroup->getId(), Factory::getHashlistFactory()->get($this->hashlist->getId())->getAccessGroupId());
    $qF = new QueryFilter(TaskWrapper::HASHLIST_ID, $this->hashlist->getId(), "=");
    $taskWrapper = Factory::getTaskWrapperFactory()->filter([Factory::FILTER => $qF], true);
    $this->assertSame($this->oldGroup->getId(), $taskWrapper->getAccessGroupId());
  }
}
