<?php

namespace dba;

use TestBase;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\OrderFilter;
use Exception;
use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\utils\AccessUtils;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\User;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class AbstractModelFactoryTest extends TestBase {
  /**
   * @throws Exception
   */
  public function testGetDBWithTest(): void {
    $db = Factory::getAgentFactory()->getDB(true);
    
    $this->assertNotNull($db);
  }
  
  /**
   * Tests both cases to be used on a simple QueryFilter with no result.
   * When single is true, null must be returned if no matching entry was found, empty array otherwise
   *
   * @return void
   */
  public function testSimpleFilter(): void {
    $qF = new QueryFilter(User::USER_ID, 99999, "=");
    
    $user = Factory::getUserFactory()->filter([Factory::FILTER => $qF], true);
    $this->assertSame(null, $user);
    
    $user = Factory::getUserFactory()->filter([Factory::FILTER => $qF]);
    $this->assertSame([], $user);
  }
  
  /**
   * Tests the columnFilter function which returns an array of values of the given column of matching rows
   *
   * @return void
   */
  public function testColumnFilter(): void {
    // add some data
    $hashlist_1 = $this->createDatabaseObject(Factory::getHashlistFactory(), new Hashlist(null, "hashlist 1", DHashlistFormat::PLAIN, 0, 0, ':', 0, 0, 0, 0, AccessUtils::getOrCreateDefaultAccessGroup()->getId(), "", 0, 0, 0));
    $this->createDatabaseObject(Factory::getHashlistFactory(), new Hashlist(null, "hashlist 2", DHashlistFormat::PLAIN, 0, 0, ':', 0, 0, 0, 1, AccessUtils::getOrCreateDefaultAccessGroup()->getId(), "", 0, 0, 0));
    $hashlist_3 = $this->createDatabaseObject(Factory::getHashlistFactory(), new Hashlist(null, "hashlist 3", DHashlistFormat::PLAIN, 0, 0, ':', 0, 0, 0, 0, AccessUtils::getOrCreateDefaultAccessGroup()->getId(), "", 0, 0, 0));
    
    $oF = new OrderFilter(Hashlist::HASHLIST_ID, "ASC");
    
    // test column filter to retrieve some of their IDs
    $qF = new QueryFilter(Hashlist::IS_SALTED, 0, "=");
    $ids = Factory::getHashlistFactory()->columnFilter([Factory::FILTER => $qF, Factory::ORDER => $oF], Hashlist::HASHLIST_ID);
    
    // hashlist 1 and 3 should be returned
    $this->assertSame([$hashlist_1->getId(), $hashlist_3->getId()], $ids);
    
    $qF = new QueryFilter(Hashlist::CRACKED, 0, ">");
    $ids = Factory::getHashlistFactory()->columnFilter([Factory::FILTER => $qF, Factory::ORDER => $oF], Hashlist::HASHLIST_ID);
    $this->assertSame([], $ids);
  }
}
