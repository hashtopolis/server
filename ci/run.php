<?php

/**
 * This is the entry point to run the full environment
 */

$CONN = [
  "db" => "hashtopolis",
  "server" => "localhost",
  "user" => "root",
  "pass" => "root",
  "port" => 3306
];
require_once(dirname(__FILE__)."/env/src/dba/init.php");
require_once(dirname(__FILE__)."/../src/inc/Util.class.php");
require_once(dirname(__FILE__)."/../src/inc/utils/AccessUtils.class.php");
require_once(dirname(__FILE__)."/HashtopolisTest.class.php");
require_once(dirname(__FILE__)."/HashtopolisTestFramework.class.php");

$dir = scandir(dirname(__FILE__) . "/tests/");
foreach ($dir as $entry) {
  if (strpos($entry, ".php") !== false) {
    require_once(dirname(__FILE__) . "/tests/" . $entry);
  }
}

if(sizeof($argv) != 2){
  die("Invalid number of arguments!\nphp -f run.php <version>\n");
}
$version = $argv[1];

$framework = new HashtopolisTestFramework();
$framework->execute($version, HashtopolisTest::RUN_FULL);