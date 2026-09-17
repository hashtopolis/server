<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\defines\DBackgroundJobStatus;
use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\defines\DDirectories;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\jobs\BackgroundJobRunner;
use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class BackgroundJobUtilsTest extends TestBase {
  private string $testFilename;

  #[Override]
  protected function setUp(): void {
    parent::setUp();
    $this->testFilename = 'phpunit_bgjob_' . uniqid() . '.txt';
  }

  #[Override]
  protected function tearDown(): void {
    $filePath = self::getFilesDir() . '/' . $this->testFilename;
    if (is_file($filePath)) {
      unlink($filePath);
    }
    parent::tearDown();
  }

  private static function getFilesDir(): string {
    return Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal();
  }

  /**
   * @throws Exception
   */
  private function createPhysicalFile(string $content): void {
    file_put_contents(self::getFilesDir() . '/' . $this->testFilename, $content);
  }

  /**
   * @throws Exception
   */
  private function enqueueRecountFileJob(int $fileId, ?User $user = null): BackgroundJob {
    $job = BackgroundJobUtils::enqueue(DBackgroundJobType::RECOUNT_FILE, ['fileId' => $fileId], $user);
    $this->registerDatabaseObject(Factory::getBackgroundJobFactory(), $job);
    return $job;
  }

  /**
   * @throws HTException
   */
  public function testEnqueueCreatesPendingJobWithUserAndPayload(): void {
    $user = $this->createUser("phpunitBgJob");
    $job = $this->enqueueRecountFileJob(123, $user);

    $this->assertSame(DBackgroundJobStatus::PENDING, $job->getStatus());
    $this->assertSame($user->getId(), $job->getUserId());
    $this->assertGreaterThan(0, $job->getId());
    $this->assertNotNull($job->getCreatedAt());
    $this->assertNull($job->getStartedAt());

    // payload is stored as native JSON and round-trips through the DBA layer
    $job = Factory::getBackgroundJobFactory()->get($job->getId());
    $this->assertNotNull($job);
    $this->assertSame(['fileId' => 123], json_decode($job->getPayload(), true, 512, JSON_THROW_ON_ERROR));
  }

  /**
   * @throws HTException
   */
  public function testEnqueueWithoutUserRecordsSystemAsTrigger(): void {
    $job = $this->enqueueRecountFileJob(123);
    $this->assertNull($job->getUserId());
  }

  /**
   * @throws HTException
   */
  public function testEnqueueUnknownJobTypeThrows(): void {
    $this->expectException(HTException::class);
    BackgroundJobUtils::enqueue('unknown_job_type', []);
  }

  /**
   * @throws Exception
   */
  public function testRunnerProcessesRecountFileJob(): void {
    $user = $this->createUser("phpunitBgJob");
    $file = $this->createFile($this->createAccessGroup("phpunitBgJob"), 0, $this->testFilename);
    $this->createPhysicalFile("one\ntwo\nthree\n");

    $job = $this->enqueueRecountFileJob($file->getId(), $user);
    BackgroundJobRunner::run();

    $job = Factory::getBackgroundJobFactory()->get($job->getId());
    $this->assertSame(DBackgroundJobStatus::DONE, $job->getStatus());
    $this->assertSame(0, $job->getExitCode());
    $this->assertSame("Recounted 3 lines.", $job->getResultMessage());
    $this->assertNotNull($job->getStartedAt());
    $this->assertNotNull($job->getFinishedAt());

    $file = Factory::getFileFactory()->get($file->getId());
    $this->assertSame(3, $file->getLineCount());
  }

  /**
   * @throws Exception
   */
  public function testRunnerFailsJobOnMissingFile(): void {
    $job = $this->enqueueRecountFileJob(999999999);
    BackgroundJobRunner::run();

    $job = Factory::getBackgroundJobFactory()->get($job->getId());
    $this->assertSame(DBackgroundJobStatus::FAILED, $job->getStatus());
    $this->assertSame(-1, $job->getExitCode());
    $this->assertSame("No such file!", $job->getResultMessage());
    $this->assertNotNull($job->getFinishedAt());
  }

  /**
   * @throws Exception
   */
  public function testRunnerMarksStaleRunningJobAsFailed(): void {
    $job = $this->createDatabaseObject(
      Factory::getBackgroundJobFactory(),
      new BackgroundJob(null, DBackgroundJobType::RECOUNT_FILE, '{"fileId": 1}', DBackgroundJobStatus::RUNNING, null, time(), time() - 8000, null, null, null)
    );
    BackgroundJobRunner::run();

    $job = Factory::getBackgroundJobFactory()->get($job->getId());
    $this->assertSame(DBackgroundJobStatus::FAILED, $job->getStatus());
    $this->assertSame(-1, $job->getExitCode());
    $this->assertStringContainsString("timed out", $job->getResultMessage());
  }

  /**
   * @throws Exception
   */
  public function testRunnerProcessesAllPendingJobsAfterEachOther(): void {
    $file = $this->createFile($this->createAccessGroup("phpunitBgJob"), 0, $this->testFilename);
    $this->createPhysicalFile("one\n");

    $first = $this->enqueueRecountFileJob(999999998);
    $second = $this->enqueueRecountFileJob($file->getId());
    BackgroundJobRunner::run();

    $first = Factory::getBackgroundJobFactory()->get($first->getId());
    $second = Factory::getBackgroundJobFactory()->get($second->getId());
    $this->assertSame(DBackgroundJobStatus::FAILED, $first->getStatus());
    $this->assertSame(DBackgroundJobStatus::DONE, $second->getStatus());
    // FIFO: the first enqueued job must have been started before the second one
    $this->assertLessThanOrEqual($second->getStartedAt(), $first->getStartedAt());
  }
}
