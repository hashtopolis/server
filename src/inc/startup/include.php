<?php

// set to 1 for debugging
use Hashtopolis\inc\Lang;

ini_set("display_errors", "0");

define("APP_NAME", "Hashtopolis");

/* Set this to true to restrict access to API keys to their individual owners.
 * This will also deny administrators permission to re-assign API key owners.
 */
define("MASK_API_KEYS", true);

// TODO: all the includes should not be required anymore

$baseDir = dirname(__FILE__) . "/..";

require_once($baseDir . "/StartupConfig.class.php");

require_once($baseDir . "/../../vendor/autoload.php");

// include all .class.php files in inc dir
$dir = scandir($baseDir);
foreach ($dir as $entry) {
  if (str_contains($entry, ".class.php")) {
    require_once($baseDir . "/" . $entry);
  }
}
require_once($baseDir . "/templating/Statement.class.php");
require_once($baseDir . "/templating/Template.class.php");

// include all required files
require_once($baseDir . "/handlers/Handler.class.php");
require_once($baseDir . "/notifications/Notification.class.php");
require_once($baseDir . "/api/APIBasic.class.php");
require_once($baseDir . "/user_api/UserAPIBasic.class.php");
$directories = array('handlers', 'api', 'defines', 'utils', 'notifications', 'user-api');
foreach ($directories as $directory) {
  $dir = scandir($baseDir . "/$directory/");
  foreach ($dir as $entry) {
    if (str_contains($entry, ".php")) {
      require_once($baseDir . "/$directory/" . $entry);
    }
  }
}

// legacy, but needed for email sending
$LANG = new Lang();
