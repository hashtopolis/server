<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\User;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class ConcatLikeFilterInsensitiveTest extends TestBase {
  public function testQueryStringSingleColumn(): void {
    $col = new ConcatColumn(Hashlist::HASHLIST_ID, Factory::getHashlistFactory());
    $filter = new ConcatLikeFilterInsensitive([$col], '%test%');
    $this->assertEquals(
      'LOWER(CONCAT(Hashlist.hashlistId)) LIKE LOWER(?)',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  public function testQueryStringMultipleColumns(): void {
    $col1 = new ConcatColumn(Hashlist::HASHLIST_ID, Factory::getHashlistFactory());
    $col2 = new ConcatColumn(Hashlist::HASHLIST_NAME, Factory::getHashlistFactory());
    $filter = new ConcatLikeFilterInsensitive([$col1, $col2], '%test%');
    $this->assertEquals(
      'LOWER(CONCAT(Hashlist.hashlistId, Hashlist.hashlistName)) LIKE LOWER(?)',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  public function testQueryStringMappedTable(): void {
    $col = new ConcatColumn(User::USERNAME, Factory::getUserFactory());
    $filter = new ConcatLikeFilterInsensitive([$col], '%test%');
    $this->assertEquals(
      'LOWER(CONCAT(htp_User.username)) LIKE LOWER(?)',
      $filter->getQueryString(Factory::getUserFactory())
    );
  }
  
  public function testGetValue(): void {
    $col = new ConcatColumn(Hashlist::HASHLIST_ID, Factory::getHashlistFactory());
    $filter = new ConcatLikeFilterInsensitive([$col], '%search%');
    $this->assertEquals('%search%', $filter->getValue());
  }
  
  public function testGetHasValue(): void {
    $col = new ConcatColumn(Hashlist::HASHLIST_ID, Factory::getHashlistFactory());
    $filter = new ConcatLikeFilterInsensitive([$col], '%test%');
    $this->assertTrue($filter->getHasValue());
  }
  
  /**
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
