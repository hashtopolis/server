<?php

namespace Hashtopolis\inc\jobs;

use Hashtopolis\dba\models\BackgroundJob;

interface BackgroundJobHandler {
  /**
   * @return string job type identifier matching the DBackgroundJobType constant value
   */
  public static function getJobType(): string;

  /**
   * @return int maximum runtime in seconds, after which a running job is considered stale
   */
  public function getMaxRuntime(): int;

  /**
   * @param BackgroundJob $job claimed job which is about to be executed
   * @param array $payload decoded JSON payload of the job
   * @return BackgroundJobResult exit code 0 marks success, anything else marks failure
   */
  public function execute(BackgroundJob $job, array $payload): BackgroundJobResult;
}
