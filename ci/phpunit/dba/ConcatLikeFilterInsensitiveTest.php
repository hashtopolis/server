<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\User;
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
    $testid = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'HelloWorld' . $testid, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'helloworld' . $testid, 0, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'other' . $testid, 0, 0));
    
    $col = new ConcatColumn(HashType::DESCRIPTION, Factory::getHashTypeFactory());
    $filter = new ConcatLikeFilterInsensitive([$col], '%helloworld' . $testid);
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
    $testid = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'HelloWorld' . $testid, 1, 0));
    
    $col = new ConcatColumn(HashType::DESCRIPTION, Factory::getHashTypeFactory());
    $filter = new ConcatLikeFilterInsensitive([$col], '%nonexistent' . $testid);
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(0, $result);
  }
}
