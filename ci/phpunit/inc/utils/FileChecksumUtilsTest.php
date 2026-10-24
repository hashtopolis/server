<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\defines\DBackgroundJobStatus;
use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\defines\DDirectories;
use Hashtopolis\inc\HTException;
use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class FileChecksumUtilsTest extends TestBase {
  /**
   * Published BLAKE3 test vectors, which b3sum must reproduce.
   */
  private const EMPTY_CHECKSUM = 'af1349b9f5f9a1a6a0404dea36dcc9499bcb25c9adc112b7cc9a93cae41f3262';
  private const HELLO_CHECKSUM = 'ea8f163db38682925e4491c5e58d4bb3506ef8c14eb78a86e908c5624a67200f';

  private string $testFilename;

  #[Override]
  protected function setUp(): void {
    parent::setUp();
    $this->testFilename = 'phpunit_checksum_' . uniqid() . '.txt';
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
  private function createPhysicalFile(string $content): string {
    $path = self::getFilesDir() . '/' . $this->testFilename;
    file_put_contents($path, $content);
    return $path;
  }

  /**
   * @throws Exception
   */
  private function unfinishedChecksumJobs(): array {
    $qF = new QueryFilter(BackgroundJob::JOB_TYPE, DBackgroundJobType::CHECKSUM_FILE, "=");
    return Factory::getBackgroundJobFactory()->filter([Factory::FILTER => $qF]);
  }

  /* ---------------------------------------------------------------- computeChecksum */

  /**
   * @throws Exception
   */
  public function testComputeChecksumMatchesKnownVectorForEmptyFile(): void {
    $path = $this->createPhysicalFile('');
    $this->assertSame(self::EMPTY_CHECKSUM, FileChecksumUtils::computeChecksum($path));
  }

  /**
   * @throws Exception
   */
  public function testComputeChecksumMatchesKnownVectorForContent(): void {
    $path = $this->createPhysicalFile('hello');
    $this->assertSame(self::HELLO_CHECKSUM, FileChecksumUtils::computeChecksum($path));
  }

  /**
   * @throws Exception
   */
  public function testComputeChecksumIsStableAcrossCalls(): void {
    $path = $this->createPhysicalFile(str_repeat('wordlist line', 5000));
    $this->assertSame(FileChecksumUtils::computeChecksum($path), FileChecksumUtils::computeChecksum($path));
  }

  /**
   * A filename containing shell metacharacters must be passed to b3sum as a single argument rather
   * than interpreted, so the checksum is still the one of that file's content.
   * @throws Exception
   */
  public function testComputeChecksumHandlesFilenameWithShellMetacharacters(): void {
    $this->testFilename = "phpunit_checksum_'; echo pwned; #" . uniqid() . '.txt';
    $path = $this->createPhysicalFile('hello');
    $this->assertSame(self::HELLO_CHECKSUM, FileChecksumUtils::computeChecksum($path));
  }

  /**
   * The reason b3sum gives has to survive into the exception, as that is what ends up in the result
   * message of the background job and is all an operator gets to debug with.
   * @throws Exception
   */
  public function testComputeChecksumOfMissingFileThrowsWithTheReportedReason(): void {
    $this->expectException(HTException::class);
    $this->expectExceptionMessageMatches('/No such file or directory/');
    FileChecksumUtils::computeChecksum(self::getFilesDir() . '/does_not_exist_' . uniqid());
  }

  /* ----------------------------------------------------------------- recomputeFile */

  /**
   * @throws Exception
   */
  public function testRecomputeFileStoresChecksumOnTheFileRow(): void {
    $group = $this->createAccessGroup('phpunitChecksum');
    $file = $this->createFile($group, 0, $this->testFilename);
    $this->createPhysicalFile('hello');

    $this->assertNull($file->getChecksum());
    $returned = FileChecksumUtils::recomputeFile($file->getId());

    $this->assertSame(self::HELLO_CHECKSUM, $returned);
    $this->assertSame(self::HELLO_CHECKSUM, Factory::getFileFactory()->get($file->getId())->getChecksum());
  }

  /**
   * @throws Exception
   */
  public function testRecomputeFileThrowsForUnknownFileId(): void {
    $this->expectException(HTException::class);
    $this->expectExceptionMessage('No such file!');
    FileChecksumUtils::recomputeFile(0);
  }

  /**
   * @throws Exception
   */
  public function testRecomputeFileThrowsWhenContentIsMissingOnDisk(): void {
    $group = $this->createAccessGroup('phpunitChecksum');
    $file = $this->createFile($group, 0, $this->testFilename);

    $this->expectException(HTException::class);
    $this->expectExceptionMessage('File not found!');
    FileChecksumUtils::recomputeFile($file->getId());
  }

  /* ------------------------------------------------------------------- invalidate */

  /**
   * @throws Exception
   */
  public function testInvalidateClearsAStoredChecksum(): void {
    $group = $this->createAccessGroup('phpunitChecksum');
    $file = $this->createFile($group, 0, $this->testFilename);
    $this->createPhysicalFile('hello');
    FileChecksumUtils::recomputeFile($file->getId());

    FileChecksumUtils::invalidate($file->getId());
    $this->assertNull(Factory::getFileFactory()->get($file->getId())->getChecksum());
  }

  /**
   * An unknown id must be ignored rather than throwing or clearing anything else, as scanFiles
   * walks rows which may disappear underneath it.
   * @throws Exception
   */
  public function testInvalidateOfUnknownFileIdLeavesOtherChecksumsAlone(): void {
    $group = $this->createAccessGroup('phpunitChecksum');
    $file = $this->createFile($group, 0, $this->testFilename);
    $this->createPhysicalFile('hello');
    FileChecksumUtils::recomputeFile($file->getId());

    FileChecksumUtils::invalidate(0);

    $this->assertSame(self::HELLO_CHECKSUM, Factory::getFileFactory()->get($file->getId())->getChecksum());
  }

  /* ---------------------------------------------------------------------- enqueue */

  /**
   * @throws Exception
   */
  public function testEnqueueCreatesAPendingChecksumJob(): void {
    $group = $this->createAccessGroup('phpunitChecksum');
    $file = $this->createFile($group, 0, $this->testFilename);

    FileChecksumUtils::enqueue($file->getId());

    $jobs = $this->unfinishedChecksumJobs();
    $matching = [];
    foreach ($jobs as $job) {
      $this->registerDatabaseObject(Factory::getBackgroundJobFactory(), $job);
      $payload = json_decode($job->getPayload(), true, 512, JSON_THROW_ON_ERROR);
      if (($payload[File::FILE_ID] ?? null) === $file->getId()) {
        $matching[] = $job;
      }
    }

    $this->assertCount(1, $matching);
    $this->assertSame(DBackgroundJobStatus::PENDING, $matching[0]->getStatus());
    $this->assertSame(DBackgroundJobType::CHECKSUM_FILE, $matching[0]->getJobType());
    $this->assertNull($matching[0]->getUserId());
  }

  /**
   * Enqueueing twice must not queue the work twice, otherwise a repeated global rescan would pile
   * up one job per file per scan.
   * @throws Exception
   */
  public function testEnqueueIsIdempotentWhileAJobIsStillPending(): void {
    $group = $this->createAccessGroup('phpunitChecksum');
    $file = $this->createFile($group, 0, $this->testFilename);

    FileChecksumUtils::enqueue($file->getId());
    FileChecksumUtils::enqueue($file->getId());

    $matching = 0;
    foreach ($this->unfinishedChecksumJobs() as $job) {
      $this->registerDatabaseObject(Factory::getBackgroundJobFactory(), $job);
      $payload = json_decode($job->getPayload(), true, 512, JSON_THROW_ON_ERROR);
      if (($payload[File::FILE_ID] ?? null) === $file->getId()) {
        $matching++;
      }
    }

    $this->assertSame(1, $matching);
  }

  /**
   * The bulk path exists so a rescan does one lookup instead of one per file; it has to dedup
   * against both what is already queued and what it queued itself in the same call.
   * @throws Exception
   */
  public function testEnqueueManyQueuesEachFileExactlyOnce(): void {
    $group = $this->createAccessGroup('phpunitChecksum');
    $fileA = $this->createFile($group, 0, 'phpunit_checksum_a_' . uniqid());
    $fileB = $this->createFile($group, 0, 'phpunit_checksum_b_' . uniqid());

    // fileA already has a job, and fileB is asked for twice in the same call
    FileChecksumUtils::enqueue($fileA->getId());
    FileChecksumUtils::enqueueMany([$fileA->getId(), $fileB->getId(), $fileB->getId()]);

    $counts = [$fileA->getId() => 0, $fileB->getId() => 0];
    foreach ($this->unfinishedChecksumJobs() as $job) {
      $this->registerDatabaseObject(Factory::getBackgroundJobFactory(), $job);
      $payload = json_decode($job->getPayload(), true, 512, JSON_THROW_ON_ERROR);
      $fileId = $payload[File::FILE_ID] ?? null;
      if (array_key_exists($fileId, $counts)) {
        $counts[$fileId]++;
      }
    }

    $this->assertSame(1, $counts[$fileA->getId()]);
    $this->assertSame(1, $counts[$fileB->getId()]);
  }

  /* ------------------------------------------------------- removePendingJobs */

  /**
   * @throws Exception
   */
  public function testRemovePendingJobsDropsTheJobOfADeletedFile(): void {
    $group = $this->createAccessGroup('phpunitChecksum');
    $file = $this->createFile($group, 0, $this->testFilename);
    FileChecksumUtils::enqueue($file->getId());

    FileChecksumUtils::removePendingJobs($file->getId());

    foreach ($this->unfinishedChecksumJobs() as $job) {
      $payload = json_decode($job->getPayload(), true, 512, JSON_THROW_ON_ERROR);
      $this->assertNotSame($file->getId(), $payload[File::FILE_ID] ?? null);
    }
  }

  /**
   * A job which is already running cannot be withdrawn, so it has to survive.
   * @throws Exception
   */
  public function testRemovePendingJobsLeavesARunningJobAlone(): void {
    $group = $this->createAccessGroup('phpunitChecksum');
    $file = $this->createFile($group, 0, $this->testFilename);
    FileChecksumUtils::enqueue($file->getId());

    $factory = Factory::getBackgroundJobFactory();
    $running = null;
    foreach ($this->unfinishedChecksumJobs() as $job) {
      $payload = json_decode($job->getPayload(), true, 512, JSON_THROW_ON_ERROR);
      if (($payload[File::FILE_ID] ?? null) === $file->getId()) {
        $running = $factory->set($job, BackgroundJob::STATUS, DBackgroundJobStatus::RUNNING);
        $this->registerDatabaseObject($factory, $running);
      }
    }
    $this->assertNotNull($running);

    FileChecksumUtils::removePendingJobs($file->getId());

    $this->assertNotNull($factory->get($running->getId()));
  }

  /* ---------------------------------------------------------------- toReprDigest */

  public function testToReprDigestEncodesTheDigestAsRfc9530ByteSequence(): void {
    $this->assertSame(
      'blake3=:rxNJufX5oaagQE3qNtzJSZvLJcmtwRK3zJqTyuQfMmI=:',
      FileChecksumUtils::toReprDigest(self::EMPTY_CHECKSUM)
    );
  }

  public function testToReprDigestReturnsNullWithoutAChecksum(): void {
    $this->assertNull(FileChecksumUtils::toReprDigest(null));
  }

  /**
   * Guards against emitting a malformed header if the column ever holds something unexpected.
   */
  public function testToReprDigestRejectsAMalformedChecksum(): void {
    $this->assertNull(FileChecksumUtils::toReprDigest('not-a-checksum'));
    $this->assertNull(FileChecksumUtils::toReprDigest(''));
    $this->assertNull(FileChecksumUtils::toReprDigest(strtoupper(self::EMPTY_CHECKSUM)));
    $this->assertNull(FileChecksumUtils::toReprDigest(substr(self::EMPTY_CHECKSUM, 0, 63)));
  }
}
