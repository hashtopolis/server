<?php

require_once __DIR__ . '/../../../../src/inc/conf.php';
require_once __DIR__ . '/../../../../src/dba/init.php';
require_once __DIR__ . '/../../../../src/inc/defines/config.php';
require_once __DIR__ . '/../../../../src/inc/info.php';
require_once __DIR__ . '/../../../../src/inc/Util.class.php';
require_once __DIR__ . '/../../../../src/inc/Encryption.class.php';
require_once __DIR__ . '/../../../../src/inc/utils/AccessUtils.class.php';
require_once __DIR__ . '/../../../HashtopolisTest.class.php';
require_once __DIR__ . '/../../../HashtopolisTestFramework.class.php';

use DBA\Factory;

// TODO: refactor to clean implementation when kept!
class FeatureContext implements Behat\Behat\Context\Context {

  private static $dbBackupFile;

  /**
   * @BeforeSuite
   */
  public static function backupDatabase() {
    self::backup();
  }

  /**
   * @BeforeFeature @requiresDB
   */
  public static function setupTestDatabase() {
    // TODO: hack, should be implemented in clean way!
    $test = new DummyTestForInitialization();
    $test->init('master');
  }

  /**
   * @AfterSuite
   */
  public static function restoreDatabase() {
    self::restore();
  }

  private static function backup() {
    global $CONN;

    if (!file_exists(__DIR__ . '/../../../../ci/db-backups')) {
      mkdir(__DIR__ . '/../../../../ci/db-backups');
    }

    self::$dbBackupFile = __DIR__ . '/../../../../ci/db-backups/database_backup_' . date('Y_m_d-H_i_s').  '.sql';

    // Note that the '-y' option avoids requirement on 'PROCESS' privilege for the 'hashtopolis' user!
    exec('mysqldump hashtopolis -y -h'.$CONN['server'] . ' -P'.$CONN['port'] . ' -u'.$CONN['user'] . ' -p'.$CONN['pass'] .' > ' . self::$dbBackupFile, $output, $status);
    if ($status != 0) {
      self::$dbBackupFile = '';
      die('test aborted!\n');
    }
  }

  private static function restore() {
    if (!empty(self::$dbBackupFile)) {

      // drop old data
      Factory::getAgentFactory()->getDB()->query('DROP DATABASE IF EXISTS hashtopolis');
      Factory::getAgentFactory()->getDB()->query('CREATE DATABASE hashtopolis');
      Factory::getAgentFactory()->getDB()->query('USE hashtopolis');

      // restore original DB
      Factory::getAgentFactory()->getDB()->query(file_get_contents(self::$dbBackupFile));
    }
  }
}

class DummyTestForInitialization extends HashtopolisTest {
  function run() {}
  function getTestName() {}
}