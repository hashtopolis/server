<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\inc\StartupConfig;
use Hashtopolis\TestBase;

require_once(dirname(__FILE__) . '/../../TestBase.php');

/**
 * Tests for MigrationUtils.
 *
 * `getAllGenerations` scans migration directories — this validates the
 * directory structure and file naming conventions.
 *
 * `getMigrationStartEntry` reads config.json files — these tests verify
 * the JSON format is valid and returns null for nonexistent generations.
 *
 * `runDatabaseMigration` is excluded from unit tests: it shells out to the
 * `sqlx` binary and calls exit(-1) on failure.  That path is covered by the
 * integration upgrade workflow (`.github/workflows/upgrade-test.yml`).
 */
final class MigrationUtilsTest extends TestBase {

  public function testGetAllGenerationsMysqlHasExpectedGenerations(): void {
    $result = MigrationUtils::getAllGenerations('mysql');
    $this->assertArrayHasKey(0, $result);
    $this->assertArrayHasKey(1, $result);
    $this->assertStringEndsWith('.sql', $result[0][0]);
  }

  public function testGetAllGenerationsPostgresHasExpectedGenerations(): void {
    $result = MigrationUtils::getAllGenerations('postgres');
    $this->assertArrayHasKey(0, $result);
    $this->assertArrayHasKey(1, $result);
    $this->assertStringEndsWith('.sql', $result[0][0]);
  }

  public function testGetAllGenerationsUnknownTypeReturnsEmptyArray(): void {
    $result = MigrationUtils::getAllGenerations('nonexistent');
    $this->assertSame([], $result);
  }

  public function testGetMigrationStartEntryGeneration0ReturnsModel(): void {
    putenv('HASHTOPOLIS_DB_TYPE=mysql');
    StartupConfig::getInstance(true);
    $result = MigrationUtils::getMigrationStartEntry(0);
    $this->assertNotNull($result);
    $dict = $result->getKeyValueDict();
    $this->assertArrayHasKey('version', $dict);
    $this->assertArrayHasKey('checksum', $dict);
  }

  public function testGetMigrationStartEntryGeneration1ReturnsModel(): void {
    putenv('HASHTOPOLIS_DB_TYPE=mysql');
    StartupConfig::getInstance(true);
    $result = MigrationUtils::getMigrationStartEntry(1);
    $this->assertNotNull($result);
  }

  public function testGetMigrationStartEntryUnknownGenerationReturnsNull(): void {
    putenv('HASHTOPOLIS_DB_TYPE=mysql');
    StartupConfig::getInstance(true);
    $result = MigrationUtils::getMigrationStartEntry(999);
    $this->assertNull($result);
  }
}
