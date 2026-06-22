<?php

namespace Hashtopolis\dba;

use Exception;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\models\User;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class ContainFilterTest extends TestBase {
  public function testQueryStringSingleValue(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1]);
    $this->assertEquals(
      'hashlistId IN (?)',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }

  public function testQueryStringMultipleValues(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1, 2, 3]);
    $this->assertEquals(
      'hashlistId IN (?,?,?)',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }

  public function testQueryStringWithTable(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1]);
    $this->assertEquals(
      'Hashlist.hashlistId IN (?)',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }

  public function testQueryStringNotIn(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1, 2], null, true);
    $this->assertEquals(
      'hashlistId NOT IN (?,?)',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }

  public function testQueryStringNotInWithTable(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1, 2], null, true);
    $this->assertEquals(
      'Hashlist.hashlistId NOT IN (?,?)',
      $filter->getQueryString(Factory::getHashlistFactory(), true)
    );
  }

  public function testQueryStringEmptyValues(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, []);
    $this->assertEquals(
      'FALSE',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }

  public function testQueryStringEmptyValuesInverse(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [], null, true);
    $this->assertEquals(
      'TRUE',
      $filter->getQueryString(Factory::getHashlistFactory())
    );
  }

  public function testQueryStringMappedTable(): void {
    $filter = new ContainFilter(User::USER_ID, [1]);
    $this->assertEquals(
      'htp_User.userId IN (?)',
      $filter->getQueryString(Factory::getUserFactory(), true)
    );
  }

  public function testQueryStringOverrideFactory(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1], Factory::getHashlistFactory());
    $this->assertEquals(
      'hashlistId IN (?)',
      $filter->getQueryString(Factory::getUserFactory())
    );
  }

  public function testQueryStringMappedColumn(): void {
    $filter = new ContainFilter(HealthCheckAgent::END, [1, 2]);
    $this->assertEquals(
      'htp_end IN (?,?)',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory())
    );
  }

  public function testQueryStringMappedColumnWithTable(): void {
    $filter = new ContainFilter(HealthCheckAgent::END, [1]);
    $this->assertEquals(
      'HealthCheckAgent.htp_end IN (?)',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory(), true)
    );
  }

  public function testQueryStringMappedColumnNotIn(): void {
    $filter = new ContainFilter(HealthCheckAgent::END, [1], null, true);
    $this->assertEquals(
      'htp_end NOT IN (?)',
      $filter->getQueryString(Factory::getHealthCheckAgentFactory())
    );
  }

  public function testGetValue(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1, 2, 3]);
    $this->assertEquals([1, 2, 3], $filter->getValue());
  }

  public function testGetHasValue(): void {
    $filter = new ContainFilter(Hashlist::HASHLIST_ID, [1]);
    $this->assertTrue($filter->getHasValue());
  }
  
  /**
   * @throws Exception
   */
  public function testFilterIn(): void {
    $testid = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testid, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2' . $testid, 5, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3' . $testid, 10, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht4' . $testid, 20, 0));

    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testid);
    $cF = new ContainFilter(HashType::IS_SALTED, [1, 10]);
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $cF]]);

    $this->assertCount(2, $result);
    foreach ($result as $ht) {
      $this->assertContains($ht->getIsSalted(), [1, 10]);
    }
  }
  
  /**
   * @throws Exception
   */
  public function testFilterNotIn(): void {
    $testid = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testid, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2' . $testid, 5, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3' . $testid, 10, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht4' . $testid, 20, 0));

    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testid);
    $cF = new ContainFilter(HashType::IS_SALTED, [1, 10], null, true);
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $cF]]);

    $this->assertCount(2, $result);
    foreach ($result as $ht) {
      $this->assertContains($ht->getIsSalted(), [5, 20]);
    }
  }
  
  /**
   * @throws Exception
   */
  public function testFilterEmptyValues(): void {
    $testid = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testid, 1, 0));

    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testid);
    $cF = new ContainFilter(HashType::IS_SALTED, []);
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $cF]]);

    $this->assertCount(0, $result);
  }
  
  /**
   * @throws Exception
   */
  public function testFilterEmptyValuesInverse(): void {
    $testid = uniqid();
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testid, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2' . $testid, 5, 0));

    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testid);
    $cF = new ContainFilter(HashType::IS_SALTED, [], null, true);
    $result = Factory::getHashTypeFactory()->filter([Factory::FILTER => [$lF, $cF]]);

    $this->assertCount(2, $result);
  }
  
  /**
   * @throws Exception
   */
  public function testFilterInWithColumnFilter(): void {
    $testid = uniqid();
    $ht1 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht1' . $testid, 1, 0));
    $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht2' . $testid, 5, 0));
    $ht3 = $this->createDatabaseObject(Factory::getHashTypeFactory(), new HashType(null, 'ht3' . $testid, 10, 0));

    $lF = new LikeFilter(HashType::DESCRIPTION, '%' . $testid);
    $cF = new ContainFilter(HashType::IS_SALTED, [1, 10]);
    $ids = Factory::getHashTypeFactory()->columnFilter([Factory::FILTER => [$lF, $cF]], HashType::HASH_TYPE_ID);

    $this->assertCount(2, $ids);
    $this->assertEqualsCanonicalizing([$ht1->getId(), $ht3->getId()], $ids);
  }
}
