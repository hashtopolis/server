<?php

namespace Hashtopolis\inc\jobs;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\inc\defines\DBackgroundJobStatus;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\Lock;
use Hashtopolis\inc\utils\LockUtils;
use Throwable;

class BackgroundJobRunner {
  /**
   * Processes all pending background jobs, one after another. If another run is still
   * active, this call returns immediately without doing anything.
   * @throws Exception
   */
  public static function run(): void {
    if (!LockUtils::tryGet(Lock::BACKGROUND_JOBS)) {
      return;
    }
    try {
      self::recoverStale();
      $factory = Factory::getBackgroundJobFactory();
      while (true) {
        $job = $factory->filter([
          Factory::FILTER => new QueryFilter(BackgroundJob::STATUS, DBackgroundJobStatus::PENDING, "="),
        ], true);
        if ($job === null) {
          break;
        }
        if (!self::claim($job)) {
          continue;
        }
        self::executeJob($job);
      }
    }
    finally {
      LockUtils::release(Lock::BACKGROUND_JOBS);
    }
  }
  
  /**
   * Atomically transitions a pending job into the running state. Returns false if the
   * job was claimed by another runner instance in the meantime.
   * @throws Exception
   */
  private static function claim(BackgroundJob $job): bool {
    $factory = Factory::getBackgroundJobFactory();
    $query = "UPDATE " . $factory->getMappedModelTable() . " SET " . BackgroundJob::STATUS . "=?, " . BackgroundJob::STARTED_AT .
      "=? WHERE " . BackgroundJob::BACKGROUND_JOB_ID . "=? AND " . BackgroundJob::STATUS . "=?";
    $stmt = $factory->getDB()->prepare($query);
    $stmt->execute([DBackgroundJobStatus::RUNNING, time(), $job->getId(), DBackgroundJobStatus::PENDING]);
    return $stmt->rowCount() > 0;
  }
  
  /**
   * Marks running jobs as failed which were left behind by a crashed or killed runner,
   * which exceeded their maximum runtime, or which no longer have a registered handler.
   * @throws Exception
   */
  private static function recoverStale(): void {
    $factory = Factory::getBackgroundJobFactory();
    $running = $factory->filter([Factory::FILTER => new QueryFilter(BackgroundJob::STATUS, DBackgroundJobStatus::RUNNING, "=")]);
    foreach ($running as $job) {
      $handlerClass = BackgroundJobRegistry::getHandlerClass($job->getJobType());
      $handler = ($handlerClass === null) ? null : new $handlerClass();
      $stale = ($job->getStartedAt() === null) || ($handler === null) || (time() - $job->getStartedAt() > $handler->getMaxRuntime());
      if ($stale) {
        $reason = ($handler === null) ? "No handler registered for job type '" . $job->getJobType() . "'!" : "Job timed out or runner died!";
        $factory->mset($job, [
          BackgroundJob::STATUS => DBackgroundJobStatus::FAILED,
          BackgroundJob::EXIT_CODE => -1,
          BackgroundJob::RESULT_MESSAGE => $reason,
          BackgroundJob::FINISHED_AT => time(),
        ]);
        DServerLog::log(DServerLog::WARNING, "Background job " . $job->getId() . " (" . $job->getJobType() . ") was stale and got marked as failed: " . $reason, [$job]);
      }
    }
  }
  
  /**
   * Executes a single claimed job and records its result, exit code and message. Any
   * uncaught throwable is caught and recorded as a failed execution.
   * @throws Exception
   */
  private static function executeJob(BackgroundJob $job): void {
    $factory = Factory::getBackgroundJobFactory();
    DServerLog::log(DServerLog::INFO, "Background job " . $job->getId() . " (" . $job->getJobType() . ") started", [$job]);
    try {
      $handler = BackgroundJobRegistry::getHandler($job->getJobType());
      if ($handler === null) {
        throw new HTException("No handler registered for job type '" . $job->getJobType() . "'!");
      }
      $payload = json_decode($job->getPayload() ?? "{}", true, 512, JSON_THROW_ON_ERROR);
      if (!is_array($payload)) {
        throw new HTException("Invalid job payload!");
      }
      $result = $handler->execute($job, $payload);
      $factory->mset($job, [
        BackgroundJob::STATUS => ($result->getExitCode() === 0) ? DBackgroundJobStatus::DONE : DBackgroundJobStatus::FAILED,
        BackgroundJob::EXIT_CODE => $result->getExitCode(),
        BackgroundJob::RESULT_MESSAGE => $result->getMessage(),
        BackgroundJob::FINISHED_AT => time(),
      ]);
      DServerLog::log(DServerLog::INFO, "Background job " . $job->getId() . " (" . $job->getJobType() . ") finished with exit code " . $result->getExitCode());
    }
    catch (Throwable $t) {
      $message = substr($t->getMessage(), 0, 1024);
      $factory->mset($job, [
        BackgroundJob::STATUS => DBackgroundJobStatus::FAILED,
        BackgroundJob::EXIT_CODE => 255,
        BackgroundJob::RESULT_MESSAGE => $message,
        BackgroundJob::FINISHED_AT => time(),
      ]);
      DServerLog::log(DServerLog::ERROR, "Background job " . $job->getId() . " (" . $job->getJobType() . ") failed: " . $message);
    }
  }
}
