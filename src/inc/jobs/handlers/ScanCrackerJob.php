<?php

namespace Hashtopolis\inc\jobs\handlers;

use Exception;
use Hashtopolis\dba\models\BackgroundJob;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\inc\defines\DBackgroundJobType;
use Hashtopolis\inc\jobs\BackgroundJobHandler;
use Hashtopolis\inc\jobs\BackgroundJobResult;
use Hashtopolis\inc\utils\CrackerScanUtils;
use Hashtopolis\inc\utils\CrackerUtils;
use Hashtopolis\inc\utils\HashtypeUtils;
use Hashtopolis\inc\HTException;
use Hashtopolis\dba\Factory;

class ScanCrackerJob implements BackgroundJobHandler {
  public static function getJobType(): string {
    return DBackgroundJobType::SCAN_CRACKER;
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
   * @throws Exception
   */
  public function execute(BackgroundJob $job, array $payload): BackgroundJobResult {
    if (!isset($payload[CrackerBinary::CRACKER_BINARY_ID]) || !is_int($payload[CrackerBinary::CRACKER_BINARY_ID])) {
      return new BackgroundJobResult(-1, "Missing or invalid '" . CrackerBinary::CRACKER_BINARY_ID . "' in payload.");
    }
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

    $tempDir = (string)tempnam(sys_get_temp_dir(), 'HTP_SCAN_');
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
      foreach (glob($tempDir . '/*') ?: [] as $path) {
        is_dir($path) ? self::removeDirectory($path) : unlink($path);
      }
      @rmdir($tempDir);
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

  private static function removeDirectory(string $dir): void {
    foreach (glob($dir . '/*') ?: [] as $path) {
      is_dir($path) ? self::removeDirectory($path) : unlink($path);
    }
    @rmdir($dir);
  }
}
