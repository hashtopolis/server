<?php

namespace Hashtopolis\dba;

use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\AccessGroupUser;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\User;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class JoinFilterTest extends TestBase {
  /**
   * Verify constructor stores all parameters and defaults are correct
   * (INNER join, no query filters, no override factory).
   */
  public function testConstructorAndGetters(): void {
    $other = Factory::getHashlistFactory();
    $filter = new JoinFilter($other, Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID);
    $this->assertSame($other, $filter->getOtherFactory());
    $this->assertEquals(Hashlist::HASHLIST_ID, $filter->getMatch1());
    $this->assertEquals(Hashlist::HASH_TYPE_ID, $filter->getMatch2());
    $this->assertEquals(JoinFilter::INNER, $filter->getJoinType());
    $this->assertEquals([], $filter->getQueryFilters());
    $this->assertNull($filter->getOverrideOwnFactory());
  }
  
  /** Verify LEFT join type is stored in the constructor. */
  public function testJoinTypeLeft(): void {
    $filter = new JoinFilter(Factory::getHashlistFactory(), Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, null, JoinFilter::LEFT);
    $this->assertEquals(JoinFilter::LEFT, $filter->getJoinType());
  }
  
  /** Verify RIGHT join type is stored in the constructor. */
  public function testJoinTypeRight(): void {
    $filter = new JoinFilter(Factory::getHashlistFactory(), Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, null, JoinFilter::RIGHT);
    $this->assertEquals(JoinFilter::RIGHT, $filter->getJoinType());
  }
  
  /** Verify join type can be changed after construction via setJoinType. */
  public function testSetJoinType(): void {
    $filter = new JoinFilter(Factory::getHashlistFactory(), Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID);
    $filter->setJoinType(JoinFilter::LEFT);
    $this->assertEquals(JoinFilter::LEFT, $filter->getJoinType());
  }
  
  /** Verify overrideOwnFactory is stored and returned. */
  public function testWithOverrideOwnFactory(): void {
    $override = Factory::getHashlistFactory();
    $filter = new JoinFilter(Factory::getUserFactory(), User::USER_ID, Hashlist::HASHLIST_ID, $override);
    $this->assertSame($override, $filter->getOverrideOwnFactory());
  }
  
  /** Verify queryFilters array is stored in the constructor. */
  public function testWithQueryFilters(): void {
    $qF = new QueryFilter(Hashlist::CRACKED, 0, '=');
    $filter = new JoinFilter(Factory::getHashlistFactory(), Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID, null, JoinFilter::INNER, [$qF]);
    $this->assertCount(1, $filter->getQueryFilters());
    $this->assertSame($qF, $filter->getQueryFilters()[0]);
  }
  
  /** Verify queryFilters can be replaced after construction via setQueryFilters. */
  public function testSetQueryFilters(): void {
    $filter = new JoinFilter(Factory::getHashlistFactory(), Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID);
    $filter->setQueryFilters([new QueryFilter(Hashlist::CRACKED, 0, '=')]);
    $this->assertCount(1, $filter->getQueryFilters());
  }
  
  /** Verify getOtherTableName returns the unmapped table name (Hashlist) for a non-mapped factory. */
  public function testOtherTableNameNonMapped(): void {
    $filter = new JoinFilter(Factory::getHashlistFactory(), Hashlist::HASHLIST_ID, Hashlist::HASH_TYPE_ID);
    $this->assertEquals('Hashlist', $filter->getOtherTableName());
  }
  
  /** Verify getOtherTableName returns the mapped table name (htp_User) for a mapped factory. */
  public function testOtherTableNameMapped(): void {
    $filter = new JoinFilter(Factory::getUserFactory(), User::USER_ID, User::USER_ID);
    $this->assertEquals('htp_User', $filter->getOtherTableName());
  }
  
  /**
   * INNER JOIN File with AccessGroup on accessGroupId.
   * Creates 3 files across 2 groups — all rows match, so both result
   * arrays contain 3 entries.
   */
  public function testJoinInner(): void {
    $testid = uniqid();
    $ag1 = $this->createAccessGroup('ag1_' . $testid);
    $ag2 = $this->createAccessGroup('ag2_' . $testid);
    
    $this->createFile($ag1, 0, 'file1_' . $testid, 10);
    $this->createFile($ag2, 0, 'file2_' . $testid, 20);
    $this->createFile($ag1, 0, 'file3_' . $testid, 30);
    
    $jF = new JoinFilter(Factory::getAccessGroupFactory(), File::ACCESS_GROUP_ID, AccessGroup::ACCESS_GROUP_ID);
    $joined = Factory::getFileFactory()->filter([Factory::JOIN => $jF]);
    
    $this->assertCount(3, $joined[Factory::getFileFactory()->getModelName()]);
    $this->assertCount(3, $joined[Factory::getAccessGroupFactory()->getModelName()]);
    foreach ($joined[Factory::getFileFactory()->getModelName()] as $file) {
      $this->assertInstanceOf(File::class, $file);
    }
    foreach ($joined[Factory::getAccessGroupFactory()->getModelName()] as $ag) {
      $this->assertInstanceOf(AccessGroup::class, $ag);
    }
  }
  
  /**
   * INNER JOIN combined with a QueryFilter on the joined table (AccessGroup).
   * Only files belonging to ag1 should be returned.
   */
  public function testJoinWithFilter(): void {
    $testid = uniqid();
    $ag1 = $this->createAccessGroup('ag1_' . $testid);
    $ag2 = $this->createAccessGroup('ag2_' . $testid);
    
    $this->createFile($ag1, 0, 'file1_' . $testid, 10);
    $this->createFile($ag2, 0, 'file2_' . $testid, 20);
    $this->createFile($ag1, 0, 'file3_' . $testid, 30);
    
    $qF = new QueryFilter(AccessGroup::GROUP_NAME, $ag1->getGroupName(), '=', Factory::getAccessGroupFactory());
    $jF = new JoinFilter(Factory::getAccessGroupFactory(), File::ACCESS_GROUP_ID, AccessGroup::ACCESS_GROUP_ID);
    $joined = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF]);
    
    $this->assertCount(2, $joined[Factory::getFileFactory()->getModelName()]);
    $this->assertEquals('file1_' . $testid, $joined[Factory::getFileFactory()->getModelName()][0]->getFilename());
    $this->assertEquals('file3_' . $testid, $joined[Factory::getFileFactory()->getModelName()][1]->getFilename());
  }
  
  /**
   * INNER JOIN combined with a filter and ORDER BY DESC on File::SIZE.
   * Files belonging to ag1 should be returned in descending size order.
   */
  public function testJoinWithOrder(): void {
    $testid = uniqid();
    $ag1 = $this->createAccessGroup('ag1_' . $testid);
    $ag2 = $this->createAccessGroup('ag2_' . $testid);
    
    $this->createFile($ag1, 0, 'file1_' . $testid, 10);
    $this->createFile($ag2, 0, 'file2_' . $testid, 20);
    $this->createFile($ag1, 0, 'file3_' . $testid, 30);
    
    $qF = new QueryFilter(AccessGroup::GROUP_NAME, $ag1->getGroupName(), '=', Factory::getAccessGroupFactory());
    $jF = new JoinFilter(Factory::getAccessGroupFactory(), File::ACCESS_GROUP_ID, AccessGroup::ACCESS_GROUP_ID);
    $oF = new OrderFilter(File::SIZE, 'DESC');
    $joined = Factory::getFileFactory()->filter([Factory::FILTER => $qF, Factory::JOIN => $jF, Factory::ORDER => $oF]);
    
    $this->assertCount(2, $joined[Factory::getFileFactory()->getModelName()]);
    $this->assertEquals('file3_' . $testid, $joined[Factory::getFileFactory()->getModelName()][0]->getFilename());
    $this->assertEquals('file1_' . $testid, $joined[Factory::getFileFactory()->getModelName()][1]->getFilename());
  }
  
  /**
   * INNER JOIN with the filter pushed directly into JoinFilter's
   * queryFilters parameter instead of via Factory::FILTER.
   * Same expected result as testJoinWithFilter.
   */
  public function testJoinWithQueryFilters(): void {
    $testid = uniqid();
    $ag1 = $this->createAccessGroup('ag1_' . $testid);
    $ag2 = $this->createAccessGroup('ag2_' . $testid);
    
    $this->createFile($ag1, 0, 'file1_' . $testid, 10);
    $this->createFile($ag2, 0, 'file2_' . $testid, 20);
    $this->createFile($ag1, 0, 'file3_' . $testid, 30);
    
    $qFJoin = new QueryFilter(AccessGroup::GROUP_NAME, $ag1->getGroupName(), '=', Factory::getAccessGroupFactory());
    $jF = new JoinFilter(Factory::getAccessGroupFactory(), File::ACCESS_GROUP_ID, AccessGroup::ACCESS_GROUP_ID, null, JoinFilter::INNER, [$qFJoin]);
    $joined = Factory::getFileFactory()->filter([Factory::JOIN => $jF]);
    
    $this->assertCount(2, $joined[Factory::getFileFactory()->getModelName()]);
    $this->assertEquals('file1_' . $testid, $joined[Factory::getFileFactory()->getModelName()][0]->getFilename());
    $this->assertEquals('file3_' . $testid, $joined[Factory::getFileFactory()->getModelName()][1]->getFilename());
  }
  
  /**
   * INNER JOIN AccessGroupUser with User on userId.
   * UserFactory has isMapping() = True, so the join table is htp_User.
   * Verifies the mapped table name works correctly in a real query.
   */
  public function testJoinMappedTable(): void {
    $testid = uniqid();
    $user = $this->createUser('user_' . $testid);
    $ag = $this->createAccessGroup('ag_' . $testid);
    $this->createAccessGroupUser($user, $ag);
    
    $jF = new JoinFilter(Factory::getUserFactory(), AccessGroupUser::USER_ID, User::USER_ID);
    $joined = Factory::getAccessGroupUserFactory()->filter([Factory::JOIN => $jF]);
    
    $this->assertCount(1, $joined[Factory::getAccessGroupUserFactory()->getModelName()]);
    $this->assertCount(1, $joined[Factory::getUserFactory()->getModelName()]);
    $this->assertInstanceOf(AccessGroupUser::class, $joined[Factory::getAccessGroupUserFactory()->getModelName()][0]);
    $this->assertInstanceOf(User::class, $joined[Factory::getUserFactory()->getModelName()][0]);
    $this->assertEquals($user->getId(), $joined[Factory::getUserFactory()->getModelName()][0]->getId());
  }
  
  /**
   * Two simultaneous INNER JOINs: AccessGroupUser joined with User
   * (on userId) AND with AccessGroup (on accessGroupId).
   * Verifies multiple joins are applied correctly.
   */
  public function testJoinMultipleInner(): void {
    $testid = uniqid();
    $user = $this->createUser('user_' . $testid);
    $ag = $this->createAccessGroup('ag_' . $testid);
    $this->createAccessGroupUser($user, $ag);
    
    $jF1 = new JoinFilter(Factory::getUserFactory(), AccessGroupUser::USER_ID, User::USER_ID);
    $jF2 = new JoinFilter(Factory::getAccessGroupFactory(), AccessGroupUser::ACCESS_GROUP_ID, AccessGroup::ACCESS_GROUP_ID);
    $joined = Factory::getAccessGroupUserFactory()->filter([Factory::JOIN => [$jF1, $jF2]]);
    
    $this->assertCount(1, $joined[Factory::getAccessGroupUserFactory()->getModelName()]);
    $this->assertCount(1, $joined[Factory::getUserFactory()->getModelName()]);
    $this->assertCount(1, $joined[Factory::getAccessGroupFactory()->getModelName()]);
    $this->assertInstanceOf(AccessGroupUser::class, $joined[Factory::getAccessGroupUserFactory()->getModelName()][0]);
    $this->assertInstanceOf(User::class, $joined[Factory::getUserFactory()->getModelName()][0]);
    $this->assertInstanceOf(AccessGroup::class, $joined[Factory::getAccessGroupFactory()->getModelName()][0]);
  }
  
  /**
   * LEFT JOIN AccessGroup (main) → File (joined) on accessGroupId.
   * ag2 has no files, so the third row has a File with null ID,
   * confirming LEFT JOIN preserves all rows from the main table.
   */
  public function testJoinLeft(): void {
    $testid = uniqid();
    $ag1 = $this->createAccessGroup('ag1_' . $testid);
    $ag2 = $this->createAccessGroup('ag2_' . $testid);
    $this->createFile($ag1, 0, 'file1_' . $testid, 10);
    $this->createFile($ag1, 0, 'file2_' . $testid, 20);
    
    $jF = new JoinFilter(Factory::getFileFactory(), AccessGroup::ACCESS_GROUP_ID, File::ACCESS_GROUP_ID, null, JoinFilter::LEFT);
    $joined = Factory::getAccessGroupFactory()->filter([Factory::JOIN => $jF]);
    
    $this->assertCount(3, $joined[Factory::getAccessGroupFactory()->getModelName()]);
    $this->assertCount(3, $joined[Factory::getFileFactory()->getModelName()]);
    $this->assertEquals($ag1->getId(), $joined[Factory::getAccessGroupFactory()->getModelName()][0]->getId());
    $this->assertEquals($ag1->getId(), $joined[Factory::getAccessGroupFactory()->getModelName()][1]->getId());
    $this->assertEquals($ag2->getId(), $joined[Factory::getAccessGroupFactory()->getModelName()][2]->getId());
    $this->assertNull($joined[Factory::getFileFactory()->getModelName()][2]->getId());
  }
  
  /**
   * RIGHT JOIN File (main) ← AccessGroup (joined) on accessGroupId.
   * ag2 has no files, so the third row has a File with null ID,
   * confirming RIGHT JOIN preserves all rows from the joined table.
   */
  public function testJoinRight(): void {
    $testid = uniqid();
    $ag1 = $this->createAccessGroup('ag1_' . $testid);
    $ag2 = $this->createAccessGroup('ag2_' . $testid);
    $this->createFile($ag1, 0, 'file1_' . $testid, 10);
    $this->createFile($ag1, 0, 'file2_' . $testid, 20);
    
    $jF = new JoinFilter(Factory::getAccessGroupFactory(), File::ACCESS_GROUP_ID, AccessGroup::ACCESS_GROUP_ID, null, JoinFilter::RIGHT);
    $joined = Factory::getFileFactory()->filter([Factory::JOIN => $jF]);
    
    $this->assertCount(3, $joined[Factory::getFileFactory()->getModelName()]);
    $this->assertCount(3, $joined[Factory::getAccessGroupFactory()->getModelName()]);
    $this->assertEquals($ag1->getId(), $joined[Factory::getAccessGroupFactory()->getModelName()][0]->getId());
    $this->assertEquals($ag1->getId(), $joined[Factory::getAccessGroupFactory()->getModelName()][1]->getId());
    $this->assertEquals($ag2->getId(), $joined[Factory::getAccessGroupFactory()->getModelName()][2]->getId());
    $this->assertNull($joined[Factory::getFileFactory()->getModelName()][2]->getId());
  }
}
