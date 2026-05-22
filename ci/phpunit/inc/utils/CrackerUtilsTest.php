<?php

namespace Tests\Utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\CrackerUtils;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

/**
 * Unit tests for CrackerUtils.
 * setUp creates a CrackerBinaryType and one CrackerBinary to use as fixtures.
 * TestBase::tearDown() cleans them up in reverse registration order (binary before type).
 */
final class CrackerUtilsTest extends TestBase {

  private ?CrackerBinaryType $type   = null;
  private ?CrackerBinary     $binary = null;

  // Creates a CrackerBinaryType and one CrackerBinary before each test.
  // These records provide valid IDs for the "happy path" tests and a known
  // duplicate name for the conflict test.
  protected function setUp(): void {
    parent::setUp();
    $this->type = $this->createDatabaseObject(
      Factory::getCrackerBinaryTypeFactory(),
      new CrackerBinaryType(null, 'test-crackerutils-type', 1)
    );
    $this->binary = $this->createDatabaseObject(
      Factory::getCrackerBinaryFactory(),
      new CrackerBinary(null, $this->type->getId(), '1.0.0', 'http://example.com', 'testcracker')
    );
  }

  // Verifies that getBinary() throws HTException when the ID does not match
  // any row — the caller must handle the "binary not found" case.
  public function testGetBinary_InvalidId_ThrowsHTException(): void {
    $this->expectException(HTException::class);
    CrackerUtils::getBinary(99999);
  }

  // Verifies that getBinaryType() throws HTException when the ID does not match
  // any row — the caller must handle the "type not found" case.
  public function testGetBinaryType_InvalidId_ThrowsHTException(): void {
    $this->expectException(HTException::class);
    CrackerUtils::getBinaryType(99999);
  }

  // Verifies that getBinary() returns the correct CrackerBinary when the ID
  // matches the record created in setUp.
  public function testGetBinary_ValidId_ReturnsBinary(): void {
    $result = CrackerUtils::getBinary($this->binary->getId());
    $this->assertSame($this->binary->getId(), $result->getId());
  }

  // Verifies that getBinaryType() returns the correct CrackerBinaryType when
  // the ID matches the record created in setUp.
  public function testGetBinaryType_ValidId_ReturnsBinaryType(): void {
    $result = CrackerUtils::getBinaryType($this->type->getId());
    $this->assertSame($this->type->getId(), $result->getId());
  }

  // Verifies that createBinaryType() throws HttpError when an empty string is
  // passed as the type name — an empty name is not a valid cracker identifier.
  public function testCreateBinaryType_EmptyName_ThrowsHttpError(): void {
    $this->expectException(HttpError::class);
    CrackerUtils::createBinaryType('');
  }

  // Verifies that createBinaryType() throws HttpConflict when a type with the
  // same name already exists in the database (setUp created "test-crackerutils-type").
  public function testCreateBinaryType_DuplicateName_ThrowsHttpConflict(): void {
    $this->expectException(HttpConflict::class);
    CrackerUtils::createBinaryType('test-crackerutils-type');
  }

  // Verifies that createBinary() throws HttpError when any required field is
  // empty. Uses a valid type ID so the method reaches the field validation.
  public function testCreateBinary_EmptyVersion_ThrowsHttpError(): void {
    $this->expectException(HttpError::class);
    CrackerUtils::createBinary('', 'testcracker', 'http://example.com', $this->type->getId());
  }

  // Verifies the full happy path: createBinary() creates and returns a new
  // CrackerBinary when all fields are valid.
  public function testCreateBinary_ValidInput_CreatesBinary(): void {
    $b = CrackerUtils::createBinary('9.9.9', 'newcracker', 'http://example.com/dl', $this->type->getId());
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $b);
    $this->assertSame('9.9.9', $b->getVersion());
  }
}
