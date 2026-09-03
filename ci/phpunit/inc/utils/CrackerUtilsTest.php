<?php

namespace Tests\Utils;

use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\models\AccessGroupUser;
use Hashtopolis\inc\Util;
use Hashtopolis\inc\defines\DDirectories;
use Hashtopolis\inc\apiv2\error\HttpConflict;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\AccessGroupUtils;
use Hashtopolis\inc\utils\AccessUtils;
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

  private ?AbstractModel $type = null;
  private ?AbstractModel $binary = null;

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
      new CrackerBinary(null, $this->type->getId(), '1.0.0', 'http://example.com', 'testcracker', null, 1)
    );
  }

  // Verifies that getBinary() throws HTException when the ID does not match
  // any row — the caller must handle the "binary not found" case.
  public function testGetBinaryInvalidIdThrowsHTException(): void {
    $this->expectException(HTException::class);
    CrackerUtils::getBinary(99999);
  }

  // Verifies that getBinaryType() throws HTException when the ID does not match
  // any row — the caller must handle the "type not found" case.
  public function testGetBinaryTypeInvalidIdThrowsHTException(): void {
    $this->expectException(HTException::class);
    CrackerUtils::getBinaryType(99999);
  }

  // Verifies that getBinary() returns the correct CrackerBinary when the ID
  // matches the record created in setUp.
  public function testGetBinaryValidIdReturnsBinary(): void {
    $result = CrackerUtils::getBinary($this->binary->getId());
    $this->assertSame($this->binary->getId(), $result->getId());
  }

  // Verifies that getBinaryType() returns the correct CrackerBinaryType when
  // the ID matches the record created in setUp.
  public function testGetBinaryTypeValidIdReturnsBinaryType(): void {
    $result = CrackerUtils::getBinaryType($this->type->getId());
    $this->assertSame($this->type->getId(), $result->getId());
  }

  // Verifies that createBinaryType() throws HttpError when an empty string is
  // passed as the type name — an empty name is not a valid cracker identifier.
  public function testCreateBinaryTypeEmptyNameThrowsHttpError(): void {
    $this->expectException(HttpError::class);
    CrackerUtils::createBinaryType('');
  }

  // Verifies that createBinaryType() throws HttpConflict when a type with the
  // same name already exists in the database (setUp created "test-crackerutils-type").
  public function testCreateBinaryTypeDuplicateNameThrowsHttpConflict(): void {
    $this->expectException(HttpConflict::class);
    CrackerUtils::createBinaryType('test-crackerutils-type');
  }

  // Verifies that createBinary() throws HttpError when any required field is
  // empty. Uses a valid type ID so the method reaches the field validation.
  public function testCreateBinaryEmptyVersionThrowsHttpError(): void {
    $this->expectException(HttpError::class);
    CrackerUtils::createBinary('', 'testcracker', 'http://example.com', $this->type->getId());
  }

  // Verifies the full happy path: createBinary() creates and returns a new
  // CrackerBinary when all fields are valid.
  public function testCreateBinaryValidInputCreatesBinary(): void {
    $b = CrackerUtils::createBinary('9.9.9', 'newcracker', 'http://example.com/dl', $this->type->getId());
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $b);
    $this->assertSame('9.9.9', $b->getVersion());
  }

  private const SEVEN_ZIP_MAGIC = "\x37\x7A\xBC\xAF\x27\x1C";

  private function getImportPath(): string {
    return Factory::getStoredValueFactory()->get(DDirectories::IMPORT)->getVal() . '/';
  }

  private function countBinariesOfType(int $typeId): int {
    $qF = new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $typeId, '=');
    return sizeof(Factory::getCrackerBinaryFactory()->filter([Factory::FILTER => $qF]));
  }

  // Verifies the full happy path: createBinaryFromUpload() with sourceType 'import'
  // moves the archive from the import directory to the crackers directory, composes
  // the server-side filename and sets the download url to the download endpoint.
  public function testCreateBinaryFromUploadImportSource(): void {
    $name = 'test-archive-' . uniqid() . '.7z';
    $content = self::SEVEN_ZIP_MAGIC . 'test-content';
    file_put_contents($this->getImportPath() . $name, $content);
    $b = CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $this->type->getId(), 'import', $name);
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $b);

    $this->assertEquals('test-crackerutils-type-7.2.7.7z', $b->getFilename());
    $this->assertEquals(
      Util::buildBackendBaseUrl() . '/api/download.php/crackerBinary/' . $b->getId(),
      $b->getDownloadUrl()
    );
    $archive = CrackerUtils::getCrackersPath() . $b->getId() . '_' . $b->getFilename();
    $this->assertFileExists($archive);
    $this->assertEquals($content, file_get_contents($archive));
    $this->assertFileDoesNotExist($this->getImportPath() . $name);

    unlink($archive);
  }

  // Verifies the full happy path: createBinaryFromUpload() with sourceType 'inline'
  // stores the base64 decoded archive in the crackers directory.
  public function testCreateBinaryFromUploadInlineSource(): void {
    $content = self::SEVEN_ZIP_MAGIC . 'inline-content';
    $b = CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $this->type->getId(), 'inline', base64_encode($content));
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $b);

    $this->assertEquals('test-crackerutils-type-7.2.7.7z', $b->getFilename());
    $this->assertStringEndsWith('/api/download.php/crackerBinary/' . $b->getId(), $b->getDownloadUrl());
    $archive = CrackerUtils::getCrackersPath() . $b->getId() . '_' . $b->getFilename();
    $this->assertFileExists($archive);
    $this->assertEquals($content, file_get_contents($archive));

    unlink($archive);
  }

  // Verifies that the composed archive filename sanitizes all characters which are
  // problematic in file names.
  public function testCreateBinaryFromUploadSanitizesFilename(): void {
    $type = $this->createDatabaseObject(
      Factory::getCrackerBinaryTypeFactory(),
      new CrackerBinaryType(null, 'weird cracker name!', 1)
    );
    $b = CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $type->getId(), 'inline', base64_encode(self::SEVEN_ZIP_MAGIC));
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $b);

    $this->assertEquals('weird-cracker-name--7.2.7.7z', $b->getFilename());
    unlink(CrackerUtils::getCrackersPath() . $b->getId() . '_' . $b->getFilename());
  }

  // Verifies that createBinaryFromUpload() rejects an unsupported sourceType.
  public function testCreateBinaryFromUploadInvalidSourceTypeThrowsHttpError(): void {
    $this->expectException(HttpError::class);
    CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $this->type->getId(), 'bogus', 'data');
  }

  // Verifies that createBinaryFromUpload() rejects an empty version.
  public function testCreateBinaryFromUploadEmptyVersionThrowsHttpError(): void {
    $this->expectException(HttpError::class);
    CrackerUtils::createBinaryFromUpload('', 'testcracker', $this->type->getId(), 'inline', base64_encode(self::SEVEN_ZIP_MAGIC));
  }

  // Verifies that createBinaryFromUpload() rejects missing sourceData.
  public function testCreateBinaryFromUploadEmptySourceDataThrowsHttpError(): void {
    $this->expectException(HttpError::class);
    CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $this->type->getId(), 'inline', '');
  }

  // Verifies that createBinaryFromUpload() rejects sourceData which is not valid base64.
  public function testCreateBinaryFromUploadInvalidBase64ThrowsHttpError(): void {
    $this->expectException(HttpError::class);
    CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $this->type->getId(), 'inline', '!!!no-base64!!!');
  }

  // Verifies that createBinaryFromUpload() only allows http and https urls, so no
  // local files or stream wrappers can be fetched by the server.
  public function testCreateBinaryFromUploadUrlSchemeThrowsHttpError(): void {
    $this->expectException(HttpError::class);
    CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $this->type->getId(), 'url', 'file:///etc/passwd');
  }

  // Verifies that a non-7z archive is rejected and the import file is restored and
  // no leftover binary or archive remains.
  public function testCreateBinaryFromUploadImportNot7zRollsBack(): void {
    $name = 'test-archive-' . uniqid() . '.txt';
    file_put_contents($this->getImportPath() . $name, 'not-a-7z-archive');
    $countBefore = $this->countBinariesOfType($this->type->getId());

    try {
      CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $this->type->getId(), 'import', $name);
      $this->fail('Expected HttpError for a non-7z archive');
    }
    catch (HttpError $e) {
      // expected
    }

    $this->assertEquals($countBefore, $this->countBinariesOfType($this->type->getId()));
    $this->assertFileExists($this->getImportPath() . $name);
    $this->assertEmpty(glob(CrackerUtils::getCrackersPath() . '*_test-crackerutils-type-7.2.7.7z'));

    unlink($this->getImportPath() . $name);
  }

  // Verifies that a missing import file results in an error and no leftover binary.
  public function testCreateBinaryFromUploadImportFileMissingRollsBack(): void {
    $countBefore = $this->countBinariesOfType($this->type->getId());

    try {
      CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $this->type->getId(), 'import', 'does-not-exist-' . uniqid() . '.7z');
      $this->fail('Expected HttpError for a missing import file');
    }
    catch (HttpError $e) {
      // expected
    }

    $this->assertEquals($countBefore, $this->countBinariesOfType($this->type->getId()));
  }

  // Verifies that deleteBinary() removes the locally stored archive of the binary.
  public function testDeleteBinaryRemovesLocalArchive(): void {
    $name = 'test-archive-' . uniqid() . '.7z';
    file_put_contents($this->getImportPath() . $name, self::SEVEN_ZIP_MAGIC . 'to-be-deleted');
    $b = CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $this->type->getId(), 'import', $name);
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $b);
    $archive = CrackerUtils::getCrackersPath() . $b->getId() . '_' . $b->getFilename();
    $this->assertFileExists($archive);

    CrackerUtils::deleteBinary($b->getId());

    $this->assertFileDoesNotExist($archive);
    $this->assertNull(Factory::getCrackerBinaryFactory()->get($b->getId()));
  }

  // Verifies that deleteBinaryType() removes the archives of all locally stored
  // binaries of the type.
  public function testDeleteBinaryTypeRemovesLocalArchives(): void {
    $type = $this->createDatabaseObject(
      Factory::getCrackerBinaryTypeFactory(),
      new CrackerBinaryType(null, 'type2-' . uniqid(), 1)
    );
    $name = 'test-archive-' . uniqid() . '.7z';
    file_put_contents($this->getImportPath() . $name, self::SEVEN_ZIP_MAGIC . 'to-be-deleted');
    $b = CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $type->getId(), 'import', $name);
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $b);
    $archive = CrackerUtils::getCrackersPath() . $b->getId() . '_' . $b->getFilename();
    $this->assertFileExists($archive);

    CrackerUtils::deleteBinaryType($type->getId());

    $this->assertFileDoesNotExist($archive);
    $this->assertEquals(0, $this->countBinariesOfType($type->getId()));
  }

  // Verifies that the download url of a locally stored binary cannot be changed.
  public function testUpdateBinaryRejectsUrlChangeForLocalBinary(): void {
    $name = 'test-archive-' . uniqid() . '.7z';
    file_put_contents($this->getImportPath() . $name, self::SEVEN_ZIP_MAGIC . 'local');
    $b = CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $this->type->getId(), 'import', $name);
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $b);

    try {
      CrackerUtils::updateBinary('8.0.0', 'testcracker', 'http://other.example.com/hc.7z', $b->getId());
      $this->fail('Expected HTException when changing the url of a local binary');
    }
    catch (HTException $e) {
      $this->assertStringContainsString('locally stored', $e->getMessage());
    }

    $reloaded = Factory::getCrackerBinaryFactory()->get($b->getId());
    $this->assertEquals($b->getDownloadUrl(), $reloaded->getDownloadUrl());
    CrackerUtils::deleteBinary($b->getId());
  }

  // Verifies that a locally stored binary can still be updated as long as the
  // download url is not changed.
  public function testUpdateBinaryAllowsUnchangedUrlForLocalBinary(): void {
    $name = 'test-archive-' . uniqid() . '.7z';
    file_put_contents($this->getImportPath() . $name, self::SEVEN_ZIP_MAGIC . 'local');
    $b = CrackerUtils::createBinaryFromUpload('7.2.7', 'testcracker', $this->type->getId(), 'import', $name);
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $b);

    CrackerUtils::updateBinary('8.0.0', 'testcracker', $b->getDownloadUrl(), $b->getId());

    $reloaded = Factory::getCrackerBinaryFactory()->get($b->getId());
    $this->assertEquals('8.0.0', $reloaded->getVersion());
    $this->assertEquals($b->getDownloadUrl(), $reloaded->getDownloadUrl());
    CrackerUtils::deleteBinary($b->getId());
  }

  // Verifies that the download url of a regular binary can still be changed.
  public function testUpdateBinaryChangesUrlForExternalBinary(): void {
    CrackerUtils::updateBinary('2.0.0', 'testcracker', 'http://changed.example.com/hc.7z', $this->binary->getId());
    $reloaded = Factory::getCrackerBinaryFactory()->get($this->binary->getId());
    $this->assertEquals('http://changed.example.com/hc.7z', $reloaded->getDownloadUrl());
    $this->assertEquals('2.0.0', $reloaded->getVersion());
  }

  // Verifies that binaries can only be created in access groups the user is a
  // member of.
  public function testCreateBinaryRequiresGroupMembership(): void {
    $group = $this->createAccessGroup('ag-crackerutils-member');
    $user = $this->createUser('crackerutils-member-user');

    try {
      CrackerUtils::createBinary('1.0.0', 'testcracker', 'http://example.com/hc.7z', $this->type->getId(), $group->getId(), $user);
      $this->fail('Expected HttpError when the user is not a member of the access group');
    }
    catch (HttpError $e) {
      $this->assertStringContainsString('no rights', $e->getMessage());
    }

    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $group->getId(), $user->getId())
    );
    $binary = CrackerUtils::createBinary('1.0.0', 'testcracker', 'http://example.com/hc.7z', $this->type->getId(), $group->getId(), $user);
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $binary);
    $this->assertEquals($group->getId(), $binary->getAccessGroupId());
  }

  // Verifies that creation without an access group falls back to the first
  // access group of the creating user (legacy UI and user api have no group input).
  public function testCreateBinaryFallsBackToUsersFirstGroup(): void {
    $group = $this->createAccessGroup('ag-crackerutils-fallback');
    $user = $this->createUser('crackerutils-fallback-user');
    // createUser already made the user a member of the default group
    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $group->getId(), $user->getId())
    );
    AccessGroupUtils::removeUser($user->getId(), AccessUtils::getOrCreateDefaultAccessGroup()->getId());

    $binary = CrackerUtils::createBinary('1.0.0', 'testcracker', 'http://example.com/hc.7z', $this->type->getId(), null, $user);
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $binary);
    $this->assertEquals($group->getId(), $binary->getAccessGroupId());
  }

  // Verifies that uploads can only be created in access groups the user is a
  // member of.
  public function testCreateBinaryFromUploadRequiresGroupMembership(): void {
    $group = $this->createAccessGroup('ag-crackerutils-upload');
    $user = $this->createUser('crackerutils-upload-user');

    try {
      CrackerUtils::createBinaryFromUpload('1.0.0', 'testcracker', $this->type->getId(), 'inline',
        base64_encode(self::SEVEN_ZIP_MAGIC . 'content'), $group->getId(), $user);
      $this->fail('Expected HttpError when the user is not a member of the access group');
    }
    catch (HttpError $e) {
      $this->assertStringContainsString('no rights', $e->getMessage());
    }
    $this->assertEmpty(glob(CrackerUtils::getCrackersPath() . '*_test-crackerutils-type-1.0.0.7z'));

    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $group->getId(), $user->getId())
    );
    $binary = CrackerUtils::createBinaryFromUpload('1.0.0', 'testcracker', $this->type->getId(), 'inline',
      base64_encode(self::SEVEN_ZIP_MAGIC . 'content'), $group->getId(), $user);
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $binary);
    $this->assertEquals($group->getId(), $binary->getAccessGroupId());
    unlink(CrackerUtils::getCrackersPath() . $binary->getId() . '_' . $binary->getFilename());
  }

  // Verifies that creation with a group that does not exist is rejected.
  public function testCreateBinaryRejectsInvalidGroup(): void {
    try {
      CrackerUtils::createBinary('1.0.0', 'testcracker', 'http://example.com/hc.7z', $this->type->getId(), 99999999, $this->adminUser);
      $this->fail('Expected HttpError for a non existing access group');
    }
    catch (HttpError $e) {
      $this->assertStringContainsString('Invalid access group', $e->getMessage());
    }
  }

  // Verifies that moving a binary to another group requires membership of the
  // current and of the new group.
  public function testChangeAccessGroupRequiresMembershipOfBothGroups(): void {
    $group1 = $this->createAccessGroup('ag-crackerutils-move-1');
    $group2 = $this->createAccessGroup('ag-crackerutils-move-2');
    $user = $this->createUser('crackerutils-move-user');
    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $group1->getId(), $user->getId())
    );
    $binary = CrackerUtils::createBinary('1.0.0', 'testcracker', 'http://example.com/hc.7z', $this->type->getId(), $group1->getId());
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $binary);

    // the user is not a member of the new group
    try {
      CrackerUtils::changeAccessGroup($binary->getId(), $group2->getId(), $user);
      $this->fail('Expected HttpError when the user is not a member of the new group');
    }
    catch (HttpError $e) {
      $this->assertStringContainsString('No access to this group', $e->getMessage());
    }

    $otherUser = $this->createUser('crackerutils-move-user-2');
    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $group2->getId(), $otherUser->getId())
    );
    // the other user is not a member of the current group of the binary
    try {
      CrackerUtils::changeAccessGroup($binary->getId(), $group2->getId(), $otherUser);
      $this->fail('Expected HttpError when the user is not a member of the current group');
    }
    catch (HttpError $e) {
      $this->assertStringContainsString('No access to this group', $e->getMessage());
    }

    $this->assertEquals($group1->getId(), Factory::getCrackerBinaryFactory()->get($binary->getId())->getAccessGroupId());
  }

  // Verifies that a binary can be moved to another group the user is a member of.
  public function testChangeAccessGroupMovesBinary(): void {
    $group1 = $this->createAccessGroup('ag-crackerutils-move-ok-1');
    $group2 = $this->createAccessGroup('ag-crackerutils-move-ok-2');
    $user = $this->createUser('crackerutils-move-ok-user');
    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $group1->getId(), $user->getId())
    );
    $this->createDatabaseObject(
      Factory::getAccessGroupUserFactory(),
      new AccessGroupUser(null, $group2->getId(), $user->getId())
    );
    $binary = CrackerUtils::createBinary('1.0.0', 'testcracker', 'http://example.com/hc.7z', $this->type->getId(), $group1->getId());
    $this->registerDatabaseObject(Factory::getCrackerBinaryFactory(), $binary);

    CrackerUtils::changeAccessGroup($binary->getId(), $group2->getId(), $user);

    $this->assertEquals($group2->getId(), Factory::getCrackerBinaryFactory()->get($binary->getId())->getAccessGroupId());
  }
}
