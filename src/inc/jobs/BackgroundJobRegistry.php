<?php

namespace Hashtopolis\inc\jobs;

use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\jobs\handlers\ChecksumFileJob;
use Hashtopolis\inc\jobs\handlers\CleaningJob;
use Hashtopolis\inc\jobs\handlers\RecountFileJob;
use Hashtopolis\inc\jobs\handlers\ScanCrackerJob;

class BackgroundJobRegistry {
  /** @var array<string, class-string<BackgroundJobHandler>> */
  private const HANDLERS = [
    DBackgroundJobType::RECOUNT_FILE => RecountFileJob::class,
    DBackgroundJobType::SCAN_CRACKER => ScanCrackerJob::class,
    DBackgroundJobType::CHECKSUM_FILE => ChecksumFileJob::class,
    DBackgroundJobType::CLEANING => CleaningJob::class,
  ];

  /**
   * @return string[] list of all registered job type identifiers
   */
  public static function getRegisteredTypes(): array {
    return array_keys(self::HANDLERS);
  }

  public static function getHandlerClass(string $jobType): ?string {
    return self::HANDLERS[$jobType] ?? null;
  }

  public static function getHandler(string $jobType): ?BackgroundJobHandler {
    $class = self::getHandlerClass($jobType);
    if ($class === null) {
      return null;
    }
    return new $class();
  }

  /**
   * All handlers which are scheduled periodically by the PeriodicJobScheduler instead of
   * being enqueued on demand.
   *
   * @return array<string, PeriodicBackgroundJobHandler> job type identifier to handler
   */
  public static function getPeriodicHandlers(): array {
    $handlers = [];
    foreach (self::HANDLERS as $jobType => $class) {
      $handler = new $class();
      if ($handler instanceof PeriodicBackgroundJobHandler) {
        $handlers[$jobType] = $handler;
      }
    }
    return $handlers;
  }
}
