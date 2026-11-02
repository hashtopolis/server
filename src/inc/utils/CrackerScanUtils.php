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
 * Currently only hashcat binaries are supported. The report is read with
 * '--example-hashes': hashcat 6.2.6 and newer report the details of all
 * their hash-modes as JSON on stdout when started with
 * '--example-hashes --machine-readable', older versions from 6.0.0 on
 * accept the flag but print plain text, which is parsed instead. Versions
 * before 6.0.0 cannot be parsed at all, they are rejected by the scan.
 */
class CrackerScanUtils {
  /** Timeout of the 7z archive extraction in seconds. */
  private const UNPACK_TIMEOUT = 120;

  /** Timeout of the '--example-hashes' invocation in seconds. */
  private const EXAMPLE_HASHES_TIMEOUT = 120;

  /** Timeout of the '--version' invocation in seconds. */
  private const VERSION_TIMEOUT = 30;

  /**
   * Minimum hashcat version the scan supports: versions before 6.0.0 are
   * rejected, the scan does not try to read their hash-modes.
   */
  public const MINIMUM_VERSION = [6, 0, 0];

  /**
   * First hashcat version which reports its hash-modes as JSON when given
   * '--example-hashes --machine-readable': 6.2.6. Older versions ignore
   * '--machine-readable' and print plain text.
   */
  public const MACHINE_READABLE_VERSION = [6, 2, 6];

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
   * Runs the given binary with '--example-hashes' and returns its stdout,
   * which reports the details of all supported hash-modes of hashcat
   * binaries. Hashcat 6.2.6 and newer print the report as JSON when
   * '--machine-readable' is passed as well, older versions accept the flag
   * but ignore it and print plain text, so it is only passed for versions
   * which use it. The binary is searched recursively in the unpack
   * directory, hashcat archives contain a version subdirectory. The
   * invocation is limited by EXAMPLE_HASHES_TIMEOUT.
   *
   * @param string $unpackDir directory the archive was unpacked into
   * @param string $binaryName binary name of the cracker, e.g. 'hashcat'
   * @param bool $machineReadable whether to pass '--machine-readable', which makes hashcat 6.2.6+ print JSON
   * @return string stdout of the '--example-hashes' invocation
   * @throws HTException when the binary is not found, not executable, fails to run or times out
   */
  public static function runExampleHashes(string $unpackDir, string $binaryName, bool $machineReadable = true): string {
    $binaryPath = self::findBinary($unpackDir, $binaryName);
    if ($binaryPath === null) {
      throw new HTException("Could not find '$binaryName.bin' in the archive of the cracker binary!");
    }
    if (!chmod($binaryPath, 0755) && !is_executable($binaryPath)) {
      throw new HTException("Could not make '$binaryName.bin' executable!");
    }
    $args = ['--example-hashes'];
    if ($machineReadable) {
      $args[] = '--machine-readable';
    }
    return self::runBinary($binaryPath, $args, self::EXAMPLE_HASHES_TIMEOUT);
  }

  /**
   * Runs the given binary with '--version' and returns its stdout, which is
   * the version tag of the binary, e.g. 'v6.2.6' for releases or
   * 'v7.1.2-672-g7015700' for self-built binaries. The version decides how
   * the hash-modes of the binary are read, so a binary which fails to
   * report its version is not fatal here: an empty string is returned
   * instead and the caller treats it as an unknown version.
   *
   * @param string $unpackDir directory the archive was unpacked into
   * @param string $binaryName binary name of the cracker, e.g. 'hashcat'
   * @return string stdout of the '--version' invocation, empty when it failed
   */
  public static function runVersion(string $unpackDir, string $binaryName): string {
    $binaryPath = self::findBinary($unpackDir, $binaryName);
    if ($binaryPath === null) {
      return '';
    }
    if (!chmod($binaryPath, 0755) && !is_executable($binaryPath)) {
      return '';
    }
    try {
      return self::runBinary($binaryPath, ['--version'], self::VERSION_TIMEOUT);
    }
    catch (HTException) {
      return '';
    }
  }

  /**
   * Runs the given binary with the given arguments and returns its stdout.
   * The executed binary is user-supplied, so only PATH is passed to it, the
   * server environment — including the database credentials — must not leak
   * to it. stdout is drained while the binary runs: a full unread pipe
   * would block it, and reading one pipe to its end before the other would
   * deadlock the scan. stderr is redirected to a file and only feeds the
   * error message.
   *
   * @param string $binaryPath path of the binary to run
   * @param array<string> $args arguments passed to the binary
   * @param int $timeout timeout of the invocation in seconds
   * @return string stdout of the invocation
   * @throws HTException when the binary fails to run or times out
   */
  private static function runBinary(string $binaryPath, array $args, int $timeout): string {
    $label = basename($binaryPath) . ' ' . implode(' ', $args);
    $errorFile = tempnam(sys_get_temp_dir(), 'HTP_SCAN_ERR_');
    if ($errorFile === false) {
      throw new HTException("Could not create a temporary file for the error output of '$label'!");
    }
    $proc = proc_open(
      array_merge([$binaryPath], $args),
      [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['file', $errorFile, 'w']],
      $pipes,
      null,
      self::childEnvironment()
    );
    if (!is_resource($proc)) {
      unlink($errorFile);
      throw new HTException("Could not start '$label'!");
    }
    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    // enforce the timeout by polling the still running process, draining
    // its report while waiting
    $stdout = '';
    $deadline = time() + $timeout;
    $status = proc_get_status($proc);
    while ($status['running']) {
      if (time() > $deadline) {
        proc_terminate($proc);
        proc_close($proc);
        unlink($errorFile);
        throw new HTException("Timeout while running '$label'!");
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
      throw new HTException("'$label' failed (exit code $exitCode): " . trim($stderr));
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
   * Parses the hash-mode report out of the plain text output of
   * 'hashcat.bin --example-hashes' of hashcat versions before 6.2.6, which
   * do not support the JSON format of '--machine-readable' yet.
   *
   * Hashcat 6.2.0 to 6.2.5 print a block per hash-mode stating the mode
   * number after 'Hash mode #', the description after 'Name', whether the
   * mode is a slow hash after 'Slow.Hash' and how the salt is provided
   * after 'Salt.Type'. Only modes with a generic salt have the salt as a
   * separate input next to the hash ('hash:salt' format), so only those
   * count as salted; embedded and virtual salts are part of the hash
   * itself. The 'Salt.Type' line is missing completely for unsalted modes.
   *
   * Hashcat 6.0.0 to 6.1.1 only print the mode number after 'MODE:' and
   * the description after 'TYPE:', they do not state the salt and slow-hash
   * properties: modes which do not exist yet are created as unsalted fast
   * hashes for them.
   *
   * @param string $exampleHashesOutput stdout of 'hashcat.bin --example-hashes'
   * @return array<int, array{mode: int, description: string, isSalted: bool, isSlowHash: bool}> mode id to entry
   */
  public static function parseHashcatModesPlain(string $exampleHashesOutput): array {
    $modes = [];
    // entry of the 'Hash mode #' block which is currently being parsed
    // (hashcat 6.2.0+), committed when the next block starts
    $entry = null;
    // mode number of a 'MODE:' line of hashcat 6.0.x/6.1.x waiting for its 'TYPE:' line
    $pendingModeId = null;
    foreach (preg_split('/\r?\n/', $exampleHashesOutput) ?: [] as $line) {
      $line = trim($line);
      if (preg_match('/^Hash mode #(\d+)$/', $line, $matches) === 1) {
        self::commitPlainMode($modes, $entry);
        $entry = ['mode' => (int)$matches[1], 'description' => '', 'isSalted' => false, 'isSlowHash' => false];
      }
      elseif (preg_match('/^MODE: (\d+)$/', $line, $matches) === 1) {
        self::commitPlainMode($modes, $entry);
        $entry = null;
        $pendingModeId = (int)$matches[1];
      }
      elseif ($pendingModeId !== null && preg_match('/^TYPE: (.+)$/', $line, $matches) === 1) {
        $modes[$pendingModeId] = [
          'mode' => $pendingModeId,
          'description' => $matches[1],
          'isSalted' => false,
          'isSlowHash' => false,
        ];
        $pendingModeId = null;
      }
      elseif ($entry !== null && preg_match('/^Name\.+: (.+)$/', $line, $matches) === 1) {
        $entry['description'] = $matches[1];
      }
      elseif ($entry !== null && preg_match('/^Slow\.Hash\.+: (Yes|No)$/', $line, $matches) === 1) {
        $entry['isSlowHash'] = $matches[1] === 'Yes';
      }
      elseif ($entry !== null && preg_match('/^Salt\.Type\.+: (Generic|Embedded|Virtual)$/', $line, $matches) === 1) {
        $entry['isSalted'] = $matches[1] === 'Generic';
      }
    }
    self::commitPlainMode($modes, $entry);
    return $modes;
  }

  /**
   * Adds the entry of the finished 'Hash mode #' block to the modes, blocks
   * without a description are skipped.
   *
   * @param array<int, array{mode: int, description: string, isSalted: bool, isSlowHash: bool}> $modes
   * @param array{mode: int, description: string, isSalted: bool, isSlowHash: bool}|null $entry
   */
  private static function commitPlainMode(array &$modes, ?array $entry): void {
    if ($entry === null || strlen($entry['description']) == 0) {
      return;
    }
    $modes[$entry['mode']] = $entry;
  }

  /**
   * Parses the version tag hashcat prints for '--version', e.g. 'v6.2.6'
   * for release binaries or 'v7.1.2-672-g7015700' for self-built ones,
   * into its major, minor and patch numbers.
   *
   * @param string $versionOutput stdout of 'hashcat.bin --version'
   * @return array{int,int,int}|null major, minor and patch number, null when the output contains no version
   */
  public static function parseHashcatVersion(string $versionOutput): ?array {
    foreach (preg_split('/\r?\n/', $versionOutput) ?: [] as $line) {
      if (preg_match('/v?(\d+)\.(\d+)\.(\d+)/', trim($line), $matches) === 1) {
        return [(int)$matches[1], (int)$matches[2], (int)$matches[3]];
      }
    }
    return null;
  }

  /**
   * Compares the two parsed version numbers, [major, minor, patch].
   *
   * @param array{int,int,int} $version
   * @param array{int,int,int} $other
   */
  public static function versionLessThan(array $version, array $other): bool {
    for ($i = 0; $i < 3; $i++) {
      if ($version[$i] != $other[$i]) {
        return $version[$i] < $other[$i];
      }
    }
    return false;
  }

  /**
   * Formats the parsed version numbers in the dotted notation hashcat
   * uses, e.g. '6.2.5'.
   *
   * @param array{int,int,int} $version
   */
  public static function formatVersion(array $version): string {
    return $version[0] . '.' . $version[1] . '.' . $version[2];
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
