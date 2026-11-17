<?php

namespace Hashtopolis\inc\jobs\handlers;

use Exception;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\defines\DBackgroundJobStatus;
use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\jobs\BackgroundJobResult;
use Hashtopolis\inc\jobs\PeriodicBackgroundJobHandler;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\utils\RefreshTokenUtils;
use Hashtopolis\inc\Util;
use Throwable;

/**
 * Periodic server housekeeping. This used to be done inline by Util::cleaning() on every
 * agent progress report, which made an agent wait for the cleanup to finish before it got
 * its response.
 */
class CleaningJob implements PeriodicBackgroundJobHandler {
  /** fallback interval in seconds, matching the cadence the request path throttle used */
  private const DEFAULT_INTERVAL = 600;
  /** terminal background jobs older than this are dropped from the job history */
  private const JOB_HISTORY_LIFETIME = 2592000; // 30 days
  
  public static function getJobType(): string {
    return DBackgroundJobType::CLEANING;
  }
  
  public static function getPayloadDefinition(): array {
    return [];
  }
  
  public function getIntervalSeconds(): int {
    $minutes = intval(SConfig::getInstance()->getVal(DConfig::CLEANING_INTERVAL));
    if ($minutes <= 0) {
      // nothing guarantees a config row exists for every item, so stay defensive here
      return self::DEFAULT_INTERVAL;
    }
    return $minutes * 60;
  }
  
  /**
   * This is not a timeout, as nothing interrupts a running job: the runner holds an
   * exclusive lock for its whole pass, so recoverStale() only ever sees running jobs whose
   * runner died. It therefore only decides how long a killed runner keeps the next
   * scheduled run from starting.
   */
  public function getMaxRuntime(): int {
    return 1800;
  }
  
  /**
   * Every step runs on its own, so a single failing one neither hides the results of the
   * others nor keeps them from running at all.
   */
  public function execute(BackgroundJob $job, array $payload): BackgroundJobResult {
    $steps = [
      "agent statistics" => fn() => Util::agentStatCleaning() . " rows",
      "zap queue" => fn() => Util::zapCleaning() . " rows",
      "stale uploads" => fn() => Util::tusFileCleaning() . " files",
      "expired refresh tokens" => fn() => RefreshTokenUtils::purgeExpired() . " rows",
      "job history" => fn() => self::pruneJobHistory() . " rows",
    ];
    
    $messages = [];
    $failed = 0;
    foreach ($steps as $name => $step) {
      try {
        $messages[] = "$name: " . $step();
      }
      catch (Throwable $t) {
        $failed++;
        $messages[] = "$name failed: " . $t->getMessage();
      }
    }
    
    return new BackgroundJobResult(($failed === 0) ? 0 : -1, substr(implode("; ", $messages), 0, 1024));
  }
  
  /**
   * Drops finished and failed jobs from the job history, which would otherwise grow by one
   * row per interval forever and bury the jobs someone actually triggered.
   *
   * Pending and running jobs are never touched, which also excludes the row of this job
   * itself. createdAt is used instead of finishedAt as it can never be null, and a row with
   * a null finishedAt would never match and therefore never be cleaned up.
   *
   * There is no portable way to limit a deletion, so the first run on an install which has
   * been collecting jobs for a long time issues one large deletion. That is acceptable in
   * a background job.
   *
   * @return int number of deleted jobs
   * @throws Exception
   */
  private static function pruneJobHistory(): int {
    $qF1 = new ContainFilter(BackgroundJob::STATUS, [DBackgroundJobStatus::DONE, DBackgroundJobStatus::FAILED]);
    $qF2 = new QueryFilter(BackgroundJob::CREATED_AT, time() - self::JOB_HISTORY_LIFETIME, "<=");
    return Factory::getBackgroundJobFactory()->massDeletion([Factory::FILTER => [$qF1, $qF2]])->rowCount();
  }
}
