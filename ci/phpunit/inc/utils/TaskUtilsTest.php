<?php

namespace Hashtopolis\inc\utils;

use Exception;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;

use Hashtopolis\inc\defines\DTaskTypes;
use Hashtopolis\TestBase;

//TODO remove:
use Hashtopolis\dba\models\User;


require_once(dirname(__FILE__) . '/../../TestBase.php');

final class TaskUtilsTest extends TestBase {
  private User $user1;
  private TaskWrapper $taskWrapper1, $taskWrapper2, $taskWrapper3;
  private Task $task1, $task2, $task3;

  protected function setUp(): void {
    parent::setUp();

    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? 80;

    $this->user1 = $this->createUser("task_utils_test");
    $accessGroup1 = $this->createAccessGroup("task_utils_test");
    $this->createAccessGroupUser($this->user1, $accessGroup1);
    
    $hashtype = $this->createHashtype();
    $hashlist1 = $this->createHashlist($accessGroup1, $hashtype);

    $this->taskWrapper1 = $this->createTaskWrapper($accessGroup1, $hashlist1, DTaskTypes::SUPERTASK);

    $this->taskWrapper2 = $this->createTaskWrapper($accessGroup1, $hashlist1);

    $this->taskWrapper3 = $this->createTaskWrapper($accessGroup1, $hashlist1);
    
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    
    $this->task1 = $this->createTask($this->taskWrapper1, $crackerBinary, $crackerBinaryType);

    $this->task2 = $this->createTask($this->taskWrapper1, $crackerBinary, $crackerBinaryType);

    $this->task3 = $this->createTask($this->taskWrapper3, $crackerBinary, $crackerBinaryType);
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
    TaskUtils::archiveTask($this->task3->getId(), $this->user1);
    
    $taskWrapperUpdated = TaskUtils::getTaskWrapper($this->task3->getTaskWrapperId(), $this->user1);
    $this->assertEquals(1, $taskWrapperUpdated->getIsArchived());

    $taskUpdated = Factory::getTaskFactory()->get($this->task3->getId());
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
}
