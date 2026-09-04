<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\defines\DBackgroundJobStatus;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\jobs\BackgroundJobRegistry;

class BackgroundJobUtils {
  /**
   * Enqueues a new background job for the given job type.
   *
   * @param string $jobType job type identifier, must be registered in the BackgroundJobRegistry
   * @param array $payload JSON-serializable job parameters
   * @param User|null $user user which triggered the job, null if it was triggered by the system
   * @return BackgroundJob the enqueued job in pending state
   * @throws HTException
   */
  public static function enqueue(string $jobType, array $payload = [], ?User $user = null): BackgroundJob {
    if (!in_array($jobType, BackgroundJobRegistry::getRegisteredTypes(), true)) {
      throw new HTException("Unknown background job type '$jobType'!");
    }
    $encoded = json_encode($payload);
    if ($encoded === false) {
      throw new HTException("Could not encode background job payload!");
    }
    $job = new BackgroundJob(null, $jobType, $encoded, DBackgroundJobStatus::PENDING, ($user == null) ? null : $user->getId(), time(), null, null, null, null);
    $job = Factory::getBackgroundJobFactory()->save($job);
    if ($job === null) {
      throw new HTException("Could not enqueue background job!");
    }
    DServerLog::log(DServerLog::INFO, "Background job " . $job->getId() . " (" . $jobType . ") enqueued", [$job]);
    return $job;
  }

  /**
   * @throws HTException
   */
  public static function getJob(int $jobId): BackgroundJob {
    $job = Factory::getBackgroundJobFactory()->get($jobId);
    if ($job === null) {
      throw new HTException("No such background job!");
    }
    return $job;
  }

  public static function deleteJob(BackgroundJob $job): void {
    Factory::getBackgroundJobFactory()->delete($job);
  }
}
