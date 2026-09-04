<?php

namespace Hashtopolis\inc\jobs;

use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\models\File;
use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\FileUtils;

class RecountFileJob implements BackgroundJobHandler {
  public static function getJobType(): string {
    return DBackgroundJobType::RECOUNT_FILE;
  }

  public function getMaxRuntime(): int {
    return 7200;
  }

  public function execute(BackgroundJob $job, array $payload): BackgroundJobResult {
    if (!isset($payload[File::FILE_ID]) || !is_int($payload[File::FILE_ID])) {
      return new BackgroundJobResult(-1, "Missing or invalid '" . File::FILE_ID . "' in payload.");
    }
    try {
      $count = FileUtils::fileCountLines($payload[File::FILE_ID]);
    }
    catch (HTException $e) {
      return new BackgroundJobResult(-1, $e->getMessage());
    }
    return new BackgroundJobResult(0, "Recounted $count lines.");
  }
}
