<?php

/**
 * This is the entry point to run the full environment
 */

$CONN = [
  "db" => "hashtopolis",
  "server" => "localhost",
  "user" => "root",
  "pass" => "",
  "port" => 3306
];
require_once("/var/www/html/hashtopolis/src/dba/init.php");
require_once("/var/www/html/hashtopolis/src/inc/defines/config.php");
require_once("/var/www/html/hashtopolis/src/inc/info.php");
require_once(dirname(__FILE__) . "/../src/inc/Util.class.php");
require_once(dirname(__FILE__) . "/../src/inc/Encryption.class.php");
require_once(dirname(__FILE__) . "/../src/inc/utils/AccessUtils.class.php");
require_once(dirname(__FILE__) . "/HashtopolisTest.class.php");
require_once(dirname(__FILE__) . "/HashtopolisTestFramework.class.php");

$dir = scandir(dirname(__FILE__) . "/tests/");
foreach ($dir as $entry) {
  if (strpos($entry, ".php") !== false) {
    require_once(dirname(__FILE__) . "/tests/" . $entry);
  }
}

$TEST = true;

if (sizeof($argv) < 2) {
  die("Invalid number of arguments!\nphp -f run.php <version> [upgrade]\n");
}
$version = $argv[1];
HashtopolisTestFramework::$logLevel = HashtopolisTestFramework::LOG_DEBUG;

$framework = new HashtopolisTestFramework();
if (isset($argv[2]) && $argv[2] != 'master') {
  $returnStatus = $framework->executeWithUpgrade($argv[2], HashtopolisTest::RUN_FULL);
}
else {
  $returnStatus = $framework->execute($version, HashtopolisTest::RUN_FULL);
}

HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, HashtopolisTest::getTestCount() . " tests executed");

exit($returnStatus);