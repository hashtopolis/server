<?php

namespace Hashtopolis\dba;

use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Nested transaction handling of the database connection. The atomic operations
 * endpoint groups calls that open transactions of their own into one unit of
 * work, so the outermost transaction has to stay in control of what is
 * persisted.
 *
 * Exercised against SQLite in memory: savepoints are standard SQL and behave
 * the same on the MySQL and PostgreSQL connections used in production.
 */
final class NestedTransactionPDOTest extends TestCase {
  private NestedTransactionPDO $db;

  protected function setUp(): void {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
      $this->markTestSkipped('pdo_sqlite is not available');
    }

    $this->db = new NestedTransactionPDO('sqlite::memory:');
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->db->exec('CREATE TABLE names (name TEXT)');
  }

  private function insert(string $name): void {
    $this->db->exec("INSERT INTO names (name) VALUES ('$name')");
  }

  /** @return list<string> */
  private function names(): array {
    return $this->db->query('SELECT name FROM names ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
  }

  public function testCommittingTheOutermostTransactionPersistsNestedWork(): void {
    $this->db->beginTransaction();
    $this->insert('outer');

    $this->db->beginTransaction();
    $this->assertSame(2, $this->db->getTransactionDepth());
    $this->insert('inner');
    $this->db->commit();

    $this->assertSame(1, $this->db->getTransactionDepth());
    $this->db->commit();

    $this->assertSame(0, $this->db->getTransactionDepth());
    $this->assertFalse($this->db->inTransaction());
    $this->assertSame(['inner', 'outer'], $this->names());
  }

  /**
   * This is what the atomic operations endpoint relies on: an inner block that
   * committed is undone as well when the request as a whole fails.
   */
  public function testRollingBackTheOutermostTransactionUndoesCommittedNestedWork(): void {
    $this->db->beginTransaction();
    $this->insert('outer');

    $this->db->beginTransaction();
    $this->insert('inner');
    $this->db->commit();

    $this->db->rollBack();

    $this->assertSame(0, $this->db->getTransactionDepth());
    $this->assertSame([], $this->names());
  }

  public function testRollingBackANestedTransactionKeepsTheEnclosingOne(): void {
    $this->db->beginTransaction();
    $this->insert('outer');

    $this->db->beginTransaction();
    $this->insert('inner');
    $this->db->rollBack();

    $this->assertSame(1, $this->db->getTransactionDepth());
    $this->assertTrue($this->db->inTransaction());

    $this->insert('after');
    $this->db->commit();

    $this->assertSame(['after', 'outer'], $this->names());
  }

  public function testASingleTransactionBehavesLikePlainPdo(): void {
    $this->db->beginTransaction();
    $this->assertSame(1, $this->db->getTransactionDepth());
    $this->insert('rolled back');
    $this->db->rollBack();

    $this->assertSame([], $this->names());

    $this->db->beginTransaction();
    $this->insert('committed');
    $this->db->commit();

    $this->assertSame(['committed'], $this->names());
  }
}
