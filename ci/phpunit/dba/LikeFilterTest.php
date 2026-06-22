<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\StartupConfig;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class LikeFilterTest extends TestBase {
  /** Verify MySQL: `column LIKE BINARY ?` when no table prefix is requested. */
  public function testQueryStringBasic(): void {
    putenv('HASHTOPOLIS_DB_TYPE=mysql');
    StartupConfig::getInstance(true);
    $filter = new LikeFilter(Hashlist::HASHLIST_ID, '%test%');
    $this->assertEquals(
      'hashlistId LIKE BINARY ?',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify MySQL: `Table.column LIKE BINARY ?` when includeTable=true. */
  public function testQueryStringWithTable(): void {
    putenv('HASHTOPOLIS_DB_TYPE=mysql');
    StartupConfig::getInstance(true);
    $filter = new LikeFilter(Hashlist::HASHLIST_ID, '%test%');
    $this->assertEquals(
      'Hashlist.hashlistId LIKE BINARY ?',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify MySQL: `column NOT LIKE BINARY ?` when setMatch(false). */
  public function testQueryStringNotMatch(): void {
    putenv('HASHTOPOLIS_DB_TYPE=mysql');
    StartupConfig::getInstance(true);
    $filter = new LikeFilter(Hashlist::HASHLIST_ID, '%test%');
    $filter->setMatch(false);
    $this->assertEquals(
      'hashlistId NOT LIKE BINARY ?',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify MySQL: `Table.column NOT LIKE BINARY ?` when includeTable=true and setMatch(false). */
  public function testQueryStringNotMatchWithTable(): void {
    putenv('HASHTOPOLIS_DB_TYPE=mysql');
    StartupConfig::getInstance(true);
    $filter = new LikeFilter(Hashlist::HASHLIST_ID, '%test%');
    $filter->setMatch(false);
    $this->assertEquals(
      'Hashlist.hashlistId NOT LIKE BINARY ?',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify MySQL: overrideFactory forces column resolution from the override regardless of the passed factory. */
  public function testQueryStringOverrideFactory(): void {
    putenv('HASHTOPOLIS_DB_TYPE=mysql');
    StartupConfig::getInstance(true);
    $filter = new LikeFilter(Hashlist::HASHLIST_ID, '%test%', Factory::getHashlistFactory());
    $this->assertEquals(
      'hashlistId LIKE BINARY ?',
      $filter->getQueryString(Factory::getUserFactory())
    );
  }
  
  /** Verify MySQL: mapped table name (htp_User) is used when factory has isMapping() = True. */
  public function testQueryStringMappedTable(): void {
    putenv('HASHTOPOLIS_DB_TYPE=mysql');
    StartupConfig::getInstance(true);
    $filter = new LikeFilter(User::USERNAME, '%admin%');
    $this->assertEquals(
      'htp_User.username LIKE BINARY ?',
      $filter->getQueryString(Factory::getUserFactory(), true)
    );
  }
  
  /** Verify MySQL: mapped column name (htp_end) is used when the column has dba_mapping = True. */
  public function testQueryStringMappedColumn(): void {
    putenv('HASHTOPOLIS_DB_TYPE=mysql');
    StartupConfig::getInstance(true);
    $filter = new LikeFilter(HealthCheckAgent::END, '%5%');
    $this->assertEquals(
      'HealthCheckAgent.htp_end LIKE BINARY ?',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory(), true)
    );
  }
  
  /** Verify Postgres: `column LIKE ? COLLATE "C"` without table prefix. */
  public function testQueryStringPostgres(): void {
    putenv('HASHTOPOLIS_DB_TYPE=postgres');
    StartupConfig::getInstance(true);
    $filter = new LikeFilter(Hashlist::HASHLIST_ID, '%test%');
    $this->assertEquals(
      'hashlistId LIKE ? COLLATE "C"',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify Postgres: `Table.column LIKE ? COLLATE "C"` with includeTable=true. */
  public function testQueryStringWithTablePostgres(): void {
    putenv('HASHTOPOLIS_DB_TYPE=postgres');
    StartupConfig::getInstance(true);
    $filter = new LikeFilter(Hashlist::HASHLIST_ID, '%test%');
    $this->assertEquals(
      'Hashlist.hashlistId LIKE ? COLLATE "C"',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify Postgres: `column NOT LIKE ? COLLATE "C"` when setMatch(false). */
  public function testQueryStringNotMatchPostgres(): void {
    putenv('HASHTOPOLIS_DB_TYPE=postgres');
    StartupConfig::getInstance(true);
    $filter = new LikeFilter(Hashlist::HASHLIST_ID, '%test%');
    $filter->setMatch(false);
    $this->assertEquals(
      'hashlistId NOT LIKE ? COLLATE "C"',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify Postgres: `Table.column NOT LIKE ? COLLATE "C"` with includeTable=true and setMatch(false). */
  public function testQueryStringNotMatchWithTablePostgres(): void {
    putenv('HASHTOPOLIS_DB_TYPE=postgres');
    StartupConfig::getInstance(true);
    $filter = new LikeFilter(Hashlist::HASHLIST_ID, '%test%');
    $filter->setMatch(false);
    $this->assertEquals(
      'Hashlist.hashlistId NOT LIKE ? COLLATE "C"',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify Postgres: overrideFactory forces column resolution from the override regardless of the passed factory. */
  public function testQueryStringOverrideFactoryPostgres(): void {
    putenv('HASHTOPOLIS_DB_TYPE=postgres');
    StartupConfig::getInstance(true);
    $filter = new LikeFilter(Hashlist::HASHLIST_ID, '%test%', Factory::getHashlistFactory());
    $this->assertEquals(
      'hashlistId LIKE ? COLLATE "C"',
      $filter->getQueryString(Factory::getUserFactory())
    );
  }
  
  /** Verify Postgres: mapped table name (htp_User) with LIKE ? COLLATE "C". */
  public function testQueryStringMappedTablePostgres(): void {
    putenv('HASHTOPOLIS_DB_TYPE=postgres');
    StartupConfig::getInstance(true);
    $filter = new LikeFilter(User::USERNAME, '%admin%');
    $this->assertEquals(
      'htp_User.username LIKE ? COLLATE "C"',
      $filter->getQueryString(Factory::getUserFactory(), true)
    );
  }
  
  /** Verify Postgres: mapped column name (htp_end) with LIKE ? COLLATE "C". */
  public function testQueryStringMappedColumnPostgres(): void {
    putenv('HASHTOPOLIS_DB_TYPE=postgres');
    StartupConfig::getInstance(true);
    $filter = new LikeFilter(HealthCheckAgent::END, '%5%');
    $this->assertEquals(
      'HealthCheckAgent.htp_end LIKE ? COLLATE "C"',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory(), true)
    );
  }
  
  /** Verify getValue returns the pattern passed to the constructor. */
  public function testGetValue(): void {
    $filter = new LikeFilter(Hashlist::HASHLIST_ID, '%search%');
    $this->assertEquals('%search%', $filter->getValue());
  }
  
  /** Verify getHasValue always returns true. */
  public function testGetHasValue(): void {
    $filter = new LikeFilter(Hashlist::HASHLIST_ID, '%test%');
    $this->assertTrue($filter->getHasValue());
  }
  
  /**
   * Create 3 hashlists and filter on hashlistName with a matching prefix.
   * All 3 hashlists whose name contains the prefix should be returned.
   */
  public function testFilterLikeBasic(): void {
    $hashType = $this->createHashType();
    $ag = $this->createAccessGroup('ag_' . uniqid());
    $this->createHashlist($ag, $hashType);
    $this->createHashlist($ag, $hashType);
    $this->createHashlist($ag, $hashType);
    
    $filter = new LikeFilter(Hashlist::HASHLIST_NAME, 'hashlist_%');
    $results = Factory::getHashlistFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(3, $results[Factory::getHashlistFactory()->getModelName()]);
    foreach ($results[Factory::getHashlistFactory()->getModelName()] as $hl) {
      $this->assertInstanceOf(Hashlist::class, $hl);
    }
  }
  
  /**
   * Create 3 hashlists and use setMatch(false) to exclude those matching
   * the pattern. Only the non-matching hashlists should be returned.
   * @throws Exception
   */
  public function testFilterLikeNotMatch(): void {
    $testid = uniqid();
    $hashType = $this->createHashType();
    $ag = $this->createAccessGroup('ag_' . $testid);
    $hl1 = $this->createDatabaseObject(
      Factory::getHashlistFactory(),
      new Hashlist(null, 'keep_' . $testid, DHashlistFormat::PLAIN, $hashType->getId(), 1, ':', 0, 0, 0, 0, $ag->getId(), '', 0, 0, 0)
    );
    $hl2 = $this->createDatabaseObject(
      Factory::getHashlistFactory(),
      new Hashlist(null, 'exclude_' . $testid, DHashlistFormat::PLAIN, $hashType->getId(), 1, ':', 0, 0, 0, 0, $ag->getId(), '', 0, 0, 0)
    );
    
    $filter = new LikeFilter(Hashlist::HASHLIST_NAME, '%exclude_' . $testid . '%');
    $filter->setMatch(false);
    $results = Factory::getHashlistFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(1, $results[Factory::getHashlistFactory()->getModelName()]);
    $this->assertEquals('keep_' . $testid, $results[Factory::getHashlistFactory()->getModelName()][0]->getHashlistName());
  }
  
  /**
   * Filter on hashlistName with a pattern that matches none of the existing
   * hashlists — the result array should be empty.
   */
  public function testFilterLikeNoMatch(): void {
    $testid = uniqid();
    $hashType = $this->createHashType();
    $ag = $this->createAccessGroup('ag_' . $testid);
    $this->createHashlist($ag, $hashType);
    $this->createHashlist($ag, $hashType);
    
    $filter = new LikeFilter(Hashlist::HASHLIST_NAME, '%nomatch_' . $testid . '%');
    $results = Factory::getHashlistFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(0, $results[Factory::getHashlistFactory()->getModelName()]);
  }
  
  /**
   * Filter User::USERNAME using UserFactory (isMapping() = True).
   * Verifies the mapped table name (htp_User) resolves correctly in an actual query.
   */
  public function testFilterLikeMappedTable(): void {
    $testid = uniqid();
    $user = $this->createUser('mapped_' . $testid);
    
    $filter = new LikeFilter(User::USERNAME, '%mapped_' . $testid . '%');
    $results = Factory::getUserFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(1, $results[Factory::getUserFactory()->getModelName()]);
    $this->assertInstanceOf(User::class, $results[Factory::getUserFactory()->getModelName()][0]);
    $this->assertEquals($user->getId(), $results[Factory::getUserFactory()->getModelName()][0]->getId());
  }
}
