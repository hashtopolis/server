<?php

namespace Tests\Utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\CrackerBinaryUtils;
use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__) . '/../../../src/inc/startup/include.php';

/**
 * Unit tests for CrackerBinaryUtils.
 * setUp creates a dedicated CrackerBinaryType so tests are isolated.
 * tearDown removes all binaries and the type created during the test.
 */
final class CrackerBinaryUtilsTest extends TestCase {

  private ?CrackerBinaryType $type    = null;
  private array               $binaries = [];

  // Creates a fresh CrackerBinaryType before each test so every test starts
  // with an empty set of binaries registered under that type.
  protected function setUp(): void {
    $this->type = Factory::getCrackerBinaryTypeFactory()->save(
      new CrackerBinaryType(null, 'test-crackerbinaryutils-type', 1)
    );
  }

  // Deletes every CrackerBinary created during the test, then the type itself.
  // Order matters: binaries must go first due to the FK constraint.
  protected function tearDown(): void {
    foreach ($this->binaries as $b) {
      Factory::getCrackerBinaryFactory()->delete($b);
    }
    Factory::getCrackerBinaryTypeFactory()->delete($this->type);
    $this->binaries = [];
  }

  // Helper: saves a CrackerBinary with the given version under the shared type
  // and registers it for cleanup so tearDown always removes it.
  private function addBinary(string $version): CrackerBinary {
    $b = Factory::getCrackerBinaryFactory()->save(
      new CrackerBinary(null, $this->type->getId(), $version, 'http://example.com', 'testcracker')
    );
    $this->binaries[] = $b;
    return $b;
  }

  // Verifies that getNewestVersion() throws HTException when no CrackerBinary
  // rows exist for the given type — there is nothing to pick the newest from.
  public function testGetNewestVersion_NoBinaries_ThrowsHTException(): void {
    $this->expectException(HTException::class);
    CrackerBinaryUtils::getNewestVersion($this->type->getId());
  }

  // Verifies that getNewestVersion() returns the only available binary when
  // exactly one version is registered under the type.
  public function testGetNewestVersion_SingleBinary_ReturnsThatBinary(): void {
    $binary = $this->addBinary('1.0.0');
    $result = CrackerBinaryUtils::getNewestVersion($this->type->getId());
    $this->assertSame($binary->getId(), $result->getId());
  }

  // Verifies that getNewestVersion() correctly picks the highest semantic version
  // when multiple binaries are registered. The comparison uses Composer\Semver
  // so "2.5.0" must beat "1.9.9" even though 1.9.9 was added after 2.5.0.
  public function testGetNewestVersion_MultipleBinaries_ReturnsHighestVersion(): void {
    $this->addBinary('1.0.0');
    $newest = $this->addBinary('2.5.0');
    $this->addBinary('1.9.9');
    $result = CrackerBinaryUtils::getNewestVersion($this->type->getId());
    $this->assertSame($newest->getId(), $result->getId());
  }

  // Verifies that getNewestVersion() handles non-sequential insertion order
  // correctly — the oldest version added last must not be chosen as newest.
  public function testGetNewestVersion_OutOfOrderInsert_StillReturnsHighest(): void {
    $newest = $this->addBinary('3.0.0');
    $this->addBinary('1.0.0');
    $this->addBinary('2.0.0');
    $result = CrackerBinaryUtils::getNewestVersion($this->type->getId());
    $this->assertSame($newest->getId(), $result->getId());
  }
}
