<?php

namespace Hashtopolis\inc\jobs\handlers;

use Exception;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\jobs\BackgroundJobHandler;
use Hashtopolis\inc\jobs\BackgroundJobResult;
use Hashtopolis\inc\jobs\payload\JobPayloadField;
use Hashtopolis\inc\utils\CrackerScanUtils;
use Hashtopolis\inc\utils\CrackerUtils;
use Hashtopolis\inc\utils\HashtypeUtils;
use Hashtopolis\inc\HTException;
use Hashtopolis\dba\Factory;

class ScanCrackerJob implements BackgroundJobHandler {
  public static function getJobType(): string {
    return DBackgroundJobType::SCAN_CRACKER;
  }

  public static function getPayloadDefinition(): array {
    return [
      CrackerBinary::CRACKER_BINARY_ID => new JobPayloadField(JobPayloadField::TYPE_INT),
    ];
  }

  public function getMaxRuntime(): int {
    return 600;
  }

  /**
   * Unpacks the archive of the given cracker binary, reads the supported
   * hash-modes with their details from the binary and updates the hashtype
   * associations of the binary: modes which do not exist yet are created as
   * new hashtypes, the associations of the binary are adjusted to exactly
   * the modes the binary supports. Existing hashtypes are never altered,
   * the scan only decides whether the binary supports them or not.
   *
   * A url-referenced binary whose archive is not on the server (e.g. when the
   * scan was enqueued without a preceding download, as done by the migration
   * on existing setups) gets its local copy downloaded first.
   *
   * @throws Exception
   */
  public function execute(BackgroundJob $job, array $payload): BackgroundJobResult {
    $binaryId = $payload[CrackerBinary::CRACKER_BINARY_ID];

    try {
      $binary = CrackerUtils::getBinary($binaryId);
    }
    catch (HTException $e) {
      return new BackgroundJobResult(-1, $e->getMessage());
    }

    // the binary might have been deleted or changed to a non-hashcat type
    // since the job was enqueued
    if (!CrackerUtils::isHashcatBinary($binary)) {
      return new BackgroundJobResult(0, "Cracker binary $binaryId is not of the hashcat type, scan skipped.");
    }

    // url-referenced binaries have no archive on the server when the scan was
    // enqueued without a preceding download (e.g. by the migration on existing
    // setups): download the local copy first, so the scan can unpack it. If
    // the download fails, the scan fails and can be re-triggered later.
    if ($binary->getFilename() === null && !file_exists(CrackerScanUtils::getLocalArchivePath($binary))) {
      try {
        CrackerUtils::storeLocalCopy($binary);
      }
      catch (HttpError $e) {
        return new BackgroundJobResult(-1, $e->getMessage());
      }
    }

    $tempDir = tempnam(sys_get_temp_dir(), 'HTP_SCAN_');
    if ($tempDir === false) {
      return new BackgroundJobResult(-1, "Could not create a temporary directory for the scan of the cracker binary $binaryId.");
    }
    try {
      unlink($tempDir);
      mkdir($tempDir);
      CrackerScanUtils::unpackArchive(CrackerScanUtils::getLocalArchivePath($binary), $tempDir);
      $output = CrackerScanUtils::runExampleHashes($tempDir, $binary->getBinaryName());
    }
    catch (HTException $e) {
      return new BackgroundJobResult(-1, $e->getMessage());
    }
    finally {
      self::removeDirectory($tempDir);
    }

    $modes = CrackerScanUtils::parseHashcatModes($output);
    if (sizeof($modes) == 0) {
      return new BackgroundJobResult(-1, "Could not find any hash-modes in the output of the cracker binary $binaryId.");
    }

    // create hashtypes for modes which do not exist yet, existing ones are
    // never altered, the scan only decides whether the binary supports them
    $created = 0;
    $supportedHashtypeIds = [];
    foreach ($modes as $mode) {
      $hashtype = Factory::getHashTypeFactory()->get($mode['mode']);
      if ($hashtype === null) {
        $hashtype = HashtypeUtils::addHashtype($mode['mode'], $mode['description'], $mode['isSalted'], $mode['isSlowHash'], null);
        $created++;
      }
      $supportedHashtypeIds[] = $hashtype->getId();
    }

    [$added, $removed] = CrackerUtils::setHashtypesOfBinary($binaryId, $supportedHashtypeIds);

    return new BackgroundJobResult(
      0,
      "Scanned " . sizeof($modes) . " hash-modes, created $created hashtypes, added $added and removed $removed associations."
    );
  }

  /**
   * Recursively removes the given directory and its contents. The contents
   * come from a user-controlled archive, so symlinks must never be followed:
   * is_dir() resolves symlinks, so a link to a directory would be recursed
   * into and delete files outside the scanned directory. Links are removed
   * themselves instead, directories are only descended into when they are
   * real directories.
   */
  private static function removeDirectory(string $dir): void {
    foreach (glob($dir . '/*') ?: [] as $path) {
      if (is_link($path)) {
        // remove the link itself, never recurse through it
        @unlink($path);
      }
      elseif (is_dir($path)) {
        self::removeDirectory($path);
      }
      else {
        @unlink($path);
      }
    }
    @rmdir($dir);
  }
}
