<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\HTException;
use Hashtopolis\TestBase;
use Override;

require_once(dirname(__FILE__) . '/../../TestBase.php');
require_once(dirname(__FILE__) . '/../../../../src/inc/startup/include.php');

final class HashtypeUtilsTest extends TestBase {
  private User $user;
  
  #[Override]
  protected function setUp(): void {
    parent::setUp();
    $this->user = $this->createUser('ht_user');
  }
  
  public function testAddHashtypeCreatesNewHashtype(): void {
    $hashtypeId = 999001;
    $description = 'test_hashtype_' . uniqid();
    
    $hashtype = HashtypeUtils::addHashtype($hashtypeId, $description, 0, false, $this->user);
    
    $this->assertSame($hashtypeId, $hashtype->getId());
    $this->assertStringContainsString($description, $hashtype->getDescription());
    
    HashtypeUtils::deleteHashtype($hashtype->getId());
    $this->assertNull(Factory::getHashTypeFactory()->get($hashtype->getId()));
  }
  
  public function testAddHashtypeThrowsForDuplicateId(): void {
    $existing = $this->createHashType();
    
    $this->expectException(HttpError::class);
    HashtypeUtils::addHashtype($existing->getId(), 'new_desc', 0, false, $this->user);
  }
  
  public function testAddHashtypeThrowsForEmptyDescription(): void {
    $this->expectException(HttpError::class);
    HashtypeUtils::addHashtype(999003, '', 0, false, $this->user);
  }
  
  public function testAddHashtypeThrowsForNegativeId(): void {
    $this->expectException(HttpError::class);
    HashtypeUtils::addHashtype(-1, 'desc', 0, false, $this->user);
  }

  // Verifies that a newly added hashtype is associated with the hashcat
  // cracker binaries, which support all hashtypes, but not with binaries of
  // other types, which keep their manually associated hashtypes.
  public function testAddHashtypeAssociatesAllBinaries(): void {
    $hashcatBinary = $this->createCrackerBinary($this->hashcatBinaryType());
    $genericBinary = $this->createCrackerBinary($this->createCrackerBinaryType());
    $hashtypeId = 999004;

    $hashtype = HashtypeUtils::addHashtype($hashtypeId, 'assoc_desc', 0, false, $this->user);

    $associatedHashcat = false;
    foreach (CrackerUtils::getHashtypesOfBinary($hashcatBinary->getId()) as $hashtypeObj) {
      if ($hashtypeObj->getId() == $hashtype->getId()) {
        $associatedHashcat = true;
      }
    }
    $this->assertTrue($associatedHashcat, "Hashtype was not associated with the hashcat binary");
    foreach (CrackerUtils::getHashtypesOfBinary($genericBinary->getId()) as $hashtypeObj) {
      $this->assertNotEquals($hashtype->getId(), $hashtypeObj->getId(), "Hashtype must not be associated with the generic binary");
    }

    HashtypeUtils::deleteHashtype($hashtype->getId());
  }

  // Returns the hashcat cracker binary type, creating it first when the
  // initial data does not contain it.
  private function hashcatBinaryType(): CrackerBinaryType {
    $qF = new QueryFilter(CrackerBinaryType::TYPE_NAME, CrackerUtils::HASHCAT_BINARY_TYPE, "=");
    $type = Factory::getCrackerBinaryTypeFactory()->filter([Factory::FILTER => $qF], true);
    if ($type === null) {
      $type = $this->createDatabaseObject(
        Factory::getCrackerBinaryTypeFactory(),
        new CrackerBinaryType(null, CrackerUtils::HASHCAT_BINARY_TYPE, 1)
      );
    }
    return $type;
  }
  
  public function testDeleteHashtypeRemovesHashtype(): void {
    $hashtype = $this->createHashType();
    
    HashtypeUtils::deleteHashtype($hashtype->getId());
    
    $this->assertNull(Factory::getHashTypeFactory()->get($hashtype->getId()));
  }
  
  public function testDeleteHashtypeThrowsForInvalidId(): void {
    $this->expectException(HTException::class);
    HashtypeUtils::deleteHashtype(-1);
  }
  
  public function testDeleteHashtypeThrowsWhenHashlistsExist(): void {
    $hashtype = $this->createHashType();
    $accessGroup = $this->createAccessGroup('ht_del');
    $this->createHashlist($accessGroup, $hashtype);
    
    $this->expectException(HTException::class);
    HashtypeUtils::deleteHashtype($hashtype->getId());
  }

  // Verifies that deleting a hashtype removes its associations with cracker
  // binaries, so the foreign key constraint does not fail on deletion.
  public function testDeleteHashtypeRemovesAssociations(): void {
    $binaryType = $this->createCrackerBinaryType();
    $binary = $this->createCrackerBinary($binaryType);
    $hashtype = $this->createHashType();
    CrackerUtils::addHashtypeToBinary($binary->getId(), $hashtype->getId());
    $this->assertEquals([$hashtype->getId()], array_map(fn($ht) => $ht->getId(), CrackerUtils::getHashtypesOfBinary($binary->getId())));

    HashtypeUtils::deleteHashtype($hashtype->getId());

    $this->assertNull(Factory::getHashTypeFactory()->get($hashtype->getId()));
    $this->assertEquals([], CrackerUtils::getHashtypesOfBinary($binary->getId()));
  }
}
