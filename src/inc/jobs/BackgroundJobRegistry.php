<?php

namespace Hashtopolis\inc\jobs;

use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\jobs\handlers\RecountFileJob;

class BackgroundJobRegistry {
  /** @var array<string, class-string<BackgroundJobHandler>> */
  private const HANDLERS = [
    DBackgroundJobType::RECOUNT_FILE => RecountFileJob::class,
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
}
