<?php

namespace Hashtopolis\inc\jobs;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\models\File;
use Hashtopolis\inc\defines\DBackgroundJobStatus;
use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\defines\DDirectories;
use Hashtopolis\inc\jobs\handlers\ChecksumFileJob;
use Hashtopolis\inc\jobs\payload\JobPayloadField;
use Hashtopolis\inc\utils\BackgroundJobUtils;
use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class ChecksumFileJobTest extends TestBase {
  private const HELLO_CHECKSUM = 'ea8f163db38682925e4491c5e58d4bb3506ef8c14eb78a86e908c5624a67200f';

  private string $testFilename;

  #[Override]
  protected function setUp(): void {
    parent::setUp();
    $this->testFilename = 'phpunit_checksumjob_' . uniqid() . '.txt';
  }

  #[Override]
  protected function tearDown(): void {
    $filePath = self::getFilesDir() . '/' . $this->testFilename;
    if (is_file($filePath)) {
      unlink($filePath);
    }
    parent::tearDown();
  }

  /**
   * @throws Exception
   */
  private static function getFilesDir(): string {
    return Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal();
  }

  /**
   * @throws Exception
   */
  private function enqueueChecksumJob(int $fileId): BackgroundJob {
    $job = BackgroundJobUtils::enqueue(DBackgroundJobType::CHECKSUM_FILE, [File::FILE_ID => $fileId]);
    $this->registerDatabaseObject(Factory::getBackgroundJobFactory(), $job);
    return $job;
  }

  public function testJobTypeIsRegistered(): void {
    $this->assertSame(DBackgroundJobType::CHECKSUM_FILE, ChecksumFileJob::getJobType());
    $this->assertContains(DBackgroundJobType::CHECKSUM_FILE, BackgroundJobRegistry::getRegisteredTypes());
    $this->assertInstanceOf(ChecksumFileJob::class, BackgroundJobRegistry::getHandler(DBackgroundJobType::CHECKSUM_FILE));
  }

  public function testPayloadDefinitionRequiresTheFileId(): void {
    $definition = ChecksumFileJob::getPayloadDefinition();
    $this->assertArrayHasKey(File::FILE_ID, $definition);
    $this->assertSame(JobPayloadField::TYPE_INT, $definition[File::FILE_ID]->type);
    $this->assertTrue($definition[File::FILE_ID]->required);
  }

  /**
   * Hashing a 100 GB wordlist has to be allowed to take a while before the runner declares the job
   * stale and fails it.
   */
  public function testMaxRuntimeLeavesRoomForVeryLargeFiles(): void {
    $this->assertGreaterThanOrEqual(7200, (new ChecksumFileJob())->getMaxRuntime());
  }

  /**
   * @throws Exception
   */
  public function testExecuteStoresTheChecksumAndSucceeds(): void {
    $group = $this->createAccessGroup('phpunitChecksumJob');
    $file = $this->createFile($group, 0, $this->testFilename);
    file_put_contents(self::getFilesDir() . '/' . $this->testFilename, 'hello');

    $job = $this->enqueueChecksumJob($file->getId());
    $result = (new ChecksumFileJob())->execute($job, [File::FILE_ID => $file->getId()]);

    $this->assertSame(0, $result->getExitCode());
    $this->assertStringContainsString(self::HELLO_CHECKSUM, $result->getMessage());
    $this->assertSame(self::HELLO_CHECKSUM, Factory::getFileFactory()->get($file->getId())->getChecksum());
  }

  /**
   * A missing file has to be reported as a failed job rather than escaping as an exception, so the
   * runner records the reason instead of treating it as a crash.
   * @throws Exception
   */
  public function testExecuteReportsAMissingFileAsFailure(): void {
    $group = $this->createAccessGroup('phpunitChecksumJob');
    $file = $this->createFile($group, 0, $this->testFilename);

    $job = $this->enqueueChecksumJob($file->getId());
    $result = (new ChecksumFileJob())->execute($job, [File::FILE_ID => $file->getId()]);

    $this->assertSame(-1, $result->getExitCode());
    $this->assertSame('File not found!', $result->getMessage());
    $this->assertNull(Factory::getFileFactory()->get($file->getId())->getChecksum());
  }

  /**
   * @throws Exception
   */
  public function testExecuteReportsAnUnknownFileAsFailure(): void {
    $job = $this->enqueueChecksumJob(0);
    $result = (new ChecksumFileJob())->execute($job, [File::FILE_ID => 0]);

    $this->assertSame(-1, $result->getExitCode());
    $this->assertSame('No such file!', $result->getMessage());
  }

  /**
   * End to end through the runner, which is how the job is actually reached in production.
   * @throws Exception
   */
  public function testRunnerProcessesAPendingChecksumJob(): void {
    $group = $this->createAccessGroup('phpunitChecksumJob');
    $file = $this->createFile($group, 0, $this->testFilename);
    file_put_contents(self::getFilesDir() . '/' . $this->testFilename, 'hello');

    $job = $this->enqueueChecksumJob($file->getId());
    BackgroundJobRunner::run();

    $job = Factory::getBackgroundJobFactory()->get($job->getId());
    $this->assertNotNull($job);
    $this->assertSame(DBackgroundJobStatus::DONE, $job->getStatus());
    $this->assertSame(0, $job->getExitCode());
    $this->assertNotNull($job->getStartedAt());
    $this->assertNotNull($job->getFinishedAt());
    $this->assertSame(self::HELLO_CHECKSUM, Factory::getFileFactory()->get($file->getId())->getChecksum());
  }
}
