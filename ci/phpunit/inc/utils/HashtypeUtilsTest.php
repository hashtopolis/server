<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
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

  // Verifies that a newly added hashtype is associated with all cracker
  // binaries, as it cannot be determined which binaries support it.
  public function testAddHashtypeAssociatesAllBinaries(): void {
    $binaryType = $this->createCrackerBinaryType();
    $binary1 = $this->createCrackerBinary($binaryType);
    $binary2 = $this->createCrackerBinary($binaryType);
    $hashtypeId = 999004;

    $hashtype = HashtypeUtils::addHashtype($hashtypeId, 'assoc_desc', 0, false, $this->user);

    $associated1 = false;
    $associated2 = false;
    foreach (CrackerUtils::getHashtypesOfBinary($binary1->getId()) as $hashtypeObj) {
      if ($hashtypeObj->getId() == $hashtype->getId()) {
        $associated1 = true;
      }
    }
    foreach (CrackerUtils::getHashtypesOfBinary($binary2->getId()) as $hashtypeObj) {
      if ($hashtypeObj->getId() == $hashtype->getId()) {
        $associated2 = true;
      }
    }
    $this->assertTrue($associated1, "Hashtype was not associated with the first binary");
    $this->assertTrue($associated2, "Hashtype was not associated with the second binary");

    HashtypeUtils::deleteHashtype($hashtype->getId());
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
