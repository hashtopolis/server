<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\inc\HTException;
use JsonException;

/**
 * Scans the archive of a cracker binary to determine the hashtypes it supports.
 *
 * The archive stored in the crackers directory is unpacked into a temporary
 * directory and the list of supported hash-modes is read from the binary.
 * Currently only hashcat binaries are supported: they report the details of
 * all their hash-modes as JSON on stdout when started with
 * '--example-hashes --machine-readable'.
 */
class CrackerScanUtils {
  /** Timeout of the 7z archive extraction in seconds. */
  private const UNPACK_TIMEOUT = 120;

  /** Timeout of the '--example-hashes' invocation in seconds. */
  private const EXAMPLE_HASHES_TIMEOUT = 120;

  /**
   * Path of the locally stored archive of the given cracker binary.
   *
   * @throws Exception
   */
  public static function getLocalArchivePath(CrackerBinary $binary): string {
    $binaryType = CrackerUtils::getBinaryType($binary->getCrackerBinaryTypeId());
    $filename = CrackerUtils::buildArchiveFilename($binaryType, $binary->getVersion());
    return CrackerUtils::getCrackersPath() . $binary->getId() . '_' . $filename;
  }

  /**
   * Unpacks the given archive into the target directory with 7z. The
   * extraction is limited by UNPACK_TIMEOUT, so a hanging or endlessly
   * busy 7z process is terminated instead of blocking the background job
   * runner.
   *
   * @param string $archive path of the archive to unpack
   * @param string $targetDir existing directory to unpack into
   * @throws HTException when the archive is missing, 7z fails or the extraction times out
   */
  public static function unpackArchive(string $archive, string $targetDir): void {
    if (!self::sevenZipAvailable()) {
      throw new HTException("7z is not available on the server, cannot unpack the cracker binary archive!");
    }
    if (!file_exists($archive)) {
      throw new HTException("The archive of the cracker binary is not stored on the server: '$archive'");
    }
    $cmd = ['7z', 'x', '-y', '-o' . $targetDir, $archive];
    // the listing 7z writes to stdout is not used, it is redirected to
    // /dev/null: an unread stdout pipe would fill up and block 7z forever
    $proc = proc_open($cmd,
      [0 => ['pipe', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['pipe', 'w']],
      $pipes, null, self::childEnvironment());
    if (!is_resource($proc)) {
      throw new HTException("Could not start 7z to unpack the cracker binary archive!");
    }
    fclose($pipes[0]);
    stream_set_blocking($pipes[2], false);
    $stderr = '';
    // enforce the timeout by polling the still running process, draining
    // its error output while waiting
    $deadline = time() + self::UNPACK_TIMEOUT;
    $status = proc_get_status($proc);
    while ($status['running']) {
      if (time() > $deadline) {
        proc_terminate($proc);
        proc_close($proc);
        throw new HTException("Timeout while unpacking the cracker binary archive (exceeded " . self::UNPACK_TIMEOUT . " seconds)!");
      }
      $stderr .= stream_get_contents($pipes[2]) ?: '';
      usleep(100000);
      $status = proc_get_status($proc);
    }
    $stderr .= stream_get_contents($pipes[2]) ?: '';
    fclose($pipes[2]);
    $exitCode = proc_close($proc);
    if ($exitCode !== 0) {
      throw new HTException("7z failed to unpack the cracker binary archive (exit code $exitCode): " . trim($stderr));
    }
  }

  /**
   * Runs the given binary with '--example-hashes --machine-readable' and
   * returns its stdout, which reports the details of all supported
   * hash-modes as JSON for hashcat binaries. The binary is searched
   * recursively in the unpack directory, hashcat archives contain a version
   * subdirectory. The invocation is limited by EXAMPLE_HASHES_TIMEOUT.
   *
   * @param string $unpackDir directory the archive was unpacked into
   * @param string $binaryName binary name of the cracker, e.g. 'hashcat'
   * @return string stdout of the '--example-hashes' invocation
   * @throws HTException when the binary is not found, not executable, fails to run or times out
   */
  public static function runExampleHashes(string $unpackDir, string $binaryName): string {
    $binaryPath = self::findBinary($unpackDir, $binaryName);
    if ($binaryPath === null) {
      throw new HTException("Could not find '$binaryName.bin' in the archive of the cracker binary!");
    }
    if (!chmod($binaryPath, 0755) && !is_executable($binaryPath)) {
      throw new HTException("Could not make '$binaryName.bin' executable!");
    }
    // the report of the hash-modes is read from stdout, stderr only feeds
    // the error message. stderr is redirected to a file and stdout is
    // drained while the binary runs: a full unread pipe would block the
    // binary, and reading one pipe to its end before the other would
    // deadlock the scan.
    $errorFile = tempnam(sys_get_temp_dir(), 'HTP_SCAN_ERR_');
    if ($errorFile === false) {
      throw new HTException("Could not create a temporary file for the error output of '$binaryName.bin'!");
    }
    $proc = proc_open(
      [$binaryPath, '--example-hashes', '--machine-readable'],
      [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['file', $errorFile, 'w']],
      $pipes,
      null,
      self::childEnvironment()
    );
    if (!is_resource($proc)) {
      unlink($errorFile);
      throw new HTException("Could not start '$binaryName.bin' to scan the supported hash-modes!");
    }
    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    // enforce the timeout by polling the still running process, draining
    // its report while waiting
    $stdout = '';
    $deadline = time() + self::EXAMPLE_HASHES_TIMEOUT;
    $status = proc_get_status($proc);
    while ($status['running']) {
      if (time() > $deadline) {
        proc_terminate($proc);
        proc_close($proc);
        unlink($errorFile);
        throw new HTException("Timeout while running '$binaryName.bin --example-hashes'!");
      }
      $stdout .= stream_get_contents($pipes[1]) ?: '';
      usleep(100000);
      $status = proc_get_status($proc);
    }
    $stdout .= stream_get_contents($pipes[1]) ?: '';
    fclose($pipes[1]);
    $exitCode = proc_close($proc);
    $stderr = file_get_contents($errorFile) ?: '';
    unlink($errorFile);
    if ($exitCode !== 0) {
      throw new HTException("'$binaryName.bin --example-hashes' failed (exit code $exitCode): " . trim($stderr));
    }
    return $stdout;
  }

  /**
   * Parses the hash-mode report out of the output of
   * 'hashcat.bin --example-hashes --machine-readable'.
   *
   * The output carries a banner line before the JSON document, the JSON is
   * an object keyed by the hash-mode number with the details of each mode:
   * 'name' is the description of the hashtype, 'slow_hash' states whether
   * the mode is computed with a key derivation, and 'is_salted' together
   * with 'salt_type' state how the salt is provided. Only modes with
   * 'is_salted' true and 'salt_type' 'generic' have the salt as a separate
   * input next to the hash ('hash:salt' format), so only those count as
   * salted; embedded and virtual salts are part of the hash itself.
   *
   * @param string $exampleHashesOutput stdout of 'hashcat.bin --example-hashes --machine-readable'
   * @return array<int, array{mode: int, description: string, isSalted: bool, isSlowHash: bool}> mode id to entry
   */
  public static function parseHashcatModes(string $exampleHashesOutput): array {
    // the JSON document starts at the first opening brace, everything before
    // it is the banner of the binary
    $jsonStart = strpos($exampleHashesOutput, '{');
    if ($jsonStart === false) {
      return [];
    }
    try {
      $document = json_decode(
        substr($exampleHashesOutput, $jsonStart),
        true,
        512,
        JSON_THROW_ON_ERROR
      );
    }
    catch (JsonException) {
      return [];
    }
    if (!is_array($document)) {
      return [];
    }

    $modes = [];
    foreach ($document as $modeId => $entry) {
      if (!is_array($entry)) {
        continue;
      }
      // json_decode decodes numeric object keys as integers, mode ids are
      // the hashcat mode numbers; mode 0 is the very first hashcat
      // hash-mode, it is valid
      $modeId = (string)$modeId;
      if (!ctype_digit($modeId)) {
        continue;
      }
      $description = $entry['name'] ?? null;
      if (!is_string($description) || strlen($description) == 0) {
        continue;
      }
      $modes[(int)$modeId] = [
        'mode' => (int)$modeId,
        'description' => $description,
        'isSalted' => ($entry['is_salted'] ?? false) === true && ($entry['salt_type'] ?? null) === 'generic',
        'isSlowHash' => ($entry['slow_hash'] ?? false) === true,
      ];
    }
    return $modes;
  }

  /**
   * Minimal environment for child processes of the scan: the executed
   * binary is user-supplied, so the server environment — including the
   * database credentials — must not be leaked to it. Only PATH is passed
   * on, so the spawned binaries can be resolved at all.
   */
  private static function childEnvironment(): array {
    return ['PATH' => getenv('PATH') ?: '/usr/local/bin:/usr/bin:/bin'];
  }

  private static function sevenZipAvailable(): bool {
    $proc = proc_open(['7z'], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, null,
      self::childEnvironment());
    if (!is_resource($proc)) {
      return false;
    }
    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);
    return true;
  }

  /**
   * Finds the '<binaryName>.bin' executable in the unpack directory, searching
   * one level of subdirectories as well because hashcat archives contain a
   * version subdirectory.
   */
  private static function findBinary(string $unpackDir, string $binaryName): ?string {
    // the binary name is user input, it must be a plain basename so the
    // candidates cannot escape the unpack directory with ../
    if ($binaryName !== basename($binaryName) || str_contains($binaryName, '..')) {
      return null;
    }
    $candidates = [
      $unpackDir . '/' . $binaryName . '.bin',
    ];
    foreach (glob($unpackDir . '/*/' . $binaryName . '.bin') ?: [] as $path) {
      $candidates[] = $path;
    }
    foreach ($candidates as $path) {
      if (file_exists($path)) {
        return $path;
      }
    }
    return null;
  }
}
