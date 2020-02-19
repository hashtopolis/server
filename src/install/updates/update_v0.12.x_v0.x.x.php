<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Config;
use DBA\Factory;
use DBA\QueryFilter;

if (!isset($TEST)) {
  /** @noinspection PhpIncludeInspection */
  require_once(dirname(__FILE__) . "/../../inc/conf.php");
  require_once(dirname(__FILE__) . "/../../inc/info.php");
  require_once(dirname(__FILE__) . "/../../dba/init.php");
  require_once(dirname(__FILE__) . "/../../inc/Util.class.php");
}
require_once(dirname(__FILE__) . "/../../inc/defines/config.php");
require_once(dirname(__FILE__) . "/../../inc/defines/log.php");

if (!isset($PRESENT["v0.12.x_conf1"])) {
  $qF = new QueryFilter(Config::ITEM, DConfig::ENABLE_DEVELOPMENT, "=");
  $item = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
  if (!$item) {
    $config = new Config(null, 5, DConfig::ENABLE_DEVELOPMENT, '0');
    Factory::getConfigFactory()->save($config);
  }
  $EXECUTED["v0.12.x_conf1"] = true;
}

