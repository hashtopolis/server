<?php

namespace Hashtopolis\inc\jobs;

use Exception;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\models\StoredValue;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\defines\DBackgroundJobStatus;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\BackgroundJobUtils;
use Hashtopolis\inc\utils\Lock;
use Hashtopolis\inc\utils\LockUtils;
use Throwable;

/**
 * Enqueues the background jobs which are supposed to run on a fixed interval instead of
 * being triggered by a user action. See PeriodicBackgroundJobHandler.
 *
 * The runner is started once a minute by a short-lived process, so an interval cannot be
 * waited out in memory. Instead the time a job type was last enqueued is kept in a
 * StoredValue row and compared on every pass.
 */
class PeriodicJobScheduler {
  /**
   * Prefix of the StoredValue key holding the unix time a periodic job type was last
   * enqueued. The schedule is anchored there and not on the BackgroundJob rows themselves,
   * so neither deleting a job through the API nor pruning the job history can disturb the
   * cadence.
   */
  private const LAST_RUN_PREFIX = "periodic_";
  /** StoredValue::STORED_VALUE_ID is a varchar(50) on MySQL, while it is a text on PostgreSQL */
  private const MAX_KEY_LENGTH = 50;
  
  /**
   * Enqueues one job for every registered periodic job type whose interval has elapsed.
   * Nothing is executed here, BackgroundJobRunner::run() picks the jobs up afterwards.
   *
   * A dedicated lock keeps two runner invocations on the same host from scheduling the same
   * job twice. Across hosts the pending/running check is the only guard and it is not
   * atomic, so a duplicate run remains possible and periodic jobs have to be idempotent.
   */
  public static function scheduleDue(): void {
    if (!LockUtils::tryGet(Lock::PERIODIC_JOBS)) {
      return;
    }
    try {
      $now = time();
      foreach (BackgroundJobRegistry::getPeriodicHandlers() as $jobType => $handler) {
        try {
          self::scheduleType($jobType, $handler, $now);
        }
        catch (Throwable $t) {
          // one unschedulable job type must neither skip the remaining ones nor fail the
          // runner process, which the integration tests assert exits successfully
          DServerLog::log(DServerLog::ERROR, "Could not schedule periodic background job '$jobType': " . $t->getMessage());
        }
      }
    }
    finally {
      LockUtils::release(Lock::PERIODIC_JOBS);
    }
  }
  
  /**
   * @throws Exception
   * @throws HTException
   */
  private static function scheduleType(string $jobType, PeriodicBackgroundJobHandler $handler, int $now): void {
    $entry = self::getLastRunEntry($jobType);
    if ($now - intval($entry->getVal()) < $handler->getIntervalSeconds()) {
      return;
    }
    if (self::hasUnfinishedJob($jobType)) {
      // the previous run is still queued or executing, never stack a second one on top
      return;
    }
    // the anchor is stamped before enqueueing on purpose: a failed enqueue only costs one
    // interval, while a failed stamp after a successful enqueue would schedule another job
    // on every following pass as soon as this one reaches a terminal state
    Factory::getStoredValueFactory()->set($entry, StoredValue::VAL, strval($now));
    // enqueued without a user, as it is triggered by the server and not by anyone
    BackgroundJobUtils::enqueue($jobType);
  }
  
  /**
   * @throws Exception
   */
  private static function hasUnfinishedJob(string $jobType): bool {
    $qF1 = new QueryFilter(BackgroundJob::JOB_TYPE, $jobType, "=");
    $qF2 = new ContainFilter(BackgroundJob::STATUS, [DBackgroundJobStatus::PENDING, DBackgroundJobStatus::RUNNING]);
    return Factory::getBackgroundJobFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true) !== null;
  }
  
  /**
   * Retrieves the anchor of the given job type, creating it when the job type has never
   * been scheduled before. It then starts out at 0 so the job runs on the very first pass.
   *
   * @throws Exception
   * @throws HTException
   */
  private static function getLastRunEntry(string $jobType): StoredValue {
    $key = self::getLastRunKey($jobType);
    $entry = Factory::getStoredValueFactory()->get($key);
    if ($entry === null) {
      $entry = new StoredValue($key, "0");
      $entry = Factory::getStoredValueFactory()->save($entry);
      if ($entry === null) {
        throw new HTException("Could not create the schedule of periodic job type '$jobType'!");
      }
    }
    return $entry;
  }
  
  /**
   * @return string key of the StoredValue row anchoring the schedule of this job type
   * @throws HTException
   */
  public static function getLastRunKey(string $jobType): string {
    $key = self::LAST_RUN_PREFIX . $jobType;
    if (strlen($key) > self::MAX_KEY_LENGTH) {
      throw new HTException("Job type '$jobType' is too long to be scheduled periodically!");
    }
    return $key;
  }
}
