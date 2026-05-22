<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\inc\utils\UserUtils;
use Hashtopolis\inc\utils\TaskUtils;

use Exception;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\Hashlist;



use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\defines\DTaskTypes;

use Hashtopolis\TestBase;

//TODO remove:
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\dba\models\User;


require_once(dirname(__FILE__) . '/../../TestBase.php');

final class TaskUtilsTest extends TestBase {
  private RightGroup $rightGroup1;
  private AccessGroup $accessGroup1;
  private User $user1;
  private Hashlist $hashlist1;
  private TaskWrapper $taskWrapper1, $taskWrapper2, $taskWrapper3;
  private Task $task1, $task2, $task3;

  protected function setUp(): void {
    parent::setUp();

    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? 80;

    $this->rightGroup1 = $this->createRightGroup();

    $this->user1 = $this->createUser();
    $this->user1->setRightGroupId($this->rightGroup1->getId());

    $this->accessGroup1 = $this->createAccessGroup("1_");

    $this->hashlist1 = $this->createHashlist($this->accessGroup1->getId());

    $this->taskWrapper1 = $this->createTaskWrapper(99999, 0, 0, DTaskTypes::NORMAL, $this->hashlist1->getId());
    $this->taskWrapper1->setHashlistId($this->hashlist1->getId());
    $this->taskWrapper1->setAccessGroupId($this->accessGroup1->getId());

    $this->taskWrapper2 = $this->createTaskWrapper(99998, 0, 0, DTaskTypes::NORMAL, $this->hashlist1->getId());
    $this->taskWrapper2->setHashlistId($this->hashlist1->getId());
    $this->taskWrapper2->setAccessGroupId($this->accessGroup1->getId());

    $this->taskWrapper3 = $this->createTaskWrapper(99997, 0, 0, DTaskTypes::NORMAL, $this->hashlist1->getId());
    $this->taskWrapper3->setHashlistId($this->hashlist1->getId());
    $this->taskWrapper3->setAccessGroupId($this->accessGroup1->getId());

    $this->task1 = $this->createTask(99999, 'phpunit-' . uniqid(), '', 600, 5, 1000, 0, 0, 0, '', 0, 0, 1, 0, 1, 1, $this->taskWrapper1->getId());
    $this->task1->setTaskWrapperId($this->taskWrapper1->getId());

    $this->task2 = $this->createTask(99998, 'phpunit-' . uniqid(), '', 600, 5, 1000, 0, 0, 0, '', 0, 0, 1, 0, 1, 1, $this->taskWrapper1->getId());
    $this->task2->setTaskWrapperId($this->taskWrapper1->getId());

    $this->task3 = $this->createTask(99997, 'phpunit-' . uniqid(), '', 600, 5, 1000, 0, 0, 0, '', 0, 0, 1, 0, 1, 1, $this->taskWrapper3->getId());
    $this->task3->setTaskWrapperId($this->taskWrapper3->getId());
  }
  
  /**
   * Test editing the notes of a task.
   *
   * @return void
   * @throws Exception
   */
  public function testEditNotes(): void {
    TaskUtils::editNotes($this->task1->getId(), 'task note', $this->user1);
    
    $taskUpdated = Factory::getTaskFactory()->get($this->task1->getId());
    $this->assertEquals('task note', $taskUpdated->getNotes());
  }

  /**
   * Test the status calculation of a task.
   *
   * @return void
   * @throws Exception
   */
  public function testGetStatus(): void {
    $this->assertEquals(3, TaskUtils::getStatus([], 100, 100));
    $this->assertEquals(3, TaskUtils::getStatus([], 100, 101));

    //TODO test status 1 (running) and 2 (idle) too
  }

  /**
   * Test the deletion of archived tasks.
   *
   * @return void
   * @throws Exception
   */
  /*public function testDeleteArchived(): void {
    $this->task1->setIsArchived(1);

    //TODO filter for specific user too on $numberOfArchivedTasks and $numberOfArchivedTasksUpdated
    $numberOfArchivedTasks = Factory::getTaskFactory()->filter(['isArchived' => true, ]);
    
    TaskUtil::deleteArchived($this->user1);
    $numberOfArchivedTasksUpdated = Factory::getTaskFactory()->filter(['isArchived' => true, ]);

    $this->assertEquals(0, $numberOfArchivedTasksUpdated);
    $this->assertNotEquals($numberOfArchivedTasks, $numberOfArchivedTasksUpdated);
  }*/

  /**
   * Test changing the attack command.
   *
   * @return void
   * @throws Exception
   */
  public function testChangeAttackCmd(): void {
    TaskUtils::changeAttackCmd($this->task1->getId(), '#HL# custom attack cmd', $this->user1);

    $taskUpdated = Factory::getTaskFactory()->get($this->task1->getId());
    $this->assertEquals('#HL# custom attack cmd', $taskUpdated->getAttackCmd());
  }

  /**
   * Test archiving a supertask.
   *
   * @return void
   * @throws Exception
   */
  /*public function testArchiveSupertask(): void {
    $supertask;
    $supertaskWrapper;
    $user;

    TaskUtils::archiveSupertask($supertask->getId(), $user);
    
    //TODO filter all task wrappers with the id of the $supertaskWrapper (using taskfactory?) and check if they're archived

    $supertaskWrapperUpdated = Factory::getTaskWrapperFactory()->get($supertaskWrapper);
    $this->assertEquals(1, $supertaskWrapperUpdated->getIsArchived());
  }*/

  /**
   * Test archiving a task.
   *
   * @return void
   * @throws Exception
   */
  public function testArchiveTask(): void {
    TaskUtils::archiveTask($this->task1->getId(), $this->user1);
    
    $taskWrapperUpdated = TaskUtils::getTaskWrapper($this->task1->getTaskWrapperId(), $this->user1);
    $this->assertEquals(1, $taskWrapperUpdated->getIsArchived());

    $taskUpdated = Factory::getTaskFactory()->get($this->task1->getId());
    $this->assertEquals(1, $taskUpdated->getIsArchived());
  }

  /**
   * Test toggle of archiving a normal task and a supertask.
   *
   * @return void
   * @throws Exception
   */
  /*public function testToggleArchiveTask(): void {
    $task;
    $taskTaskWrapper;
    $supertask;
    $supertaskWrapper;
    $user;

    //Archive task
    TaskUtils::toggleArchiveTask($task->getId(), 1, $user);
    
    $taskWrapperUpdated = TaskUtils::getTaskWrapper($task->getTaskWrapperId(), $user);
    $this->assertEquals(1, $taskWrapperUpdated->getIsArchived());

    $taskUpdated = Factory::getTaskFactory()->get($task->getId());
    $this->assertEquals(1, $taskUpdated->getIsArchived());


    //Un-archive task again
    TaskUtils::toggleArchiveTask($task->getId(), 0, $user);
    
    $taskWrapperUpdated = TaskUtils::getTaskWrapper($task->getTaskWrapperId(), $user);
    $this->assertEquals(0, $taskWrapperUpdated->getIsArchived());

    $taskUpdated = Factory::getTaskFactory()->get($task->getId());
    $this->assertEquals(0, $taskUpdated->getIsArchived());


    //Archive supertask
    TaskUtils::toggleArchiveTask($supertask->getId(), 1, $user);
    
    //TODO filter all task wrappers with the id of the $supertaskWrapper (using taskfactory?) and check if they're archived

    $supertaskWrapperUpdated = Factory::getTaskWrapperFactory()->get($supertaskWrapper);
    $this->assertEquals(1, $supertaskWrapperUpdated->getIsArchived());


    //Un-archive supertask again
    TaskUtils::toggleArchiveTask($supertask->getId(), 0, $user);
    
    //TODO filter all task wrappers with the id of the $supertaskWrapper (using taskfactory?) and check if they're archived

    $supertaskWrapperUpdated = Factory::getTaskWrapperFactory()->get($supertaskWrapper);
    $this->assertEquals(0, $supertaskWrapperUpdated->getIsArchived());
  }*/

  /**
   * Test renaming a running supertask.
   *
   * @return void
   * @throws Exception
   */
  /*public function testRenameSupertask(): void {
    $supertask;
    $supertaskWrapper;
    $user;

    TaskUtils::renameSupertask($supertaskWrapper->getId(), 'custom new supertask name', $user);

    $supertaskWrapperUpdated = TaskUtils::getTaskWrapper($supertaskWrapper->getId(), $user);
    $this->assertEquals('custom new supertask name', $supertaskWrapperUpdated->getTaskWrapperName());
  }*/


  /**
   * Test getting the task of wrapper.
   *
   * @return void
   * @throws Exception
   */
  public function testGetTaskOfWrapper(): void {
    $this->assertEquals($this->task3->getId(), TaskUtils::getTaskOfWrapper($this->taskWrapper3->getId())->getId());
  }

  /**
   * Test getting tasks of wrapper.
   *
   * @return void
   * @throws Exception
   */
  public function testGetTasksOfWrapper(): void {
    $this->assertEquals(2, count(TaskUtils::getTasksOfWrapper($this->taskWrapper1->getId())));
  }

  /**
   * Test getting task wrappers for a user.
   *
   * @return void
   * @throws Exception
   */
  public function testGetTaskWrappersForUser(): void {
    $this->assertEquals(3, count(TaskUtils::getTaskWrappersForUser($this->user1)));
  }


  /**
   * Test setting the CPU only flag for a task.
   *
   * @return void
   * @throws Exception
   */
  public function testSetCpuTask(): void {
    //Set to CPU-only
    TaskUtils::setCpuTask($this->task1->getId(), 1, $this->user1);
    $taskUpdated = Factory::getTaskFactory()->get($this->task1->getId());
    $this->assertEquals(1, $taskUpdated->getIsCpuTask());

    //Set to use GPU and CPU
    TaskUtils::setCpuTask($this->task1->getId(), 0, $this->user1);
    $taskUpdated = Factory::getTaskFactory()->get($this->task1->getId());
    $this->assertEquals(0, $taskUpdated->getIsCpuTask());
  }







  



  //TODO write more functions for creating test data like task wrappers, chunks, ...
  //TODO use createObjectFromDict like functionality to create test data for more flexibility?

  public function createTask($taskId = 99999, $taskName = 'phpunit-task1', $attackCmd = '', $chunkTime = 600, $statusTimer = 5, $keyspace = 1000, $keyspaceProgress = 0, $priority = 0, $maxAgents = 0, $color = '', $isSmall = 0, $isCpuTask = 0, $useNewBench = 1, $skipKeyspace = 0, $crackerBinaryId = 1, $crackerBinaryTypeId = 1, $taskWrapperId = 999, $isArchived = 0, $notes = '', $staticChunks = 0, $chunkSize = 0, $forcePipe = 0, $usePreprocessor = 1, $preprocessorCommand = ''): Task {
    $task = $this->createDatabaseObject(Factory::getTaskFactory(), new Task($taskId, $taskName, $attackCmd, $chunkTime, $statusTimer, $keyspace, $keyspaceProgress, $priority, $maxAgents, $color, $isSmall, $isCpuTask, $useNewBench, $skipKeyspace, $crackerBinaryId, $crackerBinaryTypeId, $taskWrapperId, $isArchived, $notes, $staticChunks, $chunkSize, $forcePipe, $usePreprocessor, $preprocessorCommand));
    $this->assertTrue($task instanceof Task);
    return $task;
  }

  public function createTaskWrapper($taskWrapperId = 99999, $priority = 0, $maxAgents = 0, $taskType = DTaskTypes::NORMAL, $hashlistId = 1, $accessGroupId = 1, $taskWrapperName = 'phpunit-taskwrapper1', $isArchived = 0, $cracked = 0): TaskWrapper {
    $taskWrapper = $this->createDatabaseObject(Factory::getTaskWrapperFactory(), new TaskWrapper($taskWrapperId, $priority, $maxAgents, $taskType, $hashlistId, $accessGroupId, $taskWrapperName, $isArchived, $cracked));
    $this->assertTrue($taskWrapper instanceof TaskWrapper);
    return $taskWrapper;
  }

  //TODO make use of the hashlist-create function that will be in the HashlistUtilsTest
  public function createHashlist($accessGroupId): Hashlist {
    $hashlist = $this->createDatabaseObject(Factory::getHashlistFactory(), new Hashlist(null, 'phpunit-' . uniqid(), DHashlistFormat::PLAIN, 0, 1, '', 0, 0, 0, 0, $accessGroupId, '', 0, 0, 0));
    $this->assertTrue($hashlist instanceof Hashlist);
    return $hashlist;
  }

  //TODO make use of the user-create function that will be in the UserUtilsTest
  public function createUser(): User {
    $user = UserUtils::createUser('phpunit-' . uniqid(), 'phpunit-' . uniqid() . '@example.com', 1, UserUtils::getUser(1));
    $this->registerDatabaseObject(Factory::getUserFactory(), $user);
    return $user;
  }

  //TODO make use of the create function that will be in the AccessGroupUtilsTest
  public function createAccessGroup(string $prefix): AccessGroup {
    $group = $this->createDatabaseObject(
      Factory::getAccessGroupFactory(),
      new AccessGroup(null, $prefix . '_' . uniqid())
    );
    $this->assertTrue($group instanceof AccessGroup);
    return $group;
  }

  //TODO make use of the rightgroup-create function that will be in the UserUtilsTest
  public function createRightGroup(): RightGroup {
    $group = $this->createDatabaseObject(Factory::getRightGroupFactory(), new RightGroup(null, 'phpunit-' . uniqid('', true), '[]'));
    $this->assertTrue($group instanceof RightGroup);
    return $group;
  }
}
