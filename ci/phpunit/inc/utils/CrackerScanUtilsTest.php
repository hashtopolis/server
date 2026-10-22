<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

/**
 * Tests for the '--example-hashes --machine-readable' output parser of
 * CrackerScanUtils. The tests run against a recorded output of a real
 * hashcat binary, no hashcat binary is needed to run them.
 */
final class CrackerScanUtilsTest extends TestBase {
  private string $exampleHashesOutput;

  #[Override]
  protected function setUp(): void {
    parent::setUp();
    $this->exampleHashesOutput = file_get_contents(__DIR__ . '/../../fixtures/crackerscan/hashcat_example_hashes.json');
    $this->assertIsString($this->exampleHashesOutput);
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
}
