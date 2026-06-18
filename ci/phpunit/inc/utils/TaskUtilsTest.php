<?php

namespace Hashtopolis\inc\utils;

use Exception;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\models\Assignment;
use Hashtopolis\dba\models\Chunk;
use Hashtopolis\dba\models\FileDownload;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\TaskWrapper;
use Hashtopolis\dba\QueryFilter;

use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DHashcatStatus;
use Hashtopolis\inc\defines\DTaskStaticChunking;
use Hashtopolis\inc\defines\DTaskTypes;

use Hashtopolis\inc\HTException;
use Hashtopolis\inc\apiv2\error\HTTPError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;

use Hashtopolis\inc\SConfig;

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
   */
  public function testGetStatus(): void {
    // Status 1 is running, 2 is idle and 3 is completed.
    $taskObjects = $this->createRunningTaskHelper();
    $this->assertEquals(1, TaskUtils::getStatus($taskObjects["chunks"], 100, 50));

    $taskObjects = $this->createAbortedTaskHelper();
    $this->assertEquals(2, TaskUtils::getStatus($taskObjects["chunks"], 100, 50));

    $this->assertEquals(3, TaskUtils::getStatus([], 100, 100));
    $this->assertEquals(3, TaskUtils::getStatus([], 100, 101));
  }

  /**
   * Test the deletion of archived tasks.
   *
   * @return void
   * @throws Exception
   */
  public function testDeleteArchived(): void {
    $taskObjects = $this->createTaskHelper();
    
    Factory::getTaskFactory()->set($taskObjects["task"], Task::IS_ARCHIVED, 1);
    Factory::getTaskWrapperFactory()->set($taskObjects["taskWrapper"], TaskWrapper::IS_ARCHIVED, 1);

    $qF1 = new QueryFilter(TaskWrapper::IS_ARCHIVED, 1, "=");
    $qF2 = new QueryFilter(TaskWrapper::ACCESS_GROUP_ID, $taskObjects["taskWrapper"]->getAccessGroupId(), "=");
    $numberOfArchivedTaskWrappers = Factory::getTaskWrapperFactory()->countFilter([Factory::FILTER => [$qF1, $qF2]]);
    $this->assertEquals(1, $numberOfArchivedTaskWrappers);

    TaskUtils::deleteArchived($taskObjects["user"]);

    //Check if the archived TaskWrapper has been deleted
    $numberOfArchivedTaskWrappersUpdated = Factory::getTaskWrapperFactory()->countFilter([Factory::FILTER => [$qF1, $qF2]]);
    $this->assertEquals(0, $numberOfArchivedTaskWrappersUpdated);

    //Check if the archived Task has been deleted too
    $qF = new QueryFilter(Task::TASK_ID, $taskObjects["task"]->getId(), "=");
    $deletedTask = Factory::getTaskFactory()->filter([Factory::FILTER => $qF]);
    $this->assertEquals([], $deletedTask);
  }

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
  public function testToggleArchiveTask(): void {
    $taskObjects = $this->createTaskHelper();

    //Archive task
    TaskUtils::toggleArchiveTask($taskObjects["task"]->getId(), 1, $taskObjects["user"]);

    $taskWrapperUpdated = Factory::getTaskWrapperFactory()->get($taskObjects["taskWrapper"]->getId());
    $this->assertEquals(1, $taskWrapperUpdated->getIsArchived());

    $taskUpdated = Factory::getTaskFactory()->get($taskObjects["task"]->getId());
    $this->assertEquals(1, $taskUpdated->getIsArchived());


    //Un-archive task again
    TaskUtils::toggleArchiveTask($taskObjects["task"]->getId(), 0, $taskObjects["user"]);
    
    $taskWrapperUpdated = Factory::getTaskWrapperFactory()->get($taskObjects["taskWrapper"]->getId());
    $this->assertEquals(0, $taskWrapperUpdated->getIsArchived());

    $taskUpdated = Factory::getTaskFactory()->get($taskObjects["task"]->getId());
    $this->assertEquals(0, $taskUpdated->getIsArchived());


    //Archive supertask
    $supertaskObjects = $this->createSupertaskHelper();
    TaskUtils::toggleArchiveTask($supertaskObjects["tasks"][0]->getId(), 1, $supertaskObjects["user"]);
    
    $supertaskWrapperUpdated = Factory::getTaskWrapperFactory()->get($supertaskObjects["taskWrapper"]->getId());
    $this->assertEquals(1, $supertaskWrapperUpdated->getIsArchived());

    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $supertaskObjects["taskWrapper"]->getId(), "=");
    $supertaskTasks = Factory::getTaskFactory()->filter([Factory::FILTER => $qF]);
    foreach ($supertaskTasks as $task) {
      $this->assertEquals(1, $task->getIsArchived());
    }


    //Un-archive supertask again
    TaskUtils::toggleArchiveTask($supertaskObjects["tasks"][0]->getId(), 0, $supertaskObjects["user"]);
    
    $supertaskWrapperUpdated = Factory::getTaskWrapperFactory()->get($supertaskObjects["taskWrapper"]->getId());
    $this->assertEquals(0, $supertaskWrapperUpdated->getIsArchived());

    $qF = new QueryFilter(Task::TASK_WRAPPER_ID, $supertaskObjects["taskWrapper"]->getId(), "=");
    $supertaskTasks = Factory::getTaskFactory()->filter([Factory::FILTER => $qF]);
    foreach ($supertaskTasks as $task) {
      $this->assertEquals(0, $task->getIsArchived());
    }
  }

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
  public function testGetTasksOfWrapper(): void {
    $supertaskObjects = $this->createSupertaskHelper();
    $this->assertEquals(2, count(TaskUtils::getTasksOfWrapper($supertaskObjects["taskWrapper"]->getId())));
  }

  /**
   * Test getting task wrappers for a user.
   *
   * @return void
   * @throws Exception
   */
  public function testGetTaskWrappersForUser(): void {
    $taskObjects = $this->createTaskHelper();
    $taskObjects2 = $this->createTaskHelper();
    
    Factory::getTaskWrapperFactory()->set($taskObjects2["taskWrapper"], TaskWrapper::ACCESS_GROUP_ID, $taskObjects["accessGroup"]->getId());
    
    $this->assertEquals(2, count(TaskUtils::getTaskWrappersForUser($taskObjects["user"])));
  }

  /**
   * Test getting various priority values.
   *
   * @return void
   * @throws Exception
   */
  public function testGetIntegerPriorityValue(): void {
    $taskObjects = $this->createTaskHelper();
    
    $this->assertEquals(101, TaskUtils::getIntegerPriorityValue(0, true, $taskObjects["user"], null));
    
    Factory::getTaskWrapperFactory()->set($taskObjects["taskWrapper"], TaskWrapper::PRIORITY, 10);
    $taskWrapperUpdated = Factory::getTaskWrapperFactory()->get($taskObjects["taskWrapper"]->getId());

    $this->assertEquals(110, TaskUtils::getIntegerPriorityValue(0, true, $taskObjects["user"], null));
    $this->assertEquals(100, TaskUtils::getIntegerPriorityValue(0, true, $taskObjects["user"], $taskWrapperUpdated));

    $this->assertEquals(0, TaskUtils::getIntegerPriorityValue(0, null, null, null));
    $this->assertEquals(0, TaskUtils::getIntegerPriorityValue(-10, null, null, null));
    $this->assertEquals(100, TaskUtils::getIntegerPriorityValue("100", null, null, null));
  }

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
   * Test changing the chunk time of a non-existing task.
   *
   * @return void
   * @throws HTException
   */
  public function testChangeChunkTimeNonExisting(): void {
    $this->expectException(HTException::class);
    $taskObjects = $this->createTaskHelper();
    TaskUtils::changeChunkTime(999999999, 1, $taskObjects["user"]);
  }

  /**
   * Test changing the chunk time with insufficient permissions.
   *
   * @return void
   * @throws HTException
   */
  public function testChangeChunkTimeNotAllowed(): void {
    $this->expectException(HTException::class);
    $taskObjects = $this->createRunningTaskHelper();
    $wrongUser = $this->createUser("phpunit");
    TaskUtils::changeChunkTime($taskObjects["task"]->getId(), 1, $wrongUser);
  }

  /**
   * Test changing the chunk time with insufficient permissions.
   *
   * @return void
   * @throws HTException
   */
  public function testChangeChunkTime(): void {
    $taskObjects = $this->createRunningTaskHelper();
    $oldChunkTime = $taskObjects["task"]->getChunkTime();
    
    TaskUtils::changeChunkTime($taskObjects["task"]->getId(), $oldChunkTime + 1, $taskObjects["user"]);

    $taskUpdated = Factory::getTaskFactory()->get($taskObjects["task"]->getId());
    $this->assertEquals($oldChunkTime + 1, $taskUpdated->getChunkTime());

    $assignmentUpdated = Factory::getAssignmentFactory()->get($taskObjects["assignment"]->getId());
    $this->assertEquals($assignmentUpdated->getBenchmark(), strval($taskObjects["assignment"]->getBenchmark() / $oldChunkTime * ($oldChunkTime + 1)));
  }

  /**
   * Test aborting a non-existing chunk.
   *
   * @return void
   * @throws HTException
   */
  public function testAbortChunkNonExisting(): void {
    $this->expectException(HTException::class);
    $user = $this->createUser("phpunit");
    TaskUtils::abortChunk(999999999, $user);
  }

  /**
   * Test aborting a chunk with insufficient permissions.
   *
   * @return void
   * @throws Exception
   */
  public function testAbortChunkNotAllowed(): void {
    $this->expectException(HTException::class);
    $taskObjects = $this->createRunningTaskHelper();
    $wrongUser = $this->createUser("phpunit");
    TaskUtils::abortChunk($taskObjects["chunks"][1]->getId(), $wrongUser);
  }

  /**
   * Test aborting a chunk.
   *
   * @return void
   * @throws Exception
   */
  public function testAbortChunk(): void {
    $taskObjects = $this->createRunningTaskHelper();
    $oldChunkState = $taskObjects["chunks"][1]->getState();
    TaskUtils::abortChunk($taskObjects["chunks"][1]->getId(), $taskObjects["user"]);

    $chunkUpdated = Factory::getChunkFactory()->get($taskObjects["chunks"][1]->getId());
    $this->assertEquals(DHashcatStatus::ABORTED, $chunkUpdated->getState());
    $this->assertNotEquals($oldChunkState, $chunkUpdated->getState());
  }

  /**
   * Test resetting a non-existing chunk.
   *
   * @return void
   * @throws HTException
   */
  public function testResetChunkNonExisting(): void {
    $this->expectException(HTException::class);
    $user = $this->createUser("phpunit");
    TaskUtils::resetChunk(999999999, $user);
  }

  /**
   * Test resetting a chunk with insufficient permissions.
   *
   * @return void
   * @throws Exception
   */
  public function testResetChunkNotAllowed(): void {
    $this->expectException(HTException::class);
    $taskObjects = $this->createRunningTaskHelper();
    $wrongUser = $this->createUser("phpunit");
    TaskUtils::resetChunk($taskObjects["chunks"][1]->getId(), $wrongUser);
  }

  /**
   * Test resetting a chunk.
   *
   * @return void
   * @throws Exception
   */
  public function testResetChunk(): void {
    $taskObjects = $this->createRunningTaskHelper();
    TaskUtils::resetChunk($taskObjects["chunks"][1]->getId(), $taskObjects["user"]);

    $chunkUpdated = Factory::getChunkFactory()->get($taskObjects["chunks"][1]->getId());
    
    $this->assertEquals(DHashcatStatus::INIT, $chunkUpdated->getState());
    $this->assertEquals(0, $chunkUpdated->getProgress());
    $this->assertEquals(0, $chunkUpdated->getDispatchTime());
    $this->assertEquals(0, $chunkUpdated->getSolveTime());
    $this->assertEquals(0, $chunkUpdated->getCheckpoint());
  }

  /**
   * Test setting a benchmark for a non-existing assignment.
   *
   * @return void
   * @throws HTException
   */
  public function testSetBenchmarkNonExisting(): void {
    $this->expectException(HTException::class);
    $taskObjects = $this->createRunningTaskHelper();
    Factory::getAssignmentFactory()->delete($taskObjects["assignment"]);
    TaskUtils::setBenchmark($taskObjects["agent"]->getId(), "1", $taskObjects["user"]);
  }

  /**
   * Test setting a benchmark with insufficient permissions.
   *
   * @return void
   * @throws Exception
   */
  public function testSetBenchmarkNotAllowed(): void {
    $this->expectException(HTException::class);
    $taskObjects = $this->createRunningTaskHelper();
    $wrongUser = $this->createUser("phpunit");
    TaskUtils::setBenchmark($taskObjects["agent"]->getId(), "1", $wrongUser);
  }

  /**
   * Test setting a benchmark.
   *
   * @return void
   * @throws Exception
   */
  public function testSetBenchmark(): void {
    $taskObjects = $this->createRunningTaskHelper();
    TaskUtils::setBenchmark($taskObjects["agent"]->getId(), "2", $taskObjects["user"]);

    $assignmentUpdated = Factory::getAssignmentFactory()->get($taskObjects["assignment"]->getId());
    $this->assertEquals("2", $assignmentUpdated->getBenchmark());
  }

  /**
   * Test purging a non-existing task.
   *
   * @return void
   * @throws HTException
   */
  public function testPurgeTaskNonExisting(): void {
    $this->expectException(HTException::class);
    $user = $this->createUser("phpunit");
    TaskUtils::purgeTask(999999999, $user);
  }

  /**
   * Test purging a task with insufficient permissions.
   *
   * @return void
   * @throws Exception
   */
  public function testPurgeTaskNotAllowed(): void {
    $this->expectException(HTException::class);
    $taskObjects = $this->createRunningTaskHelper();
    $wrongUser = $this->createUser("phpunit");
    TaskUtils::purgeTask($taskObjects["task"]->getId(), $wrongUser);
  }

  /**
   * Test purging a task.
   *
   * @return void
   * @throws Exception
   */
  public function testPurgeTask(): void {
    $taskObjects = $this->createRunningTaskHelper();
    Factory::getTaskWrapperFactory()->set($taskObjects["taskWrapper"], TaskWrapper::CRACKED, 1);
    
    TaskUtils::purgeTask($taskObjects["task"]->getId(), $taskObjects["user"]);

    $assignmentUpdated = Factory::getAssignmentFactory()->get($taskObjects["assignment"]->getId());
    $this->assertEquals("0", $assignmentUpdated->getBenchmark());

    $qF = new QueryFilter(Chunk::TASK_ID, $taskObjects["task"]->getId(), "=");
    $numberOfChunks = Factory::getChunkFactory()->countFilter([Factory::FILTER => $qF]);
    $this->assertEquals(0, $numberOfChunks);

    $taskUpdated = Factory::getTaskFactory()->get($taskObjects["task"]->getId());
    $this->assertEquals(0, $taskUpdated->getKeyspace());
    $this->assertEquals(0, $taskUpdated->getKeyspaceProgress());

    $taskWrapperUpdated = Factory::getTaskWrapperFactory()->get($taskObjects["taskWrapper"]->getId());
    $this->assertEquals(0, $taskWrapperUpdated->getCracked());
  }

  /**
   * Test deleting a non-existing task.
   *
   * @return void
   * @throws HTException
   */
  public function testDeleteNonExisting(): void {
    $this->expectException(HTException::class);
    $user = $this->createUser("phpunit");
    TaskUtils::delete(999999999, $user);
  }

  /**
   * Test deleting a task with insufficient permissions.
   *
   * @return void
   * @throws Exception
   */
  public function testDeleteNotAllowed(): void {
    $this->expectException(HTException::class);
    $taskObjects = $this->createTaskHelper();
    $wrongUser = $this->createUser("phpunit");
    TaskUtils::delete($taskObjects["task"]->getId(), $wrongUser);
  }

  /**
   * Test deleting a task.
   *
   * @return void
   * @throws Exception
   */
/*  public function testDelete(): void {
    $taskObjects = $this->createTaskHelper();    
    TaskUtils::delete($taskObjects["task"]->getId(), $taskObjects["user"]);

    $qF = new QueryFilter(Task::TASK_ID, $taskObjects["task"]->getId(), "=");
    $deletedTask = Factory::getTaskFactory()->filter([Factory::FILTER => $qF]);
    $this->assertEquals([], $deletedTask);

    $deletedTaskWrapper = Factory::getTaskWrapperFactory()->filter([Factory::FILTER => $qF]);
    $this->assertEquals([], $deletedTaskWrapper);

    //TODO everything from deleteTask deleted too or do it in a separate test

    //if supertask: task wrapper not deleted?
  }*/

  /**
   * Test creating a task with an invalid hashlist.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskInvalidHashlist(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Invalid hashlist ID!");
    TaskUtils::createTask(999999999, uniqid(), "", 0, 0, "", "", false, false, 0, null, 0, 0, 0, [], 0, null);
  }

  /**
   * Test creating a task with an invalid hashlist.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskArchivedHashlist(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("You cannot create a task for an archived hashlist!");
    $hashlistObjects = $this->createHashlistHelper();
    Factory::getHashlistFactory()->set($hashlistObjects["hashlist"], Hashlist::IS_ARCHIVED, 1);
    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), "", 0, 0, "", "", false, false, 0, null, 0, 0, 0, [], 0, null);
  }

  /**
   * Test creating a task without the required hashlist access permissions.
   *
   * @return void
   * @throws HttpForbidden
   */
  public function testCreateTaskNoAccess(): void {
    $this->expectException(HttpForbidden::class);
    $this->expectExceptionMessage("You have no access to this hashlist!");
    $hashlistObjects = $this->createHashlistHelper();
    $user = $this->createUser("phpunit");
    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), "", 0, 0, "", "", false, false, 0, null, 0, 0, 0, [], 0, $user);
  }

  /**
   * Test creating a task with an invalid cracker.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskInvalidCracker(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Invalid cracker ID!");
    $hashlistObjects = $this->createHashlistHelper();
    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), "", 0, 0, "", "", false, false, 0, null, 0, 0, 0, [], 999999999, $hashlistObjects['user']);
  }

  /**
   * Test creating a task with an attack command which doesn't include the hashlist alias.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskInvalidAttack(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Attack command does not contain hashlist alias!");

    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);

    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), "", 0, 0, "", "", false, false, 0, null, 0, 0, 0, [], $crackerBinary->getId(), $hashlistObjects['user']);
  }

  /**
   * Test creating a task with an attack command which is longer than permitted.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskAttackTooLong(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Attack command is too long (max 65535 characters)!");

    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    
    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS).random_bytes(65536), 0, 0, "", "", false, false, 0, null, 0, 0, 0, [], $crackerBinary->getId(), $hashlistObjects['user']);
  }

  /**
   * Test creating a task with invalid static chunking settings.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskInvalidStaticChunking1(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Invalid static chunk setting!");

    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);

    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS), 0, 0, "", "", false, false, 0, null, 0, 0, 0, [], $crackerBinary->getId(), $hashlistObjects['user'], "", DTaskStaticChunking::NORMAL - 1);
  }

  /**
   * Test creating a task with invalid static chunking settings.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskInvalidStaticChunking2(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Invalid static chunk setting!");

    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);

    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS), 0, 0, "", "", false, false, 0, null, 0, 0, 0, [], $crackerBinary->getId(), $hashlistObjects['user'], "", DTaskStaticChunking::NUM_CHUNKS + 1);
  }

  /**
   * Test creating a task with static chunking settings in combination with an invalid chunk size.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskInvalidStaticChunking3(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Invalid chunk size / number of chunks for static chunking!");

    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);

    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS), 0, 0, "", "", false, false, 0, null, 0, 0, 0, [], $crackerBinary->getId(), $hashlistObjects['user'], "", DTaskStaticChunking::NORMAL + 1, 0);
  }

  /**
   * Test creating a task with an attack command containing blacklisted characters.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskAttackBlacklisted(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Attack command contains blacklisted characters!");

    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);

    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS).SConfig::getInstance()->getVal(DConfig::BLACKLIST_CHARS)[0], 0, 0, "", "", false, false, 0, null, 0, 0, 0, [], $crackerBinary->getId(), $hashlistObjects['user']);
  }

  /**
   * Test creating a task with an preprocessor command containing blacklisted characters.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskPreprocessorBlacklisted(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Preprocessor command contains blacklisted characters!");

    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);

    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS), 0, 0, "", "", false, false, 0, SConfig::getInstance()->getVal(DConfig::BLACKLIST_CHARS)[0], 0, 0, 0, [], $crackerBinary->getId(), $hashlistObjects['user']);
  }

  /**
   * Test creating a task with an invalid chunk time.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskInvalidChunkTime1(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Invalid chunk time!");

    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);

    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS), '', 0, "", "", false, false, 0, "", 0, 0, 0, [], $crackerBinary->getId(), $hashlistObjects['user']);
  }

  /**
   * Test creating a task with an invalid chunk time.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskInvalidChunkTime2(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Invalid chunk time!");

    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);

    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS), 0, 0, "", "", false, false, 0, "", 0, 0, 0, [], $crackerBinary->getId(), $hashlistObjects['user']);
  }

  /**
   * Test creating a task with an invalid status timer.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskInvalidStatus1(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Invalid status timer!");

    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);

    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS), 1, '', "", "", false, false, 0, "", 0, 0, 0, [], $crackerBinary->getId(), $hashlistObjects['user']);
  }

  /**
   * Test creating a task with an invalid status timer.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskInvalidStatus2(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Invalid status timer!");

    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);

    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS), 1, 0, "", "", false, false, 0, "", 0, 0, 0, [], $crackerBinary->getId(), $hashlistObjects['user']);
  }

  /**
   * Test creating a task with an invalid benchmark type.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskBenchmark(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Invalid benchmark type!");

    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);

    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS), 1, 1, "", "", false, false, 0, "", 0, 0, 0, [], $crackerBinary->getId(), $hashlistObjects['user']);
  }

  /**
   * Test creating a task with an invalid enforce pipe value.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskInvalidPipe1(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Invalid enforce pipe value");

    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);

    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS), 1, 1, "speed", "", false, false, 0, "", 0, 0, 0, [], $crackerBinary->getId(), $hashlistObjects['user'], "", DTaskStaticChunking::NORMAL + 1, 1, -1);
  }

  /**
   * Test creating a task with an invalid enforce pipe value.
   *
   * @return void
   * @throws HttpError
   */
  public function testCreateTaskInvalidPipe2(): void {
    $this->expectException(HttpError::class);
    $this->expectExceptionMessage("Invalid enforce pipe value");

    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);

    TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS), 1, 1, "speed", "", false, false, 0, "", 0, 0, 0, [], $crackerBinary->getId(), $hashlistObjects['user'], "", DTaskStaticChunking::NORMAL + 1, 1, 2);
  }

  /**
   * Test creating a task with files.
   *
   * @return void
   */
  public function testCreateTaskWithFiles(): void {
    $hashlistObjects = $this->createHashlistHelper();
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $file = $this->createFile($hashlistObjects['accessGroup']);

    $task = TaskUtils::createTask($hashlistObjects["hashlist"]->getId(), uniqid(), SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS), 1, 1, "speed", "", false, false, 0, "", 0, 0, 0, [$file->getId()], $crackerBinary->getId(), $hashlistObjects['user']);

    $taskCreated = Factory::getTaskFactory()->get($task->getId());
    $this->assertTrue($taskCreated instanceof Task);

    $qF = new QueryFilter(Task::TASK_ID, $task->getId(), "=");
    $fileTask = Factory::getFileTaskFactory()->filter([Factory::FILTER => $qF]);
    $this->assertEquals(1, count($fileTask));

    // Delete the FileDownload, FileTask, Task and TaskWrapper in the right order manually.
    // They've been created in the createTask function of TaskUtils during the successful creation.
    $qF = new QueryFilter(FileDownload::FILE_ID, $file->getId(), "=");    
    $fileDownload = Factory::getFileDownloadFactory()->filter([Factory::FILTER => $qF]);
    Factory::getFileDownloadFactory()->delete($fileDownload[0]);

    Factory::getFileTaskFactory()->delete($fileTask[0]);

    Factory::getTaskFactory()->delete($task);

    $qF = new QueryFilter(AccessGroup::ACCESS_GROUP_ID, $hashlistObjects["accessGroup"]->getId(), "=");
    $taskWrapper = Factory::getTaskWrapperFactory()->filter([Factory::FILTER => $qF]);
    Factory::getTaskWrapperFactory()->delete($taskWrapper[0]);
  }

  /**
   * Test getting the best task for an agent who isn't trusted and only has a task with a secret hashlist.
   *
   * @return void
   */
  public function testGetBestTaskNotTrusted(): void {
    $taskObjects = $this->createRunningTaskHelper(DTaskTypes::NORMAL, 1, 0);
    $this->assertNull(TaskUtils::getBestTask($taskObjects["agent"]));
  }

  /**
   * Test getting the best task for an agent who doesn't have an overlapping access group with a hashlist.
   *
   * @return void
   */
  public function testGetBestTaskNoAccess(): void {
    $taskObjects = $this->createRunningTaskHelper();
    
    $qF = new QueryFilter(Agent::AGENT_ID, $taskObjects["agent"]->getId(), "=");
    $accessGroupAgent = Factory::getAccessGroupAgentFactory()->filter([Factory::FILTER => $qF]);
    Factory::getAccessGroupAgentFactory()->delete($accessGroupAgent[0]);
    
    $this->assertNull(TaskUtils::getBestTask($taskObjects["agent"]));
  }

  /**
   * Test getting the best task for an agent who only has access to tasks with fully cracked hashlists.
   *
   * @return void
   */
  public function testGetBestTaskFullyCracked(): void {
    $taskObjects = $this->createRunningTaskHelper();
    
    Factory::getHashlistFactory()->set($taskObjects["hashlist"], Hashlist::HASH_COUNT, 5);
    Factory::getHashlistFactory()->set($taskObjects["hashlist"], Hashlist::CRACKED, 5);
    
    $this->assertNull(TaskUtils::getBestTask($taskObjects["agent"]));
  }

  /**
   * Test if getting the best tasks returns the correct order.
   *
   * @return void
   */
  public function testGetBestTasksOrdered(): void {
    $taskObjects1 = $this->createRunningTaskHelper();
    $taskObjects2 = $this->createRunningTaskHelper();
    Factory::getTaskWrapperFactory()->set($taskObjects2["taskWrapper"], TaskWrapper::PRIORITY, 100);
    $this->createAccessGroupAgent($taskObjects1["agent"], $taskObjects2["accessGroup"]);

/*    $newAgent = $this->createAgent("phpunit");
    $this->createAccessGroupAgent($newAgent, $taskObjects1["accessGroup"]);
    $this->createAccessGroupAgent($newAgent, $taskObjects2["accessGroup"]);

    $bestTasks = TaskUtils::getBestTask($newAgent, true);
    $this->assertEquals($bestTasks[0]->getId(), $taskObjects2["task"]->getId());
    $this->assertEquals($bestTasks[1]->getId(), $taskObjects1["task"]->getId());
*/
    $bestTasks = TaskUtils::getBestTask($taskObjects1["agent"], true);
    $this->assertEquals($bestTasks[0]->getId(), $taskObjects1["task"]->getId());
    $this->assertEquals(count($bestTasks), 1);

    Factory::getAssignmentFactory()->delete($taskObjects1["assignment"]);
    Factory::getAssignmentFactory()->delete($taskObjects2["assignment"]);
    
    $bestTasks = TaskUtils::getBestTask($taskObjects1["agent"], true);
    $this->assertEquals($bestTasks[0]->getId(), $taskObjects2["task"]->getId());
    $this->assertEquals($bestTasks[1]->getId(), $taskObjects1["task"]->getId());

    //TODO add: high prio and secret file but no trusted agent || high prio but already finished || high prio and cpu task but non-cpu agent
  }

  /**
   * Test getting the best task.
   *
   * @return void
   */
  public function testGetBestTask(): void {
    $taskObjects1 = $this->createRunningTaskHelper();
    $taskObjects2 = $this->createRunningTaskHelper();
    Factory::getTaskWrapperFactory()->set($taskObjects2["taskWrapper"], TaskWrapper::PRIORITY, 100);
    $this->createAccessGroupAgent($taskObjects1["agent"], $taskObjects2["accessGroup"]);
    
    $this->assertEquals(TaskUtils::getBestTask($taskObjects1["agent"])->getId(), $taskObjects1["task"]->getId());
/*
    $newAgent = $this->createAgent("phpunit");
    $this->createAccessGroupAgent($newAgent, $taskObjects1["accessGroup"]);
    $this->createAccessGroupAgent($newAgent, $taskObjects2["accessGroup"]);
    $this->assertEquals(TaskUtils::getBestTask($newAgent)->getId(), $taskObjects2["task"]->getId());*/
  }

  /**
   * Test checking if a task is completed or fully dispatched on an archived task.
   *
   * @return void
   */
  public function testCheckTaskArchived(): void {
    $taskObjects = $this->createTaskHelper();
    Factory::getTaskFactory()->set($taskObjects["task"], Task::IS_ARCHIVED, 1);
    $this->assertNull(TaskUtils::checkTask($taskObjects["task"]));
  }

  /**
   * Test checking if a task is completed or fully dispatched on a completed task.
   *
   * @return void
   */
  public function testCheckTaskCompleted(): void {
    $taskObjects = $this->createCompletedTaskHelper();
    $this->assertEquals(TaskUtils::checkTask($taskObjects["task"])->getId(), $taskObjects["task"]->getId());
  }

  /**
   * Test unassigning all agents.
   *
   * @return void
   */
  public function testUnassignAllAgents(): void {
    $taskObjects1 = $this->createRunningTaskHelper();

    $taskObjects2 = array();
    $taskObjects2["taskWrapper"] = $this->createTaskWrapper($taskObjects1["accessGroup"], $taskObjects1["hashlist"]);
    $taskObjects2["task"] = $this->createTask($taskObjects2["taskWrapper"], $taskObjects1["crackerBinary"], $taskObjects1["crackerBinaryType"]);
    $taskObjects2["assignment"] = $this->createAssignment($taskObjects2["task"]->getId(), $taskObjects1["agent"]->getId());

    TaskUtils::UnassignAllAgents(array($taskObjects1["hashlist"]));
    $qF = new QueryFilter(Assignment::TASK_ID, $taskObjects1["task"]->getId(), "=");
    $this->assertEquals([], Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF]));
    $qF = new QueryFilter(Assignment::TASK_ID, $taskObjects2["task"]->getId(), "=");
    $this->assertEquals([], Factory::getAssignmentFactory()->filter([Factory::FILTER => $qF]));
  }

  /**
   * Test getting the most important task according to the priority.
   *
   * @return void
   */
  public function testGetImportantTask(): void {
    $taskObjects1 = $this->createTaskHelper();
    Factory::getTaskWrapperFactory()->set($taskObjects1["taskWrapper"], TaskWrapper::PRIORITY, 5);
    $taskObjects2 = $this->createTaskHelper();
    Factory::getTaskWrapperFactory()->set($taskObjects2["taskWrapper"], TaskWrapper::PRIORITY, 10);

    $this->assertEquals(TaskUtils::getImportantTask($taskObjects1["task"], null), $taskObjects1["task"]);
    $this->assertEquals(TaskUtils::getImportantTask(null, $taskObjects2["task"]), $taskObjects2["task"]);
    $this->assertEquals(TaskUtils::getImportantTask($taskObjects1["task"], $taskObjects2["task"]), $taskObjects2["task"]);

    Factory::getTaskWrapperFactory()->set($taskObjects2["taskWrapper"], TaskWrapper::PRIORITY, 5);
    Factory::getTaskFactory()->set($taskObjects1["task"], Task::PRIORITY, 10);
    $this->assertEquals(TaskUtils::getImportantTask($taskObjects1["task"], $taskObjects2["task"]), $taskObjects1["task"]);

    Factory::getTaskFactory()->set($taskObjects2["task"], Task::PRIORITY, 20);
    $this->assertEquals(TaskUtils::getImportantTask($taskObjects1["task"], $taskObjects2["task"]), $taskObjects2["task"]);
  }

  /**
   * Test depriorizing all tasks.
   *
   * @return void
   */
  public function testDepriorizeAllTasks(): void {
    $taskObjects1 = $this->createTaskHelper();
    Factory::getTaskWrapperFactory()->set($taskObjects1["taskWrapper"], TaskWrapper::PRIORITY, 5);
    Factory::getTaskFactory()->set($taskObjects1["task"], Task::PRIORITY, 10);
    $taskObjects2 = $this->createTaskHelper();
    Factory::getTaskWrapperFactory()->set($taskObjects2["taskWrapper"], TaskWrapper::PRIORITY, 10);
    Factory::getTaskFactory()->set($taskObjects2["task"], Task::PRIORITY, 20);

    TaskUtils::depriorizeAllTasks([$taskObjects1["hashlist"], $taskObjects2["hashlist"]]);

    $this->assertEquals(Factory::getTaskFactory()->get($taskObjects1["task"]->getId())->getPriority(), 0);
    $this->assertEquals(Factory::getTaskWrapperFactory()->get($taskObjects1["taskWrapper"]->getId())->getPriority(), 0);
    $this->assertEquals(Factory::getTaskFactory()->get($taskObjects2["task"]->getId())->getPriority(), 0);
    $this->assertEquals(Factory::getTaskWrapperFactory()->get($taskObjects2["taskWrapper"]->getId())->getPriority(), 0);
  }

  /**
   * Test getting all files of a task.
   *
   * @return void
   */
  public function testGetFilesOfTask(): void {
    $taskObjects = $this->createTaskHelper();
    $this->assertEquals(TaskUtils::getFilesOfTask($taskObjects["task"]), []);

    $taskObjects["file"][0] = $this->createFile($taskObjects["accessGroup"]);
    $taskObjects["fileTask"][0] = $this->createFileTask($taskObjects["file"][0], $taskObjects["task"]);
    $taskObjects["file"][1] = $this->createFile($taskObjects["accessGroup"]);
    $taskObjects["fileTask"][1] = $this->createFileTask($taskObjects["file"][1], $taskObjects["task"]);

    $taskFiles = TaskUtils::getFilesOfTask($taskObjects["task"]);
    $this->assertEquals(count($taskFiles), 2);
    $this->assertEquals($taskFiles[0], $taskObjects["file"][0]);
    $this->assertEquals($taskFiles[1], $taskObjects["file"][1]);
  }

  /**
   * Test deleting a non-existing supertask.
   *
   * @return void
   * @throws HTException
   */
  public function testDeleteSupertaskNonExisting(): void {
    $this->expectException(HTException::class);
    $this->expectExceptionMessage("Invalid supertask!");

    $user = $this->createUser("phpunit");
    TaskUtils::deleteSupertask(999999999, $user);
  }

  /**
   * Test deleting a supertask to which the user doesn't have access.
   *
   * @return void
   * @throws HTException
   */
  public function testDeleteSupertaskNoPermission(): void {
    $this->expectException(HTException::class);
    $this->expectExceptionMessage("No access to this supertask!");

    $supertaskObjects = $this->createSupertaskHelper();
    $wrongUser = $this->createUser("phpunit");
    TaskUtils::deleteSupertask($supertaskObjects["taskWrapper"]->getId(), $wrongUser);
  }

  /**
   * Test deleting a supertask.
   *
   * @return void
   */
  public function testDeleteSupertask(): void {
    $supertaskObjects = $this->createSupertaskHelper();
    TaskUtils::deleteSupertask($supertaskObjects["taskWrapper"]->getId(), $supertaskObjects["user"]);

    $this->assertNull(Factory::getTaskWrapperFactory()->get($supertaskObjects["taskWrapper"]->getId()));
    $this->assertNull(Factory::getTaskFactory()->get($supertaskObjects["tasks"][0]->getId()));
    $this->assertNull(Factory::getTaskFactory()->get($supertaskObjects["tasks"][1]->getId()));
  }

  /**
   * Test getting the number of other assigned agents.
   *
   * @return void
   */
  public function testNumberOfOtherAssignedAgents(): void {
    $taskObjects = $this->createTaskHelper();
    $taskObjects["agents"][0] = $this->createAgent("phpunit");
    $this->assertEquals(TaskUtils::numberOfOtherAssignedAgents($taskObjects["task"], $taskObjects["agents"][0]), 0);

    $this->createAssignment($taskObjects["task"]->getId(), $taskObjects["agents"][0]->getId());
    $this->assertEquals(TaskUtils::numberOfOtherAssignedAgents($taskObjects["task"], $taskObjects["agents"][0]), 0);

    $taskObjects["agents"][1] = $this->createAgent("phpunit");
    $this->createAssignment($taskObjects["task"]->getId(), $taskObjects["agents"][1]->getId());
    $this->assertEquals(TaskUtils::numberOfOtherAssignedAgents($taskObjects["task"], $taskObjects["agents"][0]), 1);
  }

  /**
   * Test if a task is saturated by other agents.
   *
   * @return void
   */
  public function testIsSaturatedByOtherAgents(): void {
    $taskObjects = $this->createTaskHelper();
    $taskObjects["agents"][0] = $this->createAgent("phpunit");
    $this->assertEquals(TaskUtils::isSaturatedByOtherAgents($taskObjects["task"], $taskObjects["agents"][0]), false);

    $this->createAssignment($taskObjects["task"]->getId(), $taskObjects["agents"][0]->getId());
    $this->assertEquals(TaskUtils::isSaturatedByOtherAgents($taskObjects["task"], $taskObjects["agents"][0]), false);

    Factory::getTaskFactory()->set($taskObjects["task"], Task::IS_SMALL, 1);
    $this->assertEquals(TaskUtils::isSaturatedByOtherAgents($taskObjects["task"], $taskObjects["agents"][0]), false);
    
    $taskObjects["agents"][1] = $this->createAgent("phpunit");
    $this->assertEquals(TaskUtils::isSaturatedByOtherAgents($taskObjects["task"], $taskObjects["agents"][1]), true);

    Factory::getTaskFactory()->mset($taskObjects["task"], [Task::IS_SMALL => 0, Task::MAX_AGENTS => 1]);
    $this->assertEquals(TaskUtils::isSaturatedByOtherAgents($taskObjects["task"], $taskObjects["agents"][0]), false);
    $this->assertEquals(TaskUtils::isSaturatedByOtherAgents($taskObjects["task"], $taskObjects["agents"][1]), true);

    Factory::getTaskFactory()->set($taskObjects["task"], Task::MAX_AGENTS, 5);
    $this->createAssignment($taskObjects["task"]->getId(), $taskObjects["agents"][1]->getId());
    $taskObjects["agents"][2] = $this->createAgent("phpunit");
    $this->assertEquals(TaskUtils::isSaturatedByOtherAgents($taskObjects["task"], $taskObjects["agents"][2]), false);
  }

  /**
   * Test getting the progress of a task.
   *
   * @return void
   */
  public function testGetTaskProgress(): void {
    $taskObjects = $this->createRunningTaskHelper();
    $this->assertEquals(TaskUtils::getTaskProgress($taskObjects["task"]), 5100);

    $taskObjects = $this->createTaskHelper();
    $this->assertEquals(TaskUtils::getTaskProgress($taskObjects["task"]), 0);
  }


  //Helper functions:
  public function createHashlistHelper(int $isSecret = 0): array {
    $user = $this->createUser("phpunit");
    $accessGroup = $this->createAccessGroup("phpunit");
    $this->createAccessGroupUser($user, $accessGroup);

    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($accessGroup, $hashType, $isSecret);

    return array(
      "user"=> $user,
      "accessGroup"=>$accessGroup,
      "hashType"=>$hashType,
      "hashlist"=>$hashlist
    );
  }

  public function createTaskHelper(int $taskType = DTaskTypes::NORMAL, int $isSecret = 0): array {
    $user = $this->createUser("phpunit");
    $accessGroup = $this->createAccessGroup("phpunit");
    $this->createAccessGroupUser($user, $accessGroup);

    $hashType = $this->createHashType();
    $hashlist = $this->createHashlist($accessGroup, $hashType, $isSecret);

    $taskWrapper = $this->createTaskWrapper($accessGroup, $hashlist, $taskType);

    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $task = $this->createTask($taskWrapper, $crackerBinary, $crackerBinaryType);

    return array(
      "user"=> $user,
      "accessGroup"=>$accessGroup,
      "hashType"=>$hashType,
      "hashlist"=>$hashlist,
      "taskWrapper"=>$taskWrapper,
      "crackerBinaryType"=>$crackerBinaryType,
      "crackerBinary"=>$crackerBinary,
      "task"=>$task
    );
  }

  public function createRunningTaskHelper(int $taskType = DTaskTypes::NORMAL, int $isSecret = 0, int $isTrusted = 1): array {
    $taskObjects = $this->createTaskHelper($taskType, $isSecret);

    $taskObjects["agent"] = $this->createAgent("phpunit", $isTrusted);
    $this->createAccessGroupAgent($taskObjects["agent"], $taskObjects["accessGroup"]);
    $taskObjects["assignment"] = $this->createAssignment($taskObjects["task"]->getId(), $taskObjects["agent"]->getId());

    $chunk1 = $this->createChunk($taskObjects["task"], $taskObjects["agent"], DHashcatStatus::EXHAUSTED);
    Factory::getChunkFactory()->mset($chunk1, [Chunk::SPEED => 0, Chunk::SOLVE_TIME => time(), Chunk::PROGRESS => 10000, Chunk::CHECKPOINT => $chunk1->getSkip() + $chunk1->getLength()]);

    $chunk2 = $this->createChunk($taskObjects["task"], $taskObjects["agent"], DHashcatStatus::RUNNING);
    Factory::getChunkFactory()->mset($chunk2, [Chunk::SPEED => 100, Chunk::DISPATCH_TIME => time(), Chunk::PROGRESS => 5000, Chunk::CHECKPOINT => 5000]);

    $qF = new QueryFilter(Task::TASK_ID, $taskObjects["task"]->getId(), "=");
    $taskObjects["chunks"] = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);

    return $taskObjects;
  }

  public function createAbortedTaskHelper(int $taskType = DTaskTypes::NORMAL, int $isSecret = 0, int $isTrusted = 1): array {
    $taskObjects = $this->createTaskHelper($taskType, $isSecret);

    $taskObjects["agent"] = $this->createAgent("phpunit", $isTrusted);

    $chunk1 = $this->createChunk($taskObjects["task"], $taskObjects["agent"], DHashcatStatus::EXHAUSTED);
    Factory::getChunkFactory()->mset($chunk1, [Chunk::SOLVE_TIME => time(), Chunk::PROGRESS => 10000]);

    $chunk2 = $this->createChunk($taskObjects["task"], $taskObjects["agent"], DHashcatStatus::ABORTED);
    Factory::getChunkFactory()->mset($chunk2, [Chunk::DISPATCH_TIME => 1, Chunk::PROGRESS => 5000]);

    $qF = new QueryFilter(Task::TASK_ID, $taskObjects["task"]->getId(), "=");
    $taskObjects["chunks"] = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);

    return $taskObjects;
  }

  public function createCompletedTaskHelper(int $taskType = DTaskTypes::NORMAL, int $isSecret = 0, int $isTrusted = 1): array {
    $taskObjects = $this->createTaskHelper($taskType, $isSecret);

    $taskObjects["agent"] = $this->createAgent("phpunit", $isTrusted);

    $chunk1 = $this->createChunk($taskObjects["task"], $taskObjects["agent"], DHashcatStatus::EXHAUSTED);
    Factory::getChunkFactory()->mset($chunk1, [Chunk::SOLVE_TIME => time(), Chunk::PROGRESS => 10000]);

    $chunk2 = $this->createChunk($taskObjects["task"], $taskObjects["agent"], DHashcatStatus::EXHAUSTED);
    Factory::getChunkFactory()->mset($chunk2, [Chunk::SOLVE_TIME => time(), Chunk::PROGRESS => 10000]);

    $qF = new QueryFilter(Task::TASK_ID, $taskObjects["task"]->getId(), "=");
    $taskObjects["chunks"] = Factory::getChunkFactory()->filter([Factory::FILTER => $qF]);

    return $taskObjects;
  }

  public function createSupertaskHelper(int $isSecret = 0): array {
    $taskObjects = $this->createTaskHelper(DTaskTypes::SUPERTASK, $isSecret);

    $taskObjects["tasks"] = [$taskObjects["task"], $this->createTask($taskObjects["taskWrapper"], $taskObjects["crackerBinary"], $taskObjects["crackerBinaryType"])];
    $taskObjects["task"] = null;

    return $taskObjects;
  }
}
