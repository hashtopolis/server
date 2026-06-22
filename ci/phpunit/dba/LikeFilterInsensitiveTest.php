<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class LikeFilterInsensitiveTest extends TestBase {
  /** Verify query string without table prefix uses 'LOWER(key) LIKE LOWER(?)'. */
  public function testQueryStringBasic(): void {
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_ID, '%test%');
    $this->assertEquals(
      'LOWER(hashlistId) LIKE LOWER(?)',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }
  
  /** Verify query string with includeTable=true prefixes the table name. */
  public function testQueryStringWithTable(): void {
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_ID, '%test%');
    $this->assertEquals(
      'LOWER(Hashlist.hashlistId) LIKE LOWER(?)',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }
  
  /** Verify overrideFactory forces column resolution from the override, ignoring the passed factory. */
  public function testQueryStringOverrideFactory(): void {
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_ID, '%test%', Factory::getHashlistFactory());
    $this->assertEquals(
      'LOWER(hashlistId) LIKE LOWER(?)',
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
  
  /** Verify mapped column name (htp_end) is used when the column has dba_mapping = True. */
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
   * Only the 2 hashlists whose name contains the prefix should be returned.
   */
  public function testFilterLikeBasic(): void {
    $testid = uniqid();
    $hashType = $this->createHashType();
    $ag = $this->createAccessGroup('ag_' . $testid);
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
   * Create 2 hashlists with names that have the same content but different
   * casing (e.g. "FindMe_xxx" and "findme_yyy") and filter with a case-
   * insensitive LIKE — both should match.
   * @throws Exception
   */
  public function testFilterLikeCaseInsensitive(): void {
    $testid = uniqid();
    $hashType = $this->createHashType();
    $ag = $this->createAccessGroup('ag_' . $testid);
    
    $this->createDatabaseObject(
      Factory::getHashlistFactory(),
      new Hashlist(null, 'TestCase_' . $testid, DHashlistFormat::PLAIN, $hashType->getId(), 1, ':', 0, 0, 0, 0, $ag->getId(), '', 0, 0, 0)
    );
    $this->createDatabaseObject(
      Factory::getHashlistFactory(),
      new Hashlist(null, 'testcase_' . $testid, DHashlistFormat::PLAIN, $hashType->getId(), 1, ':', 0, 0, 0, 0, $ag->getId(), '', 0, 0, 0)
    );
    
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_NAME, '%testcase_' . $testid . '%');
    $results = Factory::getHashlistFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(2, $results);
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
    
    $filter = new LikeFilterInsensitive(Hashlist::HASHLIST_NAME, '%nomatch_' . $testid . '%');
    $results = Factory::getHashlistFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(0, $results);
  }
  
  /**
   * Filter User::USERNAME using UserFactory (isMapping() = True).
   * Verifies the mapped table name (htp_User) resolves correctly in an actual query.
   */
  public function testFilterLikeMappedTable(): void {
    $testid = uniqid();
    $user = $this->createUser('mapped_' . $testid);
    
    $filter = new LikeFilterInsensitive(User::USERNAME, '%mapped_' . $testid . '%');
    $results = Factory::getUserFactory()->filter([Factory::FILTER => $filter]);
    
    $this->assertCount(1, $results);
    $this->assertInstanceOf(User::class, $results[0]);
    $this->assertEquals($user->getId(), $results[0]->getId());
  }
}
