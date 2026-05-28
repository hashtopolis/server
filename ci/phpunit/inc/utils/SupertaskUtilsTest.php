<?php

namespace Hashtopolis\inc\utils;

use Exception;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\FilePretask;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\Pretask;
use Hashtopolis\dba\models\Supertask;
use Hashtopolis\dba\models\SupertaskPretask;
use Hashtopolis\dba\models\Task;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../../TestBase.php');

/**
 * End-to-end coverage for the opt-in "skip pretasks already completed" behavior of
 * SupertaskUtils::runSupertask (see https://github.com/hashtopolis/server/issues/2167).
 * Each test seeds a supertask plus, where relevant, an already-exhausted equivalent task
 * on the target hashlist, then applies the supertask and inspects what was created/skipped.
 */
final class SupertaskUtilsTest extends TestBase {

  protected function setUp(): void {
    parent::setUp();
  }

  /**
   * @param string $attackCmd
   * @param int $crackerBinaryTypeId
   * @param array $files
   * @return Pretask
   * @throws Exception
   */
  private function createPretask(string $attackCmd, int $crackerBinaryTypeId, array $files = []): Pretask {
    $pretask = $this->createDatabaseObject(
      Factory::getPretaskFactory(),
      new Pretask(null, 'pretask_' . uniqid(), $attackCmd, 60, 30, '', 0, 0, 0, 1, 0, 0, $crackerBinaryTypeId)
    );
    $this->assertTrue($pretask instanceof Pretask);
    foreach ($files as $file) {
      $this->createDatabaseObject(Factory::getFilePretaskFactory(), new FilePretask(null, $file->getId(), $pretask->getId()));
    }
    return $pretask;
  }

  /**
   * @param Pretask[] $pretasks
   * @return Supertask
   * @throws Exception
   */
  private function createSupertaskFrom(array $pretasks): Supertask {
    $supertask = $this->createDatabaseObject(Factory::getSupertaskFactory(), new Supertask(null, 'supertask_' . uniqid()));
    $this->assertTrue($supertask instanceof Supertask);
    foreach ($pretasks as $pretask) {
      $this->createDatabaseObject(Factory::getSupertaskPretaskFactory(), new SupertaskPretask(null, $supertask->getId(), $pretask->getId()));
    }
    return $supertask;
  }

  /**
   * Seeds a fully exhausted task on $hashlist that is the exact equivalent of $attackCmd / $files.
   *
   * @param mixed $accessGroup
   * @param mixed $hashlist
   * @param mixed $crackerBinary
   * @param mixed $crackerBinaryType
   * @param string $attackCmd
   * @param array $files
   * @return Task
   * @throws Exception
   */
  private function seedCompletedTask($accessGroup, $hashlist, $crackerBinary, $crackerBinaryType, string $attackCmd, array $files): Task {
    $wrapper = $this->createTaskWrapper($accessGroup, $hashlist);
    $task = $this->createTask($wrapper, $crackerBinary, $crackerBinaryType);
    Factory::getTaskFactory()->mset($task, [
      Task::ATTACK_CMD => $attackCmd,
      Task::KEYSPACE => 1000,
      Task::KEYSPACE_PROGRESS => 1000,
    ]);
    foreach ($files as $file) {
      $this->createFileTask($file, $task);
    }
    return Factory::getTaskFactory()->get($task->getId());
  }

  /**
   * Registers the tasks and wrapper created in-flight by runSupertask so the TestBase
   * teardown removes them (tasks first, then the wrapper, to respect the foreign key).
   *
   * @param mixed $taskWrapper
   * @return void
   */
  private function registerCreatedWrapper($taskWrapper): void {
    $this->registerDatabaseObject(Factory::getTaskWrapperFactory(), $taskWrapper);
    $this->registerDatabaseObjects(Factory::getTaskFactory(), TaskUtils::getTasksOfWrapper($taskWrapper->getId()));
  }

  /**
   * Applies a hashlist whose hexSalt prefixing is disabled, so the effective attack command
   * equals the pretask attack command (keeps the equivalence assertions readable).
   *
   * @param mixed $accessGroup
   * @param mixed $hashType
   * @return mixed
   * @throws Exception
   */
  private function createPlainHashlist($accessGroup, $hashType) {
    $hashlist = $this->createHashlist($accessGroup, $hashType);
    Factory::getHashlistFactory()->set($hashlist, Hashlist::HEX_SALT, 0);
    return Factory::getHashlistFactory()->get($hashlist->getId());
  }

  /**
   * With skipCompleted on, a pretask whose equivalent is already exhausted is skipped while
   * the remaining pretask is still instantiated into a new wrapper.
   *
   * @return void
   * @throws Exception
   */
  public function testRunSupertaskSkipsCompletedPretask(): void {
    $accessGroup = $this->createAccessGroup("phpunit");
    $hashType = $this->createHashType();
    $hashlist = $this->createPlainHashlist($accessGroup, $hashType);
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $file = $this->createFile($accessGroup);

    $pretaskCompleted = $this->createPretask("#HL# -a 0 dict.txt", $crackerBinaryType->getId(), [$file]);
    // The fresh pretask (the one actually instantiated) is file-less so the in-flight task it
    // produces carries no FileTask/FileDownload rows for the raw TestBase teardown to choke on.
    $pretaskFresh = $this->createPretask("#HL# -a 3 ?d?d?d?d", $crackerBinaryType->getId(), []);
    $supertask = $this->createSupertaskFrom([$pretaskCompleted, $pretaskFresh]);

    $completedTask = $this->seedCompletedTask($accessGroup, $hashlist, $crackerBinary, $crackerBinaryType, "#HL# -a 0 dict.txt", [$file]);

    $result = SupertaskUtils::runSupertask($supertask->getId(), $hashlist->getId(), $crackerBinary->getId(), true);
    $this->assertNotNull($result["taskWrapper"]);
    $this->registerCreatedWrapper($result["taskWrapper"]);

    $this->assertCount(1, $result["skippedPretasks"]);
    $this->assertEquals($pretaskCompleted->getId(), $result["skippedPretasks"][0]["pretaskId"]);
    $this->assertEquals($completedTask->getId(), $result["skippedPretasks"][0]["matchingTaskId"]);

    $createdTasks = TaskUtils::getTasksOfWrapper($result["taskWrapper"]->getId());
    $this->assertCount(1, $createdTasks);
    $this->assertEquals("#HL# -a 3 ?d?d?d?d", $createdTasks[0]->getAttackCmd());
  }

  /**
   * With skipCompleted on and every pretask already completed, no wrapper is created and all
   * pretasks are reported as skipped.
   *
   * @return void
   * @throws Exception
   */
  public function testRunSupertaskAllSkippedCreatesNoWrapper(): void {
    $accessGroup = $this->createAccessGroup("phpunit");
    $hashType = $this->createHashType();
    $hashlist = $this->createPlainHashlist($accessGroup, $hashType);
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $file = $this->createFile($accessGroup);

    $pretask = $this->createPretask("#HL# -a 0 dict.txt", $crackerBinaryType->getId(), [$file]);
    $supertask = $this->createSupertaskFrom([$pretask]);
    $this->seedCompletedTask($accessGroup, $hashlist, $crackerBinary, $crackerBinaryType, "#HL# -a 0 dict.txt", [$file]);

    $result = SupertaskUtils::runSupertask($supertask->getId(), $hashlist->getId(), $crackerBinary->getId(), true);

    $this->assertNull($result["taskWrapper"]);
    $this->assertCount(1, $result["skippedPretasks"]);
    $this->assertEquals($pretask->getId(), $result["skippedPretasks"][0]["pretaskId"]);
  }

  /**
   * Default behavior (skipCompleted off) is unchanged: every pretask is instantiated even when
   * an equivalent completed task already exists, and nothing is reported as skipped.
   *
   * @return void
   * @throws Exception
   */
  public function testRunSupertaskWithoutSkipInstantiatesAll(): void {
    $accessGroup = $this->createAccessGroup("phpunit");
    $hashType = $this->createHashType();
    $hashlist = $this->createPlainHashlist($accessGroup, $hashType);
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);

    // The instantiated pretask is intentionally file-less so the in-flight task carries no
    // FileTask/FileDownload rows that the raw TestBase teardown could not clean up.
    $pretask = $this->createPretask("#HL# -a 3 ?d?d?d?d", $crackerBinaryType->getId(), []);
    $supertask = $this->createSupertaskFrom([$pretask]);
    $this->seedCompletedTask($accessGroup, $hashlist, $crackerBinary, $crackerBinaryType, "#HL# -a 3 ?d?d?d?d", []);

    $result = SupertaskUtils::runSupertask($supertask->getId(), $hashlist->getId(), $crackerBinary->getId(), false);
    $this->assertNotNull($result["taskWrapper"]);
    $this->registerCreatedWrapper($result["taskWrapper"]);

    $this->assertCount(0, $result["skippedPretasks"]);
    $createdTasks = TaskUtils::getTasksOfWrapper($result["taskWrapper"]->getId());
    $this->assertCount(1, $createdTasks);
  }
}
