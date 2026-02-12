<?php

// set to 1 for debugging
ini_set("display_errors", "0");

define("APP_NAME", "Hashtopolis");

$baseDir = dirname(__FILE__) . "/..";

require_once($baseDir . "/../../vendor/autoload.php");

require_once($baseDir . "/info.php");

require_once($baseDir . "/confv2.php");

// include all .class.php files in inc dir
$dir = scandir($baseDir);
foreach ($dir as $entry) {
  if (strpos($entry, ".class.php") !== false) {
    require_once($baseDir . "/" . $entry);
  }
}
require_once($baseDir . "/templating/Statement.class.php");
require_once($baseDir . "/templating/Template.class.php");

// include all required files
require_once($baseDir . "/handlers/Handler.class.php");
require_once($baseDir . "/notifications/Notification.class.php");
require_once($baseDir . "/api/APIBasic.class.php");
require_once($baseDir . "/user-api/UserAPIBasic.class.php");
require_once($baseDir . "/apiv2/common/ErrorHandler.class.php");
$directories = array('handlers', 'api', 'defines', 'utils', 'notifications', 'user-api');
foreach ($directories as $directory) {
  $dir = scandir($baseDir . "/$directory/");
  foreach ($dir as $entry) {
    if (strpos($entry, ".php") !== false) {
      require_once($baseDir . "/$directory/" . $entry);
    }
  }
}

require_once($baseDir . "/protocol.php");

require_once($baseDir . "/mask.php");

// include DBA
require_once($baseDir . "/../dba/init.php");

// legacy, but needed for email sending
// TODO: this later should be replaced with a singleton
$LANG = new Lang();
