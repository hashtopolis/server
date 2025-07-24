<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Config;
use DBA\Factory;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/../../inc/defines/config.php");

if (!isset($PRESENT["v0.14.x_pagination"])) {
  $qF = new QueryFilter(Config::ITEM, DConfig::DEFAULT_PAGE_SIZE, "=");
  $item = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
  if (!$item) {
    $config = new Config(null, 3, DConfig::DEFAULT_PAGE_SIZE, '10000');
    Factory::getConfigFactory()->save($config);
  }
  $qF = new QueryFilter(Config::ITEM, DConfig::MAX_PAGE_SIZE, "=");
  $item = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
  if (!$item) {
    $config = new Config(null, 3, DConfig::MAX_PAGE_SIZE, '50000');
    Factory::getConfigFactory()->save($config);
  }
  $EXECUTED["v0.14.x_pagination"] = true;
}