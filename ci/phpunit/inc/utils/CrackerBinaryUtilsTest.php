<?php

namespace Tests\Utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\CrackerBinaryUtils;
use Hashtopolis\TestBase;


require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

/**
 * Unit tests for CrackerBinaryUtils.
 * setUp creates a dedicated CrackerBinaryType so tests are isolated.
 * TestBase::tearDown() cleans up all registered objects in reverse order (binaries before type).
 */
final class CrackerBinaryUtilsTest extends TestBase {

  private ?CrackerBinaryType $type = null;

  protected function setUp(): void {
    parent::setUp();
    $this->type = $this->createDatabaseObject(
      Factory::getCrackerBinaryTypeFactory(),
      new CrackerBinaryType(null, 'test-crackerbinaryutils-type', 1)
    );
  }

  // Helper: saves a CrackerBinary with the given version under the shared type
  // and registers it for automatic cleanup via TestBase.
  private function addBinary(string $version): CrackerBinary {
    return $this->createDatabaseObject(
      Factory::getCrackerBinaryFactory(),
      new CrackerBinary(null, $this->type->getId(), $version, 'http://example.com', 'testcracker')
    );
  }

  // Verifies that getNewestVersion() throws HTException when no CrackerBinary
  // rows exist for the given type — there is nothing to pick the newest from.
  public function testGetNewestVersionNoBinariesThrowsHTException(): void {
    $this->expectException(HTException::class);
    CrackerBinaryUtils::getNewestVersion($this->type->getId());
  }

  // Verifies that getNewestVersion() returns the only available binary when
  // exactly one version is registered under the type.
  public function testGetNewestVersionSingleBinaryReturnsThatBinary(): void {
    $binary = $this->addBinary('1.0.0');
    $result = CrackerBinaryUtils::getNewestVersion($this->type->getId());
    $this->assertSame($binary->getId(), $result->getId());
  }

  // Verifies that getNewestVersion() correctly picks the highest semantic version
  // when multiple binaries are registered. The comparison uses Composer\Semver
  // so "2.5.0" must beat "1.9.9" even though 1.9.9 was added after 2.5.0.
  public function testGetNewestVersionMultipleBinariesReturnsHighestVersion(): void {
    $this->addBinary('1.0.0');
    $newest = $this->addBinary('2.5.0');
    $this->addBinary('1.9.9');
    $result = CrackerBinaryUtils::getNewestVersion($this->type->getId());
    $this->assertSame($newest->getId(), $result->getId());
  }

  // Verifies that getNewestVersion() handles non-sequential insertion order
  // correctly — the oldest version added last must not be chosen as newest.
  public function testGetNewestVersionOutOfOrderInsertStillReturnsHighest(): void {
    $newest = $this->addBinary('3.0.0');
    $this->addBinary('1.0.0');
    $this->addBinary('2.0.0');
    $result = CrackerBinaryUtils::getNewestVersion($this->type->getId());
    $this->assertSame($newest->getId(), $result->getId());
  }
}
