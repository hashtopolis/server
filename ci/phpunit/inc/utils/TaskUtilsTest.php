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

    //TODO test status 1 (running) and 3 (completed) too
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
  
  
  /**
   * Test cracking time aggregation for an agent on a task.
   *
   * @return void
   * @throws Exception
   */
  public function testCrackingTimeAggregation(): void {
    $task = $this->createTaskHelper()["task"];
    $agent1 = $this->createAgent("test");
    $agent2 = $this->createAgent("test");
    $timeSpans = (array) [
      [1000, 2000, $agent1],
      [3000, 5000, $agent2],
      [3000, 8000, $agent1],
      [8000, 10000, $agent1],
      [15000, 20000, $agent1],
    ];
    
    foreach ($timeSpans as [$start, $end, $agent]) {
      $chunk = $this->createChunk($task, $agent, 4);
      $chunk->setDispatchTime($start);
      $chunk->setSolveTime($end);
      Factory::getChunkFactory()->update($chunk);
    }
    
    // Calculate reference value via an interval merge algorithm
    $totalEnd = $referenceSum = 0;
    usort($timeSpans, fn($a, $b) => $a[0] <=> $b[0]); // Make sure time spans are sorted
    foreach ($timeSpans as [$currentStart, $currentEnd, $agent]) { // Expects list to be sorted by time
      if ($agent == $agent1) {
        $referenceSum = $referenceSum + ($currentEnd - $currentStart) // Add current time span to running total
          - max(0, $totalEnd - $currentStart) // Correct for potential overlapping when current start before previous end
          + max(0, $totalEnd - $currentEnd); // Correct for potential overcorrection when current end before previous end
        $totalEnd = max($totalEnd, $currentEnd); // Extend window if current end exceeds previous end
      }
    }
    
    // Calculate aggregate cracking time via TaskUtils
    $crackingTime = TaskUtils::getAggregateCrackingTime($agent1->getId(), $task->getId());
    
    $this->assertEquals($referenceSum, $crackingTime);
  }

  public function createTaskHelper(): array {
    $user = $this->createUser("phpunit");
    $accessGroup = $this->createAccessGroup("phpunit");
    $this->createAccessGroupUser($user, $accessGroup);

    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($accessGroup, $hashType);

    $taskWrapper = $this->createTaskWrapper($accessGroup, $hashlist);

    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $task = $this->createTask($taskWrapper, $crackerBinary, $crackerBinaryType);

    return array("user"=> $user, "accessGroup"=>$accessGroup, "hashType"=>$hashType, "hashlist"=>$hashlist, "taskWrapper"=>$taskWrapper, "crackerBinaryType"=>$crackerBinaryType, "crackerBinary"=>$crackerBinary, "task"=>$task);
  }
}
