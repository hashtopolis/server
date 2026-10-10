<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\defines\DBackgroundJobStatus;
use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\defines\DDirectories;
use Hashtopolis\inc\HTException;

/**
 * Computes and stores the BLAKE3 checksum of a file's content, so clients can verify a download
 * against the bytes the server holds. Wordlists reach sizes where hashing cannot happen inside a
 * request, so the computation runs in the background job runner and the result is cached on the
 * File row.
 *
 * BLAKE3 is not available in any PHP version, so the external b3sum binary does the hashing. It is
 * a hard requirement: when it is missing the job fails and says so, rather than silently leaving
 * files unchecksummed.
 */
class FileChecksumUtils {
  /**
   * Absolute path of the b3sum binary, installed from the distribution package in the Dockerfile.
   */
  private const B3SUM_BINARY = "/usr/bin/b3sum";

  /**
   * BLAKE3's default output is 256 bit, which b3sum prints as 64 lowercase hex characters.
   */
  private const CHECKSUM_PATTERN = "/^[0-9a-f]{64}\$/";

  /**
   * Name under which the digest is advertised in the Repr-Digest header. Not registered in the IANA
   * HTTP Digest Algorithms registry, so only clients which know Hashtopolis will understand it.
   */
  private const DIGEST_ALGORITHM = "blake3";

  /**
   * Hashes the content of the given file with BLAKE3.
   *
   * @param string $path absolute path of the file to hash
   * @return string the checksum as 64 lowercase hex characters
   * @throws HTException if b3sum is unavailable, fails, or returns something unexpected
   */
  public static function computeChecksum(string $path): string {
    if (!is_executable(self::B3SUM_BINARY)) {
      throw new HTException("Checksumming requires " . self::B3SUM_BINARY . ", which is not installed!");
    }
    /* stderr is merged into the output so a failure reason such as a permission problem ends up in
       the exception, and from there in the result message of the background job. Merging is safe
       because the digest is identified by its own format below rather than by position. */
    exec(self::B3SUM_BINARY . " --no-names " . escapeshellarg($path) . " 2>&1", $output, $retval);
    if ($retval !== 0) {
      throw new HTException("Could not compute the checksum: " . self::describeFailure($output, $retval));
    }
    foreach ($output as $line) {
      $line = trim($line);
      if (preg_match(self::CHECKSUM_PATTERN, $line) === 1) {
        return $line;
      }
    }
    throw new HTException("Could not compute the checksum, b3sum returned no digest: " . self::describeFailure($output, $retval));
  }

  /**
   * Renders what b3sum reported into a single line fit for a job result message, which the database
   * caps at 1024 characters.
   *
   * @param string[] $output
   * @param int $retval
   * @return string
   */
  private static function describeFailure(array $output, int $retval): string {
    $message = trim(implode(" ", array_map('trim', $output)));
    if ($message === "") {
      $message = "b3sum exited with code " . $retval;
    }
    return substr($message, 0, 500);
  }

  /**
   * Recomputes the checksum of a file and stores it on the File row.
   *
   * @param int $fileId
   * @return string the computed checksum
   * @throws HTException
   * @throws Exception
   */
  public static function recomputeFile(int $fileId): string {
    $file = Factory::getFileFactory()->get($fileId);
    if ($file === null) {
      throw new HTException("No such file!");
    }
    $filePath = Factory::getStoredValueFactory()->get(DDirectories::FILES)->getVal() . "/" . $file->getFilename();
    if (!file_exists($filePath)) {
      throw new HTException("File not found!");
    }
    $checksum = self::computeChecksum($filePath);
    Factory::getFileFactory()->set($file, File::CHECKSUM, $checksum);
    return $checksum;
  }

  /**
   * Removes the pending checksum jobs of a file, to be called when the file is deleted. Without
   * this they would run against content which is gone and be recorded as failures, cluttering the
   * job list with work nobody is waiting for. A job which is already running is left alone, as it
   * cannot be withdrawn and will report its own outcome.
   *
   * @param int $fileId
   * @throws Exception
   */
  public static function removePendingJobs(int $fileId): void {
    $qF1 = new QueryFilter(BackgroundJob::JOB_TYPE, DBackgroundJobType::CHECKSUM_FILE, "=");
    $qF2 = new QueryFilter(BackgroundJob::STATUS, DBackgroundJobStatus::PENDING, "=");
    $jobs = Factory::getBackgroundJobFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    foreach ($jobs as $job) {
      $payload = json_decode($job->getPayload() ?? "{}", true);
      if (is_array($payload) && ($payload[File::FILE_ID] ?? null) === $fileId) {
        Factory::getBackgroundJobFactory()->delete($job);
      }
    }
  }

  /**
   * Drops the stored checksum of a file, to be called whenever its content on disk changed. Leaves
   * the file without a checksum until it is computed again, which is preferable to handing clients
   * a digest which no longer matches the bytes they download.
   *
   * @param int $fileId
   * @throws Exception
   */
  public static function invalidate(int $fileId): void {
    $file = Factory::getFileFactory()->get($fileId);
    if ($file === null) {
      return;
    }
    Factory::getFileFactory()->set($file, File::CHECKSUM, null);
  }

  /**
   * Enqueues the checksum computation of a file as a background job, unless one is already waiting
   * or running for that file. Without that check a repeated global rescan would queue one job per
   * file per scan, and hashing the same file twice yields the same result anyway.
   *
   * @param int $fileId
   * @param User|null $user user which triggered the computation, null if it was triggered by the system
   * @throws HTException
   * @throws Exception
   */
  public static function enqueue(int $fileId, ?User $user = null): void {
    if (self::hasUnfinishedJob($fileId)) {
      return;
    }
    BackgroundJobUtils::enqueue(DBackgroundJobType::CHECKSUM_FILE, [File::FILE_ID => $fileId], $user);
  }

  /**
   * Enqueues the checksum computation of many files with a single lookup of what is already
   * queued, for callers such as a global rescan which would otherwise repeat that lookup per file.
   *
   * @param int[] $fileIds
   * @param User|null $user
   * @throws HTException
   * @throws Exception
   */
  public static function enqueueMany(array $fileIds, ?User $user = null): void {
    $queued = self::fileIdsWithUnfinishedJob();
    foreach (array_unique($fileIds) as $fileId) {
      if (in_array($fileId, $queued, true)) {
        continue;
      }
      BackgroundJobUtils::enqueue(DBackgroundJobType::CHECKSUM_FILE, [File::FILE_ID => $fileId], $user);
      $queued[] = $fileId;
    }
  }

  /**
   * Checks whether a checksum job for the given file is already pending or running.
   *
   * @param int $fileId
   * @return bool
   * @throws Exception
   */
  private static function hasUnfinishedJob(int $fileId): bool {
    return in_array($fileId, self::fileIdsWithUnfinishedJob(), true);
  }

  /**
   * File ids which already have a pending or running checksum job. The id lives inside the JSON
   * payload, which cannot be filtered on portably across MySQL and PostgreSQL, so the unfinished
   * jobs are fetched once and decoded here.
   *
   * @return int[]
   * @throws Exception
   */
  private static function fileIdsWithUnfinishedJob(): array {
    $qF1 = new QueryFilter(BackgroundJob::JOB_TYPE, DBackgroundJobType::CHECKSUM_FILE, "=");
    $qF2 = new ContainFilter(BackgroundJob::STATUS, [DBackgroundJobStatus::PENDING, DBackgroundJobStatus::RUNNING]);
    $jobs = Factory::getBackgroundJobFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    $fileIds = [];
    foreach ($jobs as $job) {
      $payload = json_decode($job->getPayload() ?? "{}", true);
      if (is_array($payload) && is_int($payload[File::FILE_ID] ?? null)) {
        $fileIds[] = $payload[File::FILE_ID];
      }
    }
    return $fileIds;
  }

  /**
   * Formats a stored checksum as the value of a RFC 9530 Repr-Digest header, which carries the
   * digest as a base64 encoded byte sequence wrapped in colons.
   *
   * @param string|null $checksum checksum as stored on the File row, null if it was not computed yet
   * @return string|null the header value, or null if there is no checksum to advertise
   */
  public static function toReprDigest(?string $checksum): ?string {
    if ($checksum === null || preg_match(self::CHECKSUM_PATTERN, $checksum) !== 1) {
      return null;
    }
    return self::DIGEST_ALGORITHM . "=:" . base64_encode(hex2bin($checksum)) . ":";
  }
}
