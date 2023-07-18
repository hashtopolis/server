<?php

/**
 * This is the entry point to run the full environment
 */

require_once(dirname(__FILE__) . "/../src/inc/conf.php");
require_once(dirname(__FILE__) . "/../src/dba/init.php");
require_once(dirname(__FILE__) . "/../src/inc/defines/config.php");
require_once(dirname(__FILE__) . "/../src/inc/info.php");
require_once(dirname(__FILE__) . "/../src/inc/Util.class.php");
require_once(dirname(__FILE__) . "/../src/inc/Encryption.class.php");
require_once(dirname(__FILE__) . "/../src/inc/utils/AccessUtils.class.php");
require_once(dirname(__FILE__) . "/../src/inc/utils/BenchmarkUtils.class.php");
require_once(dirname(__FILE__) . "/../src/inc/utils/HardwareGroupUtils.class.php");
require_once(dirname(__FILE__) . "/../src/inc/SConfig.class.php");
require_once(dirname(__FILE__) . "/../src/inc/Dataset.class.php");

require_once(dirname(__FILE__) . "/HashtopolisTest.class.php");
require_once(dirname(__FILE__) . "/HashtopolisTestFramework.class.php");

$dir = scandir(dirname(__FILE__) . "/tests/");
foreach ($dir as $entry) {
  if (strpos($entry, ".php") !== false) {
    require_once(dirname(__FILE__) . "/tests/" . $entry);
  }
}
$dir = scandir(dirname(__FILE__) . "/tests/integration/");
foreach ($dir as $entry) {
  if (strpos($entry, ".php") !== false) {
    require_once(dirname(__FILE__) . "/tests/integration/" . $entry);
  }
}

$TEST = true;

// determine the version, upgrade and which tests to run
$options = getopt("v:t::u::");

if (empty($options["v"])) {
  HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "No version specified! \nphp -f run.php -v <version> [-t <tests> ] [-u <upgrade> ]");
  die();
}

$version = $options["v"];

if (empty($options["t"])) {
  HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running all tests");
  $testNames = [];
}
else {
  $testNames = explode(",", $options["t"]);
}

if (empty($options["u"])) {
  HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "No upgrade selected");
}
else {
  $upgrade = $options["u"];
}

HashtopolisTestFramework::$logLevel = HashtopolisTestFramework::LOG_DEBUG;

$framework = new HashtopolisTestFramework();
if (isset($upgrade) && $upgrade != 'master') {
  $returnStatus = $framework->executeWithUpgrade($upgrade, $testNames, HashtopolisTest::RUN_FULL);
}
else {
  $returnStatus = $framework->execute($version, $testNames, HashtopolisTest::RUN_FULL);
}

HashtopolisTestFramework::reportTestSummary();

exit($returnStatus);