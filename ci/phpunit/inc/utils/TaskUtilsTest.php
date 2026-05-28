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

  /**
   * Builds the common fixtures used by the findCompletedEquivalent tests:
   * an access group, a hashlist, a (normal) task wrapper on that hashlist, a
   * cracker binary + type and a file.
   *
   * @return array
   * @throws Exception
   */
  private function findCompletedSetup(): array {
    $accessGroup = $this->createAccessGroup("phpunit");
    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($accessGroup, $hashType);
    $taskWrapper = $this->createTaskWrapper($accessGroup, $hashlist);
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $file = $this->createFile($accessGroup);
    return array(
      "accessGroup" => $accessGroup,
      "hashType" => $hashType,
      "hashlist" => $hashlist,
      "taskWrapper" => $taskWrapper,
      "crackerBinaryType" => $crackerBinaryType,
      "crackerBinary" => $crackerBinary,
      "file" => $file,
    );
  }

  /**
   * Creates a task on the given wrapper with a specific attack command, file set and
   * keyspace state, used to stand in for an already-completed task.
   *
   * @param TaskWrapper $taskWrapper
   * @param mixed $crackerBinary
   * @param mixed $crackerBinaryType
   * @param string $attackCmd
   * @param array $files
   * @param int $keyspace
   * @param int $keyspaceProgress
   * @param int $isArchived
   * @return Task
   * @throws Exception
   */
  private function makeCompletedTask($taskWrapper, $crackerBinary, $crackerBinaryType, string $attackCmd, array $files, int $keyspace = 1000, int $keyspaceProgress = 1000, int $isArchived = 0): Task {
    $task = $this->createTask($taskWrapper, $crackerBinary, $crackerBinaryType);
    Factory::getTaskFactory()->mset($task, [
      Task::ATTACK_CMD => $attackCmd,
      Task::KEYSPACE => $keyspace,
      Task::KEYSPACE_PROGRESS => $keyspaceProgress,
      Task::IS_ARCHIVED => $isArchived,
    ]);
    foreach ($files as $file) {
      $this->createFileTask($file, $task);
    }
    return Factory::getTaskFactory()->get($task->getId());
  }

  /**
   * A fully exhausted task with a matching attack command, file set and cracker is found.
   *
   * @return void
   * @throws Exception
   */
  public function testFindCompletedEquivalentMatches(): void {
    $s = $this->findCompletedSetup();
    $task = $this->makeCompletedTask($s["taskWrapper"], $s["crackerBinary"], $s["crackerBinaryType"], "#HL# -a 0 dict.txt", [$s["file"]]);

    $match = TaskUtils::findCompletedEquivalent(
      $s["hashlist"]->getId(),
      "#HL# -a 0 dict.txt",
      [$s["file"]->getId()],
      $s["crackerBinary"]->getId(),
      $s["crackerBinaryType"]->getId()
    );

    $this->assertNotNull($match);
    $this->assertEquals($task->getId(), $match->getId());
  }

  /**
   * A task with no files matches a spec with no files.
   *
   * @return void
   * @throws Exception
   */
  public function testFindCompletedEquivalentNoFilesMatches(): void {
    $s = $this->findCompletedSetup();
    $task = $this->makeCompletedTask($s["taskWrapper"], $s["crackerBinary"], $s["crackerBinaryType"], "#HL# -a 3 ?d?d?d?d", []);

    $match = TaskUtils::findCompletedEquivalent(
      $s["hashlist"]->getId(),
      "#HL# -a 3 ?d?d?d?d",
      [],
      $s["crackerBinary"]->getId(),
      $s["crackerBinaryType"]->getId()
    );

    $this->assertNotNull($match);
    $this->assertEquals($task->getId(), $match->getId());
  }

  /**
   * An archived but fully exhausted task still counts as a match.
   *
   * @return void
   * @throws Exception
   */
  public function testFindCompletedEquivalentArchivedMatches(): void {
    $s = $this->findCompletedSetup();
    $task = $this->makeCompletedTask($s["taskWrapper"], $s["crackerBinary"], $s["crackerBinaryType"], "#HL# -a 0 dict.txt", [$s["file"]], 1000, 1000, 1);

    $match = TaskUtils::findCompletedEquivalent(
      $s["hashlist"]->getId(),
      "#HL# -a 0 dict.txt",
      [$s["file"]->getId()],
      $s["crackerBinary"]->getId(),
      $s["crackerBinaryType"]->getId()
    );

    $this->assertNotNull($match);
    $this->assertEquals($task->getId(), $match->getId());
  }

  /**
   * Whitespace differences in the attack command are normalized away.
   *
   * @return void
   * @throws Exception
   */
  public function testFindCompletedEquivalentAttackCmdWhitespaceNormalized(): void {
    $s = $this->findCompletedSetup();
    $task = $this->makeCompletedTask($s["taskWrapper"], $s["crackerBinary"], $s["crackerBinaryType"], "#HL#   -a    0   dict.txt", [$s["file"]]);

    $match = TaskUtils::findCompletedEquivalent(
      $s["hashlist"]->getId(),
      "#HL# -a 0 dict.txt",
      [$s["file"]->getId()],
      $s["crackerBinary"]->getId(),
      $s["crackerBinaryType"]->getId()
    );

    $this->assertNotNull($match);
    $this->assertEquals($task->getId(), $match->getId());
  }

  /**
   * A partially completed task (progress below keyspace) is not a match.
   *
   * @return void
   * @throws Exception
   */
  public function testFindCompletedEquivalentPartialKeyspaceNoMatch(): void {
    $s = $this->findCompletedSetup();
    $this->makeCompletedTask($s["taskWrapper"], $s["crackerBinary"], $s["crackerBinaryType"], "#HL# -a 0 dict.txt", [$s["file"]], 1000, 500);

    $match = TaskUtils::findCompletedEquivalent(
      $s["hashlist"]->getId(),
      "#HL# -a 0 dict.txt",
      [$s["file"]->getId()],
      $s["crackerBinary"]->getId(),
      $s["crackerBinaryType"]->getId()
    );

    $this->assertNull($match);
  }

  /**
   * A task with keyspace 0 (never measured) is not a match.
   *
   * @return void
   * @throws Exception
   */
  public function testFindCompletedEquivalentZeroKeyspaceNoMatch(): void {
    $s = $this->findCompletedSetup();
    $this->makeCompletedTask($s["taskWrapper"], $s["crackerBinary"], $s["crackerBinaryType"], "#HL# -a 0 dict.txt", [$s["file"]], 0, 0);

    $match = TaskUtils::findCompletedEquivalent(
      $s["hashlist"]->getId(),
      "#HL# -a 0 dict.txt",
      [$s["file"]->getId()],
      $s["crackerBinary"]->getId(),
      $s["crackerBinaryType"]->getId()
    );

    $this->assertNull($match);
  }

  /**
   * A different cracker binary invalidates the match.
   *
   * @return void
   * @throws Exception
   */
  public function testFindCompletedEquivalentDifferentCrackerNoMatch(): void {
    $s = $this->findCompletedSetup();
    $this->makeCompletedTask($s["taskWrapper"], $s["crackerBinary"], $s["crackerBinaryType"], "#HL# -a 0 dict.txt", [$s["file"]]);
    $otherBinary = $this->createCrackerBinary($s["crackerBinaryType"]);

    $match = TaskUtils::findCompletedEquivalent(
      $s["hashlist"]->getId(),
      "#HL# -a 0 dict.txt",
      [$s["file"]->getId()],
      $otherBinary->getId(),
      $s["crackerBinaryType"]->getId()
    );

    $this->assertNull($match);
  }

  /**
   * A different file set invalidates the match.
   *
   * @return void
   * @throws Exception
   */
  public function testFindCompletedEquivalentDifferentFilesetNoMatch(): void {
    $s = $this->findCompletedSetup();
    $this->makeCompletedTask($s["taskWrapper"], $s["crackerBinary"], $s["crackerBinaryType"], "#HL# -a 0 dict.txt", [$s["file"]]);
    $otherFile = $this->createFile($s["accessGroup"]);

    $match = TaskUtils::findCompletedEquivalent(
      $s["hashlist"]->getId(),
      "#HL# -a 0 dict.txt",
      [$otherFile->getId()],
      $s["crackerBinary"]->getId(),
      $s["crackerBinaryType"]->getId()
    );

    $this->assertNull($match);
  }

  /**
   * A different attack command invalidates the match.
   *
   * @return void
   * @throws Exception
   */
  public function testFindCompletedEquivalentDifferentAttackCmdNoMatch(): void {
    $s = $this->findCompletedSetup();
    $this->makeCompletedTask($s["taskWrapper"], $s["crackerBinary"], $s["crackerBinaryType"], "#HL# -a 0 dict.txt", [$s["file"]]);

    $match = TaskUtils::findCompletedEquivalent(
      $s["hashlist"]->getId(),
      "#HL# -a 3 ?d?d?d?d",
      [$s["file"]->getId()],
      $s["crackerBinary"]->getId(),
      $s["crackerBinaryType"]->getId()
    );

    $this->assertNull($match);
  }

  /**
   * An identical completed task on a different hashlist is not a match.
   *
   * @return void
   * @throws Exception
   */
  public function testFindCompletedEquivalentDifferentHashlistNoMatch(): void {
    $s = $this->findCompletedSetup();
    $this->makeCompletedTask($s["taskWrapper"], $s["crackerBinary"], $s["crackerBinaryType"], "#HL# -a 0 dict.txt", [$s["file"]]);
    $otherHashlist = $this->createHashlist($s["accessGroup"], $s["hashType"]);

    $match = TaskUtils::findCompletedEquivalent(
      $otherHashlist->getId(),
      "#HL# -a 0 dict.txt",
      [$s["file"]->getId()],
      $s["crackerBinary"]->getId(),
      $s["crackerBinaryType"]->getId()
    );

    $this->assertNull($match);
  }
}
