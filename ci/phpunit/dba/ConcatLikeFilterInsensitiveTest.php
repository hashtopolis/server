<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class ConcatLikeFilterInsensitiveTest extends TestBase {
  /** Verify single-column CONCAT produces 'LOWER(CONCAT(Table.col)) LIKE LOWER(?)'. */
  public function testQueryStringSingleColumn(): void {
    $col = new ConcatColumn(Hashlist::HASHLIST_ID, Factory::getHashlistFactory());
    $filter = new ConcatLikeFilterInsensitive([$col], '%test%');
    $this->assertEquals(
      'LOWER(CONCAT(Hashlist.hashlistId)) LIKE LOWER(?)',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify multiple columns produce 'LOWER(CONCAT(col1, col2)) LIKE LOWER(?)'. */
  public function testQueryStringMultipleColumns(): void {
    $col1 = new ConcatColumn(Hashlist::HASHLIST_ID, Factory::getHashlistFactory());
    $col2 = new ConcatColumn(Hashlist::HASHLIST_NAME, Factory::getHashlistFactory());
    $filter = new ConcatLikeFilterInsensitive([$col1, $col2], '%test%');
    $this->assertEquals(
      'LOWER(CONCAT(Hashlist.hashlistId, Hashlist.hashlistName)) LIKE LOWER(?)',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify mapped table name (htp_User) appears in the CONCAT expression. */
  public function testQueryStringMappedTable(): void {
    $col = new ConcatColumn(User::USERNAME, Factory::getUserFactory());
    $filter = new ConcatLikeFilterInsensitive([$col], '%test%');
    $this->assertEquals(
      'LOWER(CONCAT(htp_User.username)) LIKE LOWER(?)',
      $filter->getQueryString(Factory::getUserFactory())
    );
  }
  
  /** Verify getValue returns the pattern passed to the constructor. */
  public function testGetValue(): void {
    $col = new ConcatColumn(Hashlist::HASHLIST_ID, Factory::getHashlistFactory());
    $filter = new ConcatLikeFilterInsensitive([$col], '%search%');
    $this->assertEquals('%search%', $filter->getValue());
  }
  
  /** Verify getHasValue always returns true. */
  public function testGetHasValue(): void {
    $col = new ConcatColumn(Hashlist::HASHLIST_ID, Factory::getHashlistFactory());
    $filter = new ConcatLikeFilterInsensitive([$col], '%test%');
    $this->assertTrue($filter->getHasValue());
  }
  
  /**
   * Create 3 hash types: "HelloWorld_*", "helloworld_*", "other_*".
   * Filter with case-insensitive CONCAT LIKE for "%helloworld_*" —
   * both "HelloWorld_*" and "helloworld_*" should match.
   *
   * @throws Exception
   */
  public function testFilterCaseInsensitive(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'HelloWorld' . $testId, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'helloworld' . $testId, 0, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'other' . $testId, 0, 0));
    
    $col = new ConcatColumn(HashType::DESCRIPTION, Factory::getHashTypeFactory());
    $filter = new ConcatLikeFilterInsensitive([$col], '%helloworld' . $testId);
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(2, $result);
  }
  
  /**
   * Create a hash type and filter with a pattern that does not match —
   * result should be empty.
   *
   * @throws Exception
   */
  public function testFilterCaseInsensitiveNoMatch(): void {
    $testId = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'HelloWorld' . $testId, 1, 0));
    
    $col = new ConcatColumn(HashType::DESCRIPTION, Factory::getHashTypeFactory());
    $filter = new ConcatLikeFilterInsensitive([$col], '%nonexistent' . $testId);
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(0, $result);
  }
  
  /**
   * Create two hashlist + hashtype pairs and filter with a CONCAT LIKE
   * across columns from both joined tables. Only the pair whose
   * concatenated hashlistName + hashtype description matches the pattern
   * should be returned.
   *
   * @throws Exception
   */
  public function testFilterCrossTableConcatLike(): void {
    $testId = uniqid();
    
    $hashType = $this->createDatabaseObject(
      Factory::getHashTypeFactory(),
      new HashType(null, 'crypto' . $testId, 0, 0)
    );
    
    $group = $this->createDatabaseObject(
      Factory::getAccessGroupFactory(),
      new AccessGroup(null, 'clf_cross_' . $testId)
    );
    
    $hashlist = $this->createDatabaseObject(
      Factory::getHashlistFactory(),
      new Hashlist(null, 'bitcoin' . $testId, DHashlistFormat::PLAIN, $hashType->getId(), 1, ':', 0, 0, 0, 0, $group->getId(), '', 0, 0, 0)
    );
    
    // second — non-matching — pair
    $hashType2 = $this->createDatabaseObject(
      Factory::getHashTypeFactory(),
      new HashType(null, 'other' . $testId, 0, 0)
    );
    $this->createDatabaseObject(
      Factory::getHashlistFactory(),
      new Hashlist(null, 'something' . $testId, DHashlistFormat::PLAIN, $hashType2->getId(), 1, ':', 0, 0, 0, 0, $group->getId(), '', 0, 0, 0)
    );
    
    $jF = new JoinFilter(Factory::getHashTypeFactory(), Hashlist::HASH_TYPE_ID, HashType::HASH_TYPE_ID);
    
    $col1 = new ConcatColumn(Hashlist::HASHLIST_NAME, Factory::getHashlistFactory());
    $col2 = new ConcatColumn(HashType::DESCRIPTION, Factory::getHashTypeFactory());
    $filter = new ConcatLikeFilterInsensitive([$col1, $col2], '%bitcoin%crypto%' . $testId);
    
    $joined = Factory::getHashlistFactory()->filter([Factory::JOIN => $jF, Factory::FILTER => $filter]);
    
    $hashlists = $joined[Factory::getHashlistFactory()->getModelName()];
    $hashTypes = $joined[Factory::getHashTypeFactory()->getModelName()];
    
    $this->assertCount(1, $hashlists);
    $this->assertCount(1, $hashTypes);
    $this->assertEquals($hashlist->getId(), $hashlists[0]->getId());
    $this->assertEquals($hashType->getId(), $hashTypes[0]->getId());
  }
}
