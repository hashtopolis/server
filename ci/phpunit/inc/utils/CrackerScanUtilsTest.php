<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

/**
 * Tests for the '--example-hashes' output parsers of CrackerScanUtils:
 * the JSON report of hashcat 6.2.6 and newer ('--example-hashes
 * --machine-readable') and the plain text report of the older versions.
 * The tests run against a recorded output of a real hashcat binary and a
 * fixture modeled after the plain text format of hashcat 6.2.5, no hashcat
 * binary is needed to run them.
 */
final class CrackerScanUtilsTest extends TestBase {
  private string $exampleHashesOutput;
  private string $plainHashesOutput;

  #[Override]
  protected function setUp(): void {
    parent::setUp();
    $this->exampleHashesOutput = file_get_contents(__DIR__ . '/../../fixtures/crackerscan/hashcat_example_hashes.json');
    $this->assertIsString($this->exampleHashesOutput);
    $this->plainHashesOutput = file_get_contents(__DIR__ . '/../../fixtures/crackerscan/hashcat_example_hashes_plain_625.txt');
    $this->assertIsString($this->plainHashesOutput);
  }

  // The recorded output parses into exactly the recorded modes.
  public function testParseHashcatModesFullOutput(): void {
    $modes = CrackerScanUtils::parseHashcatModes($this->exampleHashesOutput);

    $modeIds = array_keys($modes);
    sort($modeIds);
    $this->assertSame(
      [0, 20, 500, 1000, 2100, 2600, 3200, 5800, 8300, 10900, 11300, 18400, 22000, 34810],
      $modeIds
    );
  }

  // Mode entries carry the mode id, the description and the salted and
  // slow-hash flags: only generic salts count as salted, the salt is then a
  // separate input next to the hash; embedded and virtual salts are part of
  // the hash itself. The slow-hash flag is taken from the report directly.
  public function testParseHashcatModesEntryFlags(): void {
    $modes = CrackerScanUtils::parseHashcatModes($this->exampleHashesOutput);

    // not salted, not slow
    $this->assertSame('MD5', $modes[0]['description']);
    $this->assertFalse($modes[0]['isSalted']);
    $this->assertFalse($modes[0]['isSlowHash']);

    // generic salt, not slow
    $this->assertSame('md5($salt.$pass)', $modes[20]['description']);
    $this->assertTrue($modes[20]['isSalted']);
    $this->assertFalse($modes[20]['isSlowHash']);

    // embedded salt, slow
    $this->assertFalse($modes[500]['isSalted']);
    $this->assertTrue($modes[500]['isSlowHash']);

    // virtual salt counts as embedded, not salted
    $this->assertSame('md5(md5($pass))', $modes[2600]['description']);
    $this->assertFalse($modes[2600]['isSalted']);
    $this->assertFalse($modes[2600]['isSlowHash']);

    // generic salt, slow
    $this->assertTrue($modes[5800]['isSalted']);
    $this->assertTrue($modes[5800]['isSlowHash']);

    // embedded salt, not slow
    $this->assertFalse($modes[8300]['isSalted']);
    $this->assertFalse($modes[8300]['isSlowHash']);
  }

  // Mode 0 is a regular hashcat hash-mode and must be parsed like any other.
  public function testParseHashcatModesModeZeroIsIncluded(): void {
    $modes = CrackerScanUtils::parseHashcatModes($this->exampleHashesOutput);

    $this->assertArrayHasKey(0, $modes);
    $this->assertSame(0, $modes[0]['mode']);
  }

  // The banner line of the binary in front of the JSON document is skipped,
  // and outputs without any JSON document at all parse to an empty list.
  public function testParseHashcatModesBannerAndInvalidOutput(): void {
    $this->assertSame([], CrackerScanUtils::parseHashcatModes('hashcat (v9.9.9) starting in autodetect mode'));
    $this->assertSame([], CrackerScanUtils::parseHashcatModes(''));
    $this->assertSame([], CrackerScanUtils::parseHashcatModes('not json at all { broken'));
    $this->assertSame([], CrackerScanUtils::parseHashcatModes('["a list is not a mode report"]'));
  }

  // A minimal fake report with the essential fields of a mode parses to the
  // expected entries, entries without a name are skipped.
  public function testParseHashcatModesMinimalOutput(): void {
    $output = "hashcat (v9.9.9) starting in autodetect mode\n" .
      "\n" .
      '{"0": { "name": "MD5", "slow_hash": false, "is_salted": false, "salt_type": null },' .
      ' "20": { "name": "md5($salt.$pass)", "slow_hash": false, "is_salted": true, "salt_type": "generic" },' .
      ' "500": { "name": "md5crypt, MD5 (Unix), Cisco-IOS $1$ (MD5)", "slow_hash": true, "is_salted": true, "salt_type": "embedded" },' .
      ' "99999": { "name": "", "slow_hash": false, "is_salted": false, "salt_type": null }}';

    $modes = CrackerScanUtils::parseHashcatModes($output);

    $modeIds = array_keys($modes);
    sort($modeIds);
    $this->assertSame([0, 20, 500], $modeIds);
    $this->assertTrue($modes[20]['isSalted']);
    $this->assertFalse($modes[500]['isSalted']);
    $this->assertTrue($modes[500]['isSlowHash']);
  }

  // The plain text report parses into exactly the recorded modes.
  public function testParseHashcatModesPlainFullOutput(): void {
    $modes = CrackerScanUtils::parseHashcatModesPlain($this->plainHashesOutput);

    $modeIds = array_keys($modes);
    sort($modeIds);
    $this->assertSame(
      [0, 20, 500, 1000, 2100, 2600, 3200, 5800, 8300, 10900, 11300, 18400, 22000, 34810],
      $modeIds
    );
  }

  // Mode entries of the plain text report carry the same flags as the
  // JSON report: only generic salts count as salted, the salt is then a
  // separate input next to the hash; embedded and virtual salts are part
  // of the hash itself, a missing 'Salt.Type' line means an unsalted mode.
  // The slow-hash flag is taken from the 'Slow.Hash' line.
  public function testParseHashcatModesPlainEntryFlags(): void {
    $modes = CrackerScanUtils::parseHashcatModesPlain($this->plainHashesOutput);

    // not salted, not slow
    $this->assertSame('MD5', $modes[0]['description']);
    $this->assertFalse($modes[0]['isSalted']);
    $this->assertFalse($modes[0]['isSlowHash']);

    // generic salt, not slow
    $this->assertSame('md5($salt.$pass)', $modes[20]['description']);
    $this->assertTrue($modes[20]['isSalted']);
    $this->assertFalse($modes[20]['isSlowHash']);

    // embedded salt, slow
    $this->assertFalse($modes[500]['isSalted']);
    $this->assertTrue($modes[500]['isSlowHash']);

    // virtual salt counts as embedded, not salted
    $this->assertSame('md5(md5($pass))', $modes[2600]['description']);
    $this->assertFalse($modes[2600]['isSalted']);
    $this->assertFalse($modes[2600]['isSlowHash']);

    // generic salt, slow
    $this->assertTrue($modes[5800]['isSalted']);
    $this->assertTrue($modes[5800]['isSlowHash']);

    // embedded salt, not slow
    $this->assertFalse($modes[8300]['isSalted']);
    $this->assertFalse($modes[8300]['isSlowHash']);

    // generic salt of a newer mode
    $this->assertSame('BLAKE2b-256($pass.$salt)', $modes[34810]['description']);
    $this->assertTrue($modes[34810]['isSalted']);
  }

  // Hashcat 6.0.0 to 6.1.1 print the mode number after 'MODE:' and the
  // description after 'TYPE:' without any salt and slow-hash details: the
  // entries default to unsalted fast hashes.
  public function testParseHashcatModesPlainOldFormat(): void {
    $output = "hashcat (v6.1.1) starting in autodetect mode" . "\n" .
      "\n" .
      "MODE: 0\n" .
      "TYPE: MD5\n" .
      "HASH: 8743b52063cd84097a65d1633f5c74f5\n" .
      "PASS: hashcat\n" .
      "\n" .
      "MODE: 99901\n" .
      "TYPE: Fake Salted Mode\n" .
      "HASH: 3edde1eb9e6679ccbc1ff3c417e8a475a2d2e279:7724368582277760\n" .
      "PASS: hashcat\n" .
      "\n";

    $modes = CrackerScanUtils::parseHashcatModesPlain($output);

    $modeIds = array_keys($modes);
    sort($modeIds);
    $this->assertSame([0, 99901], $modeIds);
    $this->assertSame('Fake Salted Mode', $modes[99901]['description']);
    // the salt and slow-hash properties are not stated, they default to false
    $this->assertFalse($modes[99901]['isSalted']);
    $this->assertFalse($modes[99901]['isSlowHash']);
  }

  // Outputs without any mode report parse to an empty list, the JSON
  // report is not a plain text report either.
  public function testParseHashcatModesPlainInvalidOutput(): void {
    $this->assertSame([], CrackerScanUtils::parseHashcatModesPlain('hashcat (v9.9.9) starting in autodetect mode'));
    $this->assertSame([], CrackerScanUtils::parseHashcatModesPlain(''));
    $this->assertSame([], CrackerScanUtils::parseHashcatModesPlain($this->exampleHashesOutput));
  }

  // The version tag of '--version' is parsed into its major, minor and
  // patch numbers, with and without the leading 'v' and with the suffix
  // of self-built binaries. Output without a version parses to null.
  public function testParseHashcatVersion(): void {
    $this->assertSame([6, 2, 6], CrackerScanUtils::parseHashcatVersion("v6.2.6\n"));
    $this->assertSame([6, 2, 5], CrackerScanUtils::parseHashcatVersion("v6.2.5\n"));
    $this->assertSame([7, 1, 2], CrackerScanUtils::parseHashcatVersion("v7.1.2-672-g7015700\n"));
    $this->assertSame([6, 1, 1], CrackerScanUtils::parseHashcatVersion("6.1.1\n"));
    $this->assertSame([6, 0, 0], CrackerScanUtils::parseHashcatVersion('v6.0.0'));
    $this->assertSame([6, 0, 0], CrackerScanUtils::parseHashcatVersion("v6.0.0\r\n"));
    $this->assertNull(CrackerScanUtils::parseHashcatVersion(''));
    $this->assertNull(CrackerScanUtils::parseHashcatVersion('not a version'));
    $this->assertNull(CrackerScanUtils::parseHashcatVersion("v6\n"));
  }

  // Version numbers compare by major, minor and patch number.
  public function testVersionLessThanAndFormatVersion(): void {
    $this->assertTrue(CrackerScanUtils::versionLessThan([6, 0, 0], [6, 2, 6]));
    $this->assertTrue(CrackerScanUtils::versionLessThan([6, 2, 5], [6, 2, 6]));
    $this->assertTrue(CrackerScanUtils::versionLessThan([5, 9, 9], [6, 0, 0]));
    $this->assertFalse(CrackerScanUtils::versionLessThan([6, 2, 6], [6, 2, 6]));
    $this->assertFalse(CrackerScanUtils::versionLessThan([7, 0, 0], [6, 2, 6]));
    $this->assertFalse(CrackerScanUtils::versionLessThan([6, 2, 7], [6, 2, 6]));
    $this->assertSame('6.2.5', CrackerScanUtils::formatVersion([6, 2, 5]));
  }
}
