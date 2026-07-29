<?php

namespace Hashtopolis\inc\utils;

use Exception;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\TestBase;


require_once(dirname(__FILE__) . '/../../TestBase.php');

final class TaskUtilsTest extends TestBase {
  
  protected function setUp(): void {
    parent::setUp();
  }
  
  /**
   * Test editing the notes of a task.
   *
   * @return void
   * @throws Exception
   */
  public function testEditNotes(): void {
    $taskObjects = $this->createTaskHelper();

    TaskUtils::editNotes($taskObjects["task"]->getId(), 'task note', $taskObjects["user"]);
    
    $taskUpdated = Factory::getTaskFactory()->get($taskObjects["task"]->getId());
    $this->assertEquals('task note', $taskUpdated->getNotes());
  }

  /**
   * Test the status calculation of a task.
   *
   * @return void
   * @throws Exception
   */
  public function testGetStatus(): void {
    $taskObjects = $this->createTaskHelper();
    $this->assertEquals(2, TaskUtils::getStatus($taskObjects["task"]));
  }

  /**
   * A task with a recently dispatched, incomplete chunk is running (status 1).
   *
   * @return void
   * @throws Exception
   */
  public function testGetStatusRunning(): void {
    $taskObjects = $this->createTaskHelper();
    $agent = $this->createAgent('phpunit');
    $this->createChunk($taskObjects["task"], $agent, 0);

    $this->assertEquals(1, TaskUtils::getStatus($taskObjects["task"]));
  }

  /**
   * An archived task that is idle (no running chunks) should be skipped (status 4).
   * archiveTask() archives both the task and its taskWrapper.
   *
   * @return void
   * @throws Exception
   */
  public function testGetStatusSkippedWhenTaskArchived(): void {
    $taskObjects = $this->createTaskHelper();
    TaskUtils::archiveTask($taskObjects["task"]->getId(), $taskObjects["user"]);

    $this->assertEquals(4, TaskUtils::getStatus($taskObjects["task"]));
  }

  /**
   * A task whose taskWrapper is archived (but the task itself is not) should
   * also be skipped (status 4) when idle.
   *
   * @return void
   * @throws Exception
   */
  public function testGetStatusSkippedWhenOnlyTaskWrapperArchived(): void {
    $taskObjects = $this->createTaskHelper();
    Factory::getTaskWrapperFactory()->set($taskObjects["taskWrapper"], TaskWrapper::IS_ARCHIVED, 1);

    $this->assertEquals(4, TaskUtils::getStatus($taskObjects["task"]));
  }

  /**
   * An archived task that has a running chunk should still be running (status 1),
   * not skipped — the archived check only applies when the task is idle.
   *
   * @return void
   * @throws Exception
   */
  public function testGetStatusRunningNotSkippedWhenArchived(): void {
    $taskObjects = $this->createTaskHelper();
    $agent = $this->createAgent('phpunit');
    $this->createChunk($taskObjects["task"], $agent, 0);
    TaskUtils::archiveTask($taskObjects["task"]->getId(), $taskObjects["user"]);

    $this->assertEquals(1, TaskUtils::getStatus($taskObjects["task"]));
  }

  /**
   * An archived task that is fully completed (keyspace reached) should be
   * completed (status 3), not skipped — the archived check only applies to
   * idle tasks.
   *
   * @return void
   * @throws Exception
   */
  public function testGetStatusCompletedNotSkippedWhenArchived(): void {
    $taskObjects = $this->createTaskHelper();

    $agent = $this->createAgent('phpunit');
    $chunk = $this->createChunk($taskObjects["task"], $agent, 0);
    Factory::getChunkFactory()->mset($chunk, [Chunk::CHECKPOINT => 100, Chunk::PROGRESS => 10000]);
    Factory::getTaskFactory()->set($taskObjects["task"], Task::KEYSPACE, 100);

    TaskUtils::archiveTask($taskObjects["task"]->getId(), $taskObjects["user"]);

    $this->assertEquals(3, TaskUtils::getStatus($taskObjects["task"]));
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
    $taskObjects = $this->createTaskHelper();
    TaskUtils::changeAttackCmd($taskObjects["task"]->getId(), '#HL# custom attack cmd', $taskObjects["user"]);

    $taskUpdated = Factory::getTaskFactory()->get($taskObjects["task"]->getId());
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
    $taskObjects = $this->createTaskHelper();
    TaskUtils::archiveTask($taskObjects["task"]->getId(), $taskObjects["user"]);
    
    $taskWrapperUpdated = TaskUtils::getTaskWrapper($taskObjects["task"]->getTaskWrapperId(), $taskObjects["user"]);
    $this->assertEquals(1, $taskWrapperUpdated->getIsArchived());

    $taskUpdated = Factory::getTaskFactory()->get($taskObjects["task"]->getId());
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
    $taskObjects = $this->createTaskHelper();
    $this->assertEquals($taskObjects["task"]->getId(), TaskUtils::getTaskOfWrapper($taskObjects["taskWrapper"]->getId())->getId());
  }

  /**
   * Test getting tasks of wrapper.
   *
   * @return void
   * @throws Exception
   */
  /*public function testGetTasksOfWrapper(): void {
    //TODO create supertask
    $this->assertEquals(2, count(TaskUtils::getTasksOfWrapper($this->taskWrapper1->getId())));
  }*/

  /**
   * Test getting task wrappers for a user.
   *
   * @return void
   * @throws Exception
   */
  /*public function testGetTaskWrappersForUser(): void {
    $taskObjects = $this->createTaskHelper();
    $taskObjects2 = $this->createTaskHelper();
    
    $taskObjects2["taskWrapper"]->setAccessGroupId($taskObjects["accessGroup"]->getId());
    //$this->createAccessGroupUser($taskObjects2["user"], $taskObjects["accessGroup"]);
    
    //var_dump($taskObjects);
    //var_dump($taskObjects2);

    $this->assertEquals(2, count(TaskUtils::getTaskWrappersForUser($taskObjects["user"])));
  }*/


  /**
   * Test setting the CPU only flag for a task.
   *
   * @return void
   * @throws Exception
   */
  public function testSetCpuTask(): void {
    $taskObjects = $this->createTaskHelper();

    //Set to CPU-only
    TaskUtils::setCpuTask($taskObjects["task"]->getId(), 1, $taskObjects["user"]);
    $taskUpdated = Factory::getTaskFactory()->get($taskObjects["task"]->getId());
    $this->assertEquals(1, $taskUpdated->getIsCpuTask());

    //Set to use GPU and CPU
    TaskUtils::setCpuTask($taskObjects["task"]->getId(), 0, $taskObjects["user"]);
    $taskUpdated = Factory::getTaskFactory()->get($taskObjects["task"]->getId());
    $this->assertEquals(0, $taskUpdated->getIsCpuTask());
  }
}
