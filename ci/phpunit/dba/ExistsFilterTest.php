<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\AccessGroupUser;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\User;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class ExistsFilterTest extends TestBase {
  /**
   * Verify a basic EXISTS subquery without sub-filters or baseFilter.
   * Expected: EXISTS (SELECT 1 FROM File WHERE File.accessGroupId=AccessGroup.accessGroupId)
   */
  public function testBasicExists(): void {
    $filter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID
    );
    $this->assertEquals(
      'EXISTS (SELECT 1 FROM File WHERE File.accessGroupId=AccessGroup.accessGroupId)',
      $filter->getQueryString(Factory::getAccessGroupFactory())
    );
  }
  
  /**
   * Verify NOT EXISTS is generated when inverse=true.
   * Expected: NOT EXISTS (SELECT 1 FROM File WHERE File.accessGroupId=AccessGroup.accessGroupId)
   */
  public function testNotExists(): void {
    $filter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [],
      null,
      true
    );
    $this->assertEquals(
      'NOT EXISTS (SELECT 1 FROM File WHERE File.accessGroupId=AccessGroup.accessGroupId)',
      $filter->getQueryString(Factory::getAccessGroupFactory())
    );
  }
  
  /**
   * Verify a single subquery filter is AND-ed into the WHERE clause.
   * Expected: ... AND File.size>?
   */
  public function testExistsWithSubqueryFilter(): void {
    $subFilter = new QueryFilter(File::SIZE, 100, '>');
    $filter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [$subFilter]
    );
    $this->assertEquals(
      'EXISTS (SELECT 1 FROM File WHERE File.accessGroupId=AccessGroup.accessGroupId AND File.size>?)',
      $filter->getQueryString(Factory::getAccessGroupFactory())
    );
  }
  
  /**
   * Verify multiple subquery filters are joined with AND.
   * Expected: ... AND File.size>? AND File.isSecret=?
   */
  public function testExistsWithMultipleSubqueryFilters(): void {
    $subFilter1 = new QueryFilter(File::SIZE, 100, '>');
    $subFilter2 = new QueryFilter(File::IS_SECRET, 1, '=');
    $filter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [$subFilter1, $subFilter2]
    );
    $this->assertEquals(
      'EXISTS (SELECT 1 FROM File WHERE File.accessGroupId=AccessGroup.accessGroupId AND File.size>? AND File.isSecret=?)',
      $filter->getQueryString(Factory::getAccessGroupFactory())
    );
  }
  
  /**
   * Verify baseFilter wraps the EXISTS in an OR with a null-check.
   * Expected: (EXISTS (...) OR AccessGroup.accessGroupId IS NULL )
   */
  public function testExistsWithBaseFilter(): void {
    $baseFilter = new QueryFilter(AccessGroup::ACCESS_GROUP_ID, null, '=');
    $filter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [],
      $baseFilter
    );
    $this->assertEquals(
      '(EXISTS (SELECT 1 FROM File WHERE File.accessGroupId=AccessGroup.accessGroupId) OR AccessGroup.accessGroupId IS NULL )',
      $filter->getQueryString(Factory::getAccessGroupFactory())
    );
  }
  
  /**
   * Verify subquery filter and baseFilter are combined.
   * Expected: (EXISTS (... AND File.size>?) OR AccessGroup.accessGroupId IS NULL )
   */
  public function testExistsWithSubqueryFilterAndBaseFilter(): void {
    $subFilter = new QueryFilter(File::SIZE, 100, '>');
    $baseFilter = new QueryFilter(AccessGroup::ACCESS_GROUP_ID, null, '=');
    $filter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [$subFilter],
      $baseFilter
    );
    $this->assertEquals(
      '(EXISTS (SELECT 1 FROM File WHERE File.accessGroupId=AccessGroup.accessGroupId AND File.size>?) OR AccessGroup.accessGroupId IS NULL )',
      $filter->getQueryString(Factory::getAccessGroupFactory())
    );
  }
  
  /**
   * Verify the subquery uses the factory's mapped table name (htp_User).
   * Expected: EXISTS (SELECT 1 FROM htp_User WHERE htp_User.userId=AccessGroupUser.userId)
   */
  public function testExistsWithMappedTable(): void {
    $filter = new ExistsFilter(
      Factory::getUserFactory(),
      User::USER_ID,
      AccessGroupUser::USER_ID
    );
    $this->assertEquals(
      'EXISTS (SELECT 1 FROM htp_User WHERE htp_User.userId=AccessGroupUser.userId)',
      $filter->getQueryString(Factory::getAccessGroupUserFactory())
    );
  }
  
  // --- getValue unit tests ---
  
  /**
   * getValue() returns an empty array when there are no sub-filters.
   */
  public function testGetValueNoFilters(): void {
    $filter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID
    );
    $this->assertSame([], $filter->getValue());
  }
  
  /**
   * getValue() returns the sub-filter's parameter value.
   */
  public function testGetValueWithQueryFilter(): void {
    $subFilter = new QueryFilter(File::SIZE, 100, '>');
    $filter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [$subFilter]
    );
    $this->assertSame([100], $filter->getValue());
  }
  
  /**
   * getValue() flattens array values from a ContainFilter sub-filter.
   */
  public function testGetValueWithContainFilter(): void {
    $subFilter = new ContainFilter(File::ACCESS_GROUP_ID, [1, 2, 3]);
    $filter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [$subFilter]
    );
    $this->assertSame([1, 2, 3], $filter->getValue());
  }
  
  /**
   * getValue() concatenates values from multiple sub-filters.
   */
  public function testGetValueWithMultipleFilters(): void {
    $subFilter1 = new QueryFilter(File::SIZE, 100, '>');
    $subFilter2 = new QueryFilter(File::IS_SECRET, 1, '=');
    $filter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [$subFilter1, $subFilter2]
    );
    $this->assertSame([100, 1], $filter->getValue());
  }
  
  /**
   * getValue() skips sub-filters whose getHasValue() is false (e.g. null-value filters).
   */
  public function testGetValueSkipsValuelessFilters(): void {
    $subFilter1 = new QueryFilter(File::SIZE, 100, '>');
    $subFilter2 = new QueryFilter(File::IS_SECRET, null, '=');
    $filter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [$subFilter1, $subFilter2]
    );
    $this->assertSame([100], $filter->getValue());
  }
  
  /**
   * getHasValue() returns false when there are no sub-filters.
   */
  public function testGetHasValueNoFilters(): void {
    $filter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID
    );
    $this->assertFalse($filter->getHasValue());
  }
  
  /**
   * getHasValue() returns true when a sub-filter has a value.
   */
  public function testGetHasValueTrue(): void {
    $subFilter = new QueryFilter(File::SIZE, 100, '>');
    $filter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [$subFilter]
    );
    $this->assertTrue($filter->getHasValue());
  }
  
  /**
   * getHasValue() returns false when all sub-filters are valueless.
   */
  public function testGetHasValueWithOnlyValuelessFilters(): void {
    $subFilter = new QueryFilter(File::IS_SECRET, null, '=');
    $filter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [$subFilter]
    );
    $this->assertFalse($filter->getHasValue());
  }
  
  /**
   * Integration test: filter AccessGroups by existence of a File, verifying partial joins.
   * Only ag1 (which has a File) should match.
   *
   * @throws Exception
   */
  public function testExistsFilterIntegration(): void {
    $testId = uniqid();
    $ag1 = $this->createAccessGroup('ag1_' . $testId);
    $this->createAccessGroup('ag2_' . $testId);
    $this->createFile($ag1, 0, 'file_' . $testId, 10);
    
    $existsFilter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID
    );
    $qF = new LikeFilter(AccessGroup::GROUP_NAME, '%' . $testId . '%', Factory::getAccessGroupFactory());
    $result = Factory::getAccessGroupFactory()->filter([Factory::FILTER => [$qF, $existsFilter]]);
    
    $this->assertCount(1, $result);
    $this->assertEquals($ag1->getId(), $result[0]->getId());
  }
  
  /**
   * Integration test: NOT EXISTS filter returns AccessGroups without any File.
   * ag1 has a file, ag2 has none. Only ag2 should match.
   *
   * @throws Exception
   */
  public function testNotExistsFilterIntegration(): void {
    $testId = uniqid();
    $ag1 = $this->createAccessGroup('ag1_' . $testId);
    $ag2 = $this->createAccessGroup('ag2_' . $testId);
    $this->createFile($ag1, 0, 'file_' . $testId, 10);
    
    $notExists = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [],
      null,
      true
    );
    $qF = new LikeFilter(AccessGroup::GROUP_NAME, '%' . $testId . '%', Factory::getAccessGroupFactory());
    $result = Factory::getAccessGroupFactory()->filter([Factory::FILTER => [$qF, $notExists]]);
    
    $this->assertCount(1, $result);
    $this->assertEquals($ag2->getId(), $result[0]->getId());
  }
  
  /**
   * Integration test: EXISTS with a sub-filter on File.size.
   * ag1 has a 10-byte file, ag2 has a 100-byte file. Filter for size > 50 should match ag2 only.
   *
   * @throws Exception
   */
  public function testExistsFilterWithSubFilterIntegration(): void {
    $testId = uniqid();
    $ag1 = $this->createAccessGroup('ag1_' . $testId);
    $ag2 = $this->createAccessGroup('ag2_' . $testId);
    $this->createFile($ag1, 0, 'small_' . $testId, 10);
    $this->createFile($ag2, 0, 'large_' . $testId, 100);
    
    $existsFilter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [new QueryFilter(File::SIZE, 50, '>')]
    );
    $qF = new LikeFilter(AccessGroup::GROUP_NAME, '%' . $testId . '%', Factory::getAccessGroupFactory());
    $result = Factory::getAccessGroupFactory()->filter([Factory::FILTER => [$qF, $existsFilter]]);
    
    $this->assertCount(1, $result);
    $this->assertEquals($ag2->getId(), $result[0]->getId());
  }
  
  /**
   * Integration test: EXISTS with multiple sub-filters (size AND isSecret).
   * ag1 has file(size=10, non-secret), ag2 has file(size=100, secret).
   * Filter for size > 50 AND isSecret = 1 should match ag2 only.
   *
   * @throws Exception
   */
  public function testExistsFilterMultipleCriterionIntegration(): void {
    $testId = uniqid();
    $ag1 = $this->createAccessGroup('ag1_' . $testId);
    $ag2 = $this->createAccessGroup('ag2_' . $testId);
    $this->createFile($ag1, 0, 'small_' . $testId, 10);
    $this->createFile($ag2, 1, 'large_' . $testId, 100);
    
    $existsFilter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [new QueryFilter(File::SIZE, 50, '>'), new QueryFilter(File::IS_SECRET, 1, '=')]
    );
    $qF = new LikeFilter(AccessGroup::GROUP_NAME, '%' . $testId . '%', Factory::getAccessGroupFactory());
    $result = Factory::getAccessGroupFactory()->filter([Factory::FILTER => [$qF, $existsFilter]]);
    
    $this->assertCount(1, $result);
    $this->assertEquals($ag2->getId(), $result[0]->getId());
  }
  
  /**
   * Integration test: EXISTS returns empty when no child records exist.
   * Two AccessGroups, no Files at all. Should return no groups.
   *
   * @throws Exception
   */
  public function testExistsFilterEmptyResult(): void {
    $testId = uniqid();
    $this->createAccessGroup('ag1_' . $testId);
    $this->createAccessGroup('ag2_' . $testId);
    
    $existsFilter = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID
    );
    $qF = new LikeFilter(AccessGroup::GROUP_NAME, '%' . $testId . '%', Factory::getAccessGroupFactory());
    $result = Factory::getAccessGroupFactory()->filter([Factory::FILTER => [$qF, $existsFilter]]);
    
    $this->assertCount(0, $result);
  }
  
  /**
   * Integration test: NOT EXISTS returns all rows when no child records exist.
   * Two AccessGroups, no Files at all. Both should match.
   *
   * @throws Exception
   */
  public function testNotExistsFilterReturnsAllWhenNoneHaveFiles(): void {
    $testId = uniqid();
    $this->createAccessGroup('ag1_' . $testId);
    $this->createAccessGroup('ag2_' . $testId);
    
    $notExists = new ExistsFilter(
      Factory::getFileFactory(),
      File::ACCESS_GROUP_ID,
      AccessGroup::ACCESS_GROUP_ID,
      [],
      null,
      true
    );
    $qF = new LikeFilter(AccessGroup::GROUP_NAME, '%' . $testId . '%', Factory::getAccessGroupFactory());
    $result = Factory::getAccessGroupFactory()->filter([Factory::FILTER => [$qF, $notExists]]);
    
    $this->assertCount(2, $result);
  }
  
  /**
   * Integration test: EXISTS across User -> AccessGroupUser constrained to a specific group.
   * createUser() automatically adds every user to the default access group,
   * so the EXISTS subquery must also filter by the test group's accessGroupId
   * to correctly distinguish the linked user from the unlinked one.
   *
   * @throws Exception
   */
  public function testExistsFilterUserBelongsToGroup(): void {
    $testId = uniqid();
    $user1 = $this->createUser('u1_' . $testId);
    $this->createUser('u2_' . $testId);
    $group = $this->createAccessGroup('ag_' . $testId);
    $this->createAccessGroupUser($user1, $group);
    
    $existsFilter = new ExistsFilter(
      Factory::getAccessGroupUserFactory(),
      AccessGroupUser::USER_ID,
      User::USER_ID,
      [new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $group->getId(), '=')]
    );
    $qF = new LikeFilter(User::USERNAME, '%' . $testId . '%', Factory::getUserFactory());
    $result = Factory::getUserFactory()->filter([Factory::FILTER => [$qF, $existsFilter]]);
    
    $this->assertCount(1, $result);
    $this->assertEquals($user1->getId(), $result[0]->getId());
  }
  
  /**
   * Integration test: NOT EXISTS across User -> AccessGroupUser constrained to a specific group.
   * createUser() adds every user to the default access group, so we constrain
   * the NOT EXISTS subquery to the test group to find the unlinked user.
   *
   * @throws Exception
   */
  public function testNotExistsFilterUserWithoutGroup(): void {
    $testId = uniqid();
    $user1 = $this->createUser('u1_' . $testId);
    $user2 = $this->createUser('u2_' . $testId);
    $group = $this->createAccessGroup('ag_' . $testId);
    $this->createAccessGroupUser($user1, $group);
    
    $notExists = new ExistsFilter(
      Factory::getAccessGroupUserFactory(),
      AccessGroupUser::USER_ID,
      User::USER_ID,
      [new QueryFilter(AccessGroupUser::ACCESS_GROUP_ID, $group->getId(), '=')],
      null,
      true
    );
    $qF = new LikeFilter(User::USERNAME, '%' . $testId . '%', Factory::getUserFactory());
    $result = Factory::getUserFactory()->filter([Factory::FILTER => [$qF, $notExists]]);
    
    $this->assertCount(1, $result);
    $this->assertEquals($user2->getId(), $result[0]->getId());
  }
}
