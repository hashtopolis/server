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
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Hash` MODIFY `hash` MEDIUMTEXT NOT NULL;");
  $EXECUTED["v0.13.x_hash_length"] = true;
}

