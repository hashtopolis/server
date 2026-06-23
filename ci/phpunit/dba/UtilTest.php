<?php

namespace Hashtopolis\dba;

use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../TestBase.php');

class UtilTestCastSource {
  public string $a = 'hello';
  public int    $b = 42;
  public array  $c = [1, 2, 3];
}

class UtilTestCastTarget {
  public string $a = '';
  public int    $b = 0;
  public array  $c = [];
}

final class UtilTest extends TestBase {
  /** Verify cast returns null when given null. */
  public function testCastNullReturnsNull(): void {
    $this->assertNull(Util::cast(null, 'SomeClass'));
  }
  
  /** Verify cast returns null when target class does not exist. */
  public function testCastNonExistentClassReturnsNull(): void {
    $obj = new UtilTestCastSource();
    $this->assertNull(Util::cast($obj, 'DoesNotExist\\FakeClass'));
  }
  
  /** Verify cast to the same class produces an equivalent object. */
  public function testCastToSameClass(): void {
    $obj = new UtilTestCastSource();
    $result = Util::cast($obj, UtilTestCastSource::class);
    $this->assertInstanceOf(UtilTestCastSource::class, $result);
    $this->assertEquals('hello', $result->a);
    $this->assertEquals(42, $result->b);
    $this->assertEquals([1, 2, 3], $result->c);
  }
  
  /** Verify cast transfers all public properties to the target class. */
  public function testCastToDifferentClass(): void {
    $obj = new UtilTestCastSource();
    $result = Util::cast($obj, UtilTestCastTarget::class);
    $this->assertInstanceOf(UtilTestCastTarget::class, $result);
    $this->assertEquals('hello', $result->a);
    $this->assertEquals(42, $result->b);
    $this->assertEquals([1, 2, 3], $result->c);
  }
  
  /** Verify cast preserves null property values. */
  public function testCastWithNullProperty(): void {
    $src = new UtilTestCastSource();
    $src->a = '';
    $src->b = 0;
    $src->c = [];
    $result = Util::cast($src, UtilTestCastTarget::class);
    $this->assertInstanceOf(UtilTestCastTarget::class, $result);
    $this->assertSame('', $result->a);
    $this->assertSame(0, $result->b);
    $this->assertSame([], $result->c);
  }
  
  /** Verify cast works with stdClass input. */
  public function testCastStdClass(): void {
    $obj = new \stdClass();
    $obj->a = 'foo';
    $obj->b = 99;
    $result = Util::cast($obj, UtilTestCastTarget::class);
    $this->assertInstanceOf(UtilTestCastTarget::class, $result);
    $this->assertEquals('foo', $result->a);
    $this->assertEquals(99, $result->b);
  }
  
  /** Verify createPrefixedString returns correct format for multiple keys. */
  public function testCreatePrefixedStringBasic(): void {
    $result = Util::createPrefixedString('hashlist', ['hashlist_id', 'name']);
    $this->assertEquals('hashlist.hashlist_id AS hashlist_hashlist_id, hashlist.name AS hashlist_name', $result);
  }
  
  /** Verify createPrefixedString handles a single key. */
  public function testCreatePrefixedStringSingleKey(): void {
    $result = Util::createPrefixedString('t', ['id']);
    $this->assertEquals('t.id AS t_id', $result);
  }
  
  /** Verify createPrefixedString returns empty string for empty keys. */
  public function testCreatePrefixedStringEmptyKeys(): void {
    $result = Util::createPrefixedString('t', []);
    $this->assertEquals('', $result);
  }
  
  /** Verify createPrefixedString handles table names with underscores. */
  public function testCreatePrefixedStringWithUnderscoreTable(): void {
    $result = Util::createPrefixedString('my_table', ['col_a', 'col_b']);
    $this->assertEquals('my_table.col_a AS my_table_col_a, my_table.col_b AS my_table_col_b', $result);
  }
}
