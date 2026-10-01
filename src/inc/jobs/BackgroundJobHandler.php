<?php

namespace Hashtopolis\inc\jobs;

use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\inc\jobs\payload\JobPayloadField;

interface BackgroundJobHandler {
  /**
   * @return string job type identifier matching the DBackgroundJobType constant value
   */
  public static function getJobType(): string;

  /**
   * Defines the structure of the payload this handler expects. Validated when a job is
   * enqueued and before it is executed, so the handler can rely on the declared fields.
   *
   * @return array<string, JobPayloadField> payload key to field definition
   */
  public static function getPayloadDefinition(): array;

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
