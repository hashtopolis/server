<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\models\_sqlx_migrations;
use Hashtopolis\inc\StartupConfig;

class MigrationUtils {
  /**
   * Run the migration on one generation (default is actual generation which is 0).
   *
   * @param int $generation
   * @return void
   */
  public static function runDatabaseMigration(int $generation = 0): void {
    $generationPath = "";
    if ($generation > 0) {
      $generationPath = ".$generation";
    }
    
    $output = [];
    $database_uri = StartupConfig::getInstance()->getDatabaseType() . "://" . rawurlencode(StartupConfig::getInstance()->getDatabaseUser()) . ":" . rawurlencode(StartupConfig::getInstance()->getDatabasePassword()) . "@" . StartupConfig::getInstance()->getDatabaseServer() . ":" . StartupConfig::getInstance()->getDatabasePort() . "/" . StartupConfig::getInstance()->getDatabaseDB();
    exec('/usr/bin/sqlx migrate run --source ' . escapeshellarg(dirname(__FILE__) . '/../../migrations/' . StartupConfig::getInstance()->getDatabaseType() . $generationPath . '/') . ' -D ' . escapeshellarg($database_uri), $output, $retval);
    if ($retval !== 0) {
      echo "Failed to run migrations: \n" . implode("\n", $output);
      exit(-1);
    }
  }
  
  /**
   * Get an array with all existing generations and their migrations steps existing.
   *
   * @param string $databaseType
   * @return array the generation count is the key and the value is the list of all migrations ordered ascending
   */
  public static function getAllGenerations(string $databaseType): array {
    $generations = [];
    $basePath = __DIR__ . "/../../migrations/" . $databaseType;
    $current = $basePath;
    $count = 0;
    while (is_dir($current)) {
      // scanning ascending should work as all are prefixed with the timestamp
      $migrations = scandir($current);
      $generations[$count] = [];
      foreach ($migrations as $migration) {
        if (str_contains($migration, "_")) {
          $generations[$count][] = $migration;
        }
      }
      $count++;
      $current = $basePath . ".$count";
    }
    return $generations;
  }
  
  /**
   * Get the first migration entry of a given generation. This is needed to start with a new initial migration on an
   * already existing database from an earlier migration.
   *
   * @param int $generation
   * @return _sqlx_migrations|null
   */
  public static function getMigrationStartEntry(int $generation): ?_sqlx_migrations {
    $generationPath = "";
    if ($generation > 0) {
      $generationPath = ".$generation";
    }
    $configPath = dirname(__FILE__) . '/../../migrations/' . StartupConfig::getInstance()->getDatabaseType() . $generationPath . '/config.json';
    if (!file_exists($configPath)) {
      return null;
    }
    $data = json_decode(file_get_contents($configPath), true);
    return new _sqlx_migrations($data['version'], $data['description'], $data['installed_on'], 1, $data['checksum'], 1);
  }
}