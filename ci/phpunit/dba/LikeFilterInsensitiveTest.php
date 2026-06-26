<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\StartupConfig;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class LikeFilterInsensitiveTest extends TestBase {
  /** Verify PostgreSQL: `col::text LIKE LOWER(?)` for int column. */
  public function testQueryStringBasic(): void {
    putenv('HASHTOPOLIS_DB_TYPE=postgres');
    StartupConfig::getInstance(true);
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_ID, '%test%');
    $this->assertEquals(
      'hashlistId::text LIKE LOWER(?)',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify MySQL: `CONVERT(col, CHAR) LIKE LOWER(?)` for int column. */
  public function testQueryStringBasicMysql(): void {
    putenv('HASHTOPOLIS_DB_TYPE=mysql');
    StartupConfig::getInstance(true);
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_ID, '%test%');
    $this->assertEquals(
      'CONVERT(hashlistId, CHAR) LIKE LOWER(?)',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify PostgreSQL: `Table.col::text LIKE LOWER(?)` with includeTable=true. */
  public function testQueryStringWithTable(): void {
    putenv('HASHTOPOLIS_DB_TYPE=postgres');
    StartupConfig::getInstance(true);
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_ID, '%test%');
    $this->assertEquals(
      'Hashlist.hashlistId::text LIKE LOWER(?)',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify MySQL: `CONVERT(Table.col, CHAR) LIKE LOWER(?)` with includeTable=true. */
  public function testQueryStringWithTableMysql(): void {
    putenv('HASHTOPOLIS_DB_TYPE=mysql');
    StartupConfig::getInstance(true);
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_ID, '%test%');
    $this->assertEquals(
      'CONVERT(Hashlist.hashlistId, CHAR) LIKE LOWER(?)',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify overrideFactory forces column resolution from the override, ignoring the passed factory. */
  public function testQueryStringOverrideFactory(): void {
    putenv('HASHTOPOLIS_DB_TYPE=postgres');
    StartupConfig::getInstance(true);
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_ID, '%test%', Factory::getHashlistFactory());
    $this->assertEquals(
      'hashlistId::text LIKE LOWER(?)',
      $filter->getQueryString(Factory::getUserFactory())
    );
  }
  
  /** Verify mapped table name (htp_User) is used when factory has isMapping() = True. */
  public function testQueryStringMappedTable(): void {
    $filter = new LikeFilterInsensitive(User::USERNAME, '%admin%');
    $this->assertEquals(
      'LOWER(htp_User.username) LIKE LOWER(?)',
      $filter->getQueryString(Factory::getUserFactory(), true)
    );
  }
  
  /**
   * Verify mapped int64 column (htp_end) — the type check in the current
   * implementation only covers 'int', not 'int64', so LOWER() is still
   * applied to the mapped column name.
   */
  public function testQueryStringMappedColumn(): void {
    $filter = new LikeFilterInsensitive(HealthCheckAgent::END, '%5%');
    $this->assertEquals(
      'LOWER(HealthCheckAgent.htp_end) LIKE LOWER(?)',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory(), true)
    );
  }
  
  /** Verify getValue returns the pattern passed to the constructor. */
  public function testGetValue(): void {
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_ID, '%search%');
    $this->assertEquals('%search%', $filter->getValue());
  }
  
  /** Verify getHasValue always returns true. */
  public function testGetHasValue(): void {
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_ID, '%test%');
    $this->assertTrue($filter->getHasValue());
  }
  
  /** Verify getKey returns the column key passed to the constructor. */
  public function testGetKey(): void {
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_NAME, '%test%');
    $this->assertEquals(Hashlist::HASHLIST_NAME, $filter->getKey());
  }
  
  /**
   * Create 3 hashlists and filter on hashlistName with a matching prefix.
   *
   * @throws Exception
   */
  public function testFilterLikeBasic(): void {
    $testId = uniqid();
    $hashType = $this->createHashType();
    $ag = $this->createAccessGroup('ag_' . $testId);
    $this->createHashlist($ag, $hashType);
    $this->createHashlist($ag, $hashType);
    $this->createHashlist($ag, $hashType);
    
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_NAME, 'hashlist_%');
    $results = Factory::getHashlistFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(3, $results);
    foreach ($results as $hl) {
      $this->assertInstanceOf(Hashlist::class, $hl);
    }
  }
  
  /**
   * Verify case-insensitive matching — both uppercase and lowercase names
   * are found.
   *
   * @throws Exception
   */
  public function testFilterLikeCaseInsensitive(): void {
    $testId = uniqid();
    $hashType = $this->createHashType();
    $ag = $this->createAccessGroup('ag_' . $testId);
    
    $this->createDatabaseObject(
      Factory::getHashlistFactory(),
      new Hashlist(null, 'TestCase_' . $testId, DHashlistFormat::PLAIN, $hashType->getId(), 1, ':', 0, 0, 0, 0, $ag->getId(), '', 0, 0, 0)
    );
    $this->createDatabaseObject(
      Factory::getHashlistFactory(),
      new Hashlist(null, 'testcase_' . $testId, DHashlistFormat::PLAIN, $hashType->getId(), 1, ':', 0, 0, 0, 0, $ag->getId(), '', 0, 0, 0)
    );
    
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_NAME, '%testcase_' . $testId . '%');
    $results = Factory::getHashlistFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(2, $results);
  }
  
  /**
   * Filter with a pattern that matches none of the existing hashlists —
   * result should be empty.
   *
   * @throws Exception
   */
  public function testFilterLikeNoMatch(): void {
    $testId = uniqid();
    $hashType = $this->createHashType();
    $ag = $this->createAccessGroup('ag_' . $testId);
    $this->createHashlist($ag, $hashType);
    $this->createHashlist($ag, $hashType);
    
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_NAME, '%nomatch_' . $testId . '%');
    $results = Factory::getHashlistFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(0, $results);
  }
  
  /**
   * Filter User::USERNAME using UserFactory (isMapping() = True).
   * Verifies the mapped table name (htp_User) resolves correctly in an actual query.
   *
   * @throws Exception
   */
  public function testFilterLikeMappedTable(): void {
    $testId = uniqid();
    $user = $this->createUser('mapped_' . $testId);
    
    $filter = new LikeFilterInsensitive(User::USERNAME, '%mapped_' . $testId . '%');
    $results = Factory::getUserFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(1, $results);
    $this->assertInstanceOf(User::class, $results[0]);
    $this->assertEquals($user->getId(), $results[0]->getId());
  }
  
  /**
   * Verify PostgreSQL query string for integer column hashTypeId on
   * HashType — casts with ::text and omits LOWER().
   *
   * @throws Exception
   */
  public function testQueryStringIntegerColumn(): void {
    putenv('HASHTOPOLIS_DB_TYPE=postgres');
    StartupConfig::getInstance(true);
    $filter = new LikeFilterInsensitive(HashType::HASH_TYPE_ID, '%1%');
    $this->assertEquals(
      'hashTypeId::text LIKE LOWER(?)',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }
  
  /**
   * Verify MySQL query string for integer column hashTypeId on
   * HashType — wraps with CONVERT(col, CHAR) and omits LOWER().
   *
   * @throws Exception
   */
  public function testQueryStringIntegerColumnMysql(): void {
    putenv('HASHTOPOLIS_DB_TYPE=mysql');
    StartupConfig::getInstance(true);
    $filter = new LikeFilterInsensitive(HashType::HASH_TYPE_ID, '%1%');
    $this->assertEquals(
      'CONVERT(hashTypeId, CHAR) LIKE LOWER(?)',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }
  
  /**
   * Verify query string for the string column description on HashType —
   * LOWER() is preserved for text-based columns regardless of DB type.
   *
   * @throws Exception
   */
  public function testQueryStringStringColumn(): void {
    $filter = new LikeFilterInsensitive(HashType::DESCRIPTION, '%test%');
    $this->assertEquals(
      'LOWER(description) LIKE LOWER(?)',
      $filter->getQueryString(Factory::getHashTypeFactory())
    );
  }
  
  /**
   * Integration test: create 2 HashType objects and filter on hashTypeId
   * (integer primary key) with LikeFilterInsensitive. Verifies the int
   * cast works end-to-end on the current database backend.
   *
   * @throws Exception
   */
  public function testFilterLikeIntegerColumn(): void {
    $hashType1 = $this->createHashType();
    $this->createHashType();
    
    $id1 = $hashType1->getId();
    
    $filter = new LikeFilterInsensitive(HashType::HASH_TYPE_ID, '%' . $id1 . '%');
    $results = Factory::getHashTypeFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(1, $results);
    $this->assertInstanceOf(HashType::class, $results[0]);
    $this->assertEquals($id1, $results[0]->getId());
  }
  
  /**
   * Integration test: create a HealthCheckAgent with a specific status
   * and filter on HealthCheckAgent::STATUS (int column) with
   * LikeFilterInsensitive. Verifies the int cast works end-to-end on the
   * current database backend.
   *
   * @throws Exception
   */
  public function testFilterLikeIntegerColumnHealthCheckAgent(): void {
    $crackerBinaryType = $this->createCrackerBinaryType();
    $crackerBinary = $this->createCrackerBinary($crackerBinaryType);
    $healthCheck = $this->createHealthCheck($crackerBinary);
    $agent = $this->createAgent('inttest');
    
    $statusValue = 1;
    $this->createHealthCheckAgent($healthCheck, $agent, $statusValue);
    
    $filter = new LikeFilterInsensitive(HealthCheckAgent::STATUS, '%' . $statusValue . '%');
    $results = Factory::getHealthCheckAgentFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(1, $results);
    $this->assertInstanceOf(HealthCheckAgent::class, $results[0]);
    $this->assertEquals($statusValue, $results[0]->getStatus());
  }
}
