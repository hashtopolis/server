<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Factory;

if (!isset($TEST)) {
  /** @noinspection PhpIncludeInspection */
  require_once(dirname(__FILE__) . "/../../inc/conf.php");
  require_once(dirname(__FILE__) . "/../../inc/info.php");
  require_once(dirname(__FILE__) . "/../../dba/init.php");
  require_once(dirname(__FILE__) . "/../../inc/Util.class.php");
}
require_once(dirname(__FILE__) . "/../../inc/defines/config.php");
require_once(dirname(__FILE__) . "/../../inc/defines/log.php");


if (!isset($PRESENT["v0.13.x_hash_length"])) {
  $conn = Factory::getAgentFactory()->getDB();

  $hash_column_length = $conn->query("SELECT hash FROM Hash")->getColumnMeta(0)['len'];
  $zap_column_length = $conn->query("SELECT hash FROM Zap")->getColumnMeta(0)['len'];
  // TEXT == 65535
  if ($hash_column_length <= 65535) {
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Hash` MODIFY `hash` MEDIUMTEXT NOT NULL;");
  }
  if ($zap_column_length <= 65535) {
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Zap` MODIFY `hash` MEDIUMTEXT NOT NULL;");
  }
  $EXECUTED["v0.13.x_hash_length"] = true;
}
