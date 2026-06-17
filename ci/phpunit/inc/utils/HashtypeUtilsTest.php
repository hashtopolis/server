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
    
    Factory::getHashTypeFactory()->delete($hashtype);
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
}
