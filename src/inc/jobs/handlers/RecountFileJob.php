<?php

namespace Hashtopolis\inc\jobs\handlers;

use Exception;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\models\File;
use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\jobs\BackgroundJobHandler;
use Hashtopolis\inc\jobs\BackgroundJobResult;
use Hashtopolis\inc\jobs\payload\JobPayloadField;
use Hashtopolis\inc\utils\FileUtils;

class RecountFileJob implements BackgroundJobHandler {
  public static function getJobType(): string {
    return DBackgroundJobType::RECOUNT_FILE;
  }

  public static function getPayloadDefinition(): array {
    return [
      File::FILE_ID => new JobPayloadField(JobPayloadField::TYPE_INT),
    ];
  }

  public function getMaxRuntime(): int {
    return 7200;
  }
  
  /**
   * @throws Exception
   */
  public function execute(BackgroundJob $job, array $payload): BackgroundJobResult {
    try {
      $count = FileUtils::fileCountLines($payload[File::FILE_ID]);
    }
    catch (HTException $e) {
      return new BackgroundJobResult(-1, $e->getMessage());
    }
    return new BackgroundJobResult(0, "Recounted $count lines.");
  }
}
