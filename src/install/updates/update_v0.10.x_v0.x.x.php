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

if(!isset($PRESENT["v0.10.x_conf2"])){
  $config = new Config(null, 4, DConfig::AGENT_UTIL_THRESHOLD_1, '90');
  Factory::getConfigFactory()->save($config);
  $config = new Config(null, 4, DConfig::AGENT_UTIL_THRESHOLD_2, '75');
  Factory::getConfigFactory()->save($config);
  $EXECUTED["v0.10.x_conf2"] = true;
}

if(!isset($PRESENT["v0.10.x_agentBinaries"])){
  Util::checkAgentVersion("python", "0.5.0", true);
  $EXECUTED["v0.10.x_agentBinaries"] = true;
}