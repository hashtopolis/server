<?php /** @noinspection SqlNoDataSourceInspection */

if (!isset($TEST)) {
  if (file_exists(dirname(__FILE__) . "/../../inc/conf.php")) {
    require_once(dirname(__FILE__) . "/../../inc/conf.php");
  }
  require_once(dirname(__FILE__) . "/../../inc/info.php");
  require_once(dirname(__FILE__) . "/../../dba/init.php");
  require_once(dirname(__FILE__) . "/../../inc/Util.class.php");
}
require_once(dirname(__FILE__) . "/../../inc/defines/config.php");
require_once(dirname(__FILE__) . "/../../inc/defines/log.php");


