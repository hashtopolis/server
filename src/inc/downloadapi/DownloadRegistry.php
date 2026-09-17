<?php

namespace Hashtopolis\inc\downloadapi;

/**
 * Maps the download kind given in the url to the handler serving it. This is
 * the place to register handlers for additional downloadable resources, so
 * the download endpoint can take over other downloads like files or hashlists
 * in the future.
 */
final class DownloadRegistry {
  private const HANDLERS = [
    'crackerBinary' => CrackerBinaryDownloadHandler::class,
  ];

  public static function getHandler(string $kind): ?string {
    return self::HANDLERS[$kind] ?? null;
  }
}
