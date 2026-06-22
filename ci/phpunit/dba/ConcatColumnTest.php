<?php

namespace Hashtopolis\dba;

use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\dba\models\User;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

final class ConcatColumnTest extends TestBase {
  /** Verify getValue returns the column key string. */
  public function testReturnsValue(): void {
    $col = new ConcatColumn(Hashlist::HASHLIST_ID, Factory::getHashlistFactory());
    $this->assertEquals('hashlistId', $col->getValue());
  }

  /** Verify getFactory returns the factory passed to the constructor. */
  public function testReturnsFactory(): void {
    $factory = Factory::getHashlistFactory();
    $col = new ConcatColumn(Hashlist::HASHLIST_NAME, $factory);
    $this->assertSame($factory, $col->getFactory());
  }

  /** Verify getValue returns null when null is passed as the column key. */
  public function testNullValue(): void {
    $col = new ConcatColumn(null, Factory::getHashlistFactory());
    $this->assertNull($col->getValue());
  }

  /** Verify getValue returns the correct column key for a mapped-table factory (User). */
  public function testUserColumn(): void {
    $col = new ConcatColumn(User::USERNAME, Factory::getUserFactory());
    $this->assertEquals('username', $col->getValue());
  }

  /** Verify getFactory returns an AbstractModelFactory instance. */
  public function testFactoryIsAbstractModelFactory(): void {
    $col = new ConcatColumn(Hashlist::HASH_TYPE_ID, Factory::getHashlistFactory());
    $this->assertInstanceOf(AbstractModelFactory::class, $col->getFactory());
  }
}
