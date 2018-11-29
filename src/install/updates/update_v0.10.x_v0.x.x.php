<?php

use DBA\Config;
use DBA\Factory;

if (!isset($TEST)) {
  require_once(dirname(__FILE__) . "/../../inc/conf.php");
  require_once(dirname(__FILE__) . "/../../inc/info.php");
  require_once(dirname(__FILE__) . "/../../dba/init.php");
  require_once(dirname(__FILE__) . "/../../inc/Util.class.php");
}
require_once(dirname(__FILE__) . "/../../inc/defines/config.php");
require_once(dirname(__FILE__) . "/../../inc/defines/log.php");

if(!isset($PRESENT["v0.10.x_conf1"])){
  $config = new Config(null, 4, DConfig::AGENT_TEMP_THRESHOLD_1, '70');
  Factory::getConfigFactory()->save($config);
  $config = new Config(null, 4, DConfig::AGENT_TEMP_THRESHOLD_2, '80');
  Factory::getConfigFactory()->save($config);
  $EXECUTED["v0.10.x_conf1"] = true;
}