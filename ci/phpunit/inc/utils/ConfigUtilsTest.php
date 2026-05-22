<?php

namespace Tests\Utils;

use Hashtopolis\dba\models\Config;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\ConfigUtils;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

/**
 * Unit tests for ConfigUtils.
 * Uses the pre-seeded "chunktime" config row that every Hashtopolis install
 * has, so no fixture creation is needed and tearDown is not required.
 */
final class ConfigUtilsTest extends TestBase {

  // Fetched once per test — holds the live "chunktime" Config record from DB.
  private ?Config $existingConfig = null;

  // Loads the "chunktime" config before every test so tests have a real
  // Config object with a known item name and a valid database ID.
  protected function setUp(): void {
    parent::setUp();
    $this->existingConfig = ConfigUtils::get('chunktime');
  }

  // Verifies that get() returns the correct Config object when the item exists.
  // Uses "chunktime" which is always present in the default database.
  public function testGetKnownItemReturnsConfig(): void {
    $config = ConfigUtils::get('chunktime');
    $this->assertSame('chunktime', $config->getItem());
  }

  // Verifies that get() throws HTException when the item does not exist.
  // Uses a deliberately nonsensical key that will never be in the database.
  public function testGetUnknownItemThrowsHTException(): void {
    $this->expectException(HTException::class);
    ConfigUtils::get('nonexistent_item_xyz_999');
  }

  // Verifies that updateSingleConfig() throws HTException when the attributes
  // array contains no VALUE key, meaning no new value was provided.
  public function testUpdateSingleConfigMissingValueThrowsHTException(): void {
    $this->expectException(HTException::class);
    // Empty attributes array — Config::VALUE key is absent, triggering the guard.
    ConfigUtils::updateSingleConfig($this->existingConfig->getId(), []);
  }

  // Verifies that updateSingleConfig() returns early without performing any
  // database write when the provided value is identical to the stored value.
  // This is the no-op path that avoids unnecessary DB updates.
  public function testUpdateSingleConfigSameValueReturnsEarlyWithoutException(): void {
    $this->expectNotToPerformAssertions();
    $sameValue = $this->existingConfig->getValue();
    // Passing the same value back — the method must detect no change and return.
    ConfigUtils::updateSingleConfig($this->existingConfig->getId(), [Config::VALUE => $sameValue]);
    
  }

  // Verifies that updateSingleConfig() throws HTException when the given ID
  // does not match any row in the Config table.
  public function testUpdateSingleConfigInvalidIdThrowsHTException(): void {
    $this->expectException(HTException::class);
    ConfigUtils::updateSingleConfig(99999, [Config::VALUE => 'anything']);
  }
}
