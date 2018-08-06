<?php

use DBA\Factory;
use DBA\Config;

require_once(dirname(__FILE__) . "/../../inc/db.php");
require_once(dirname(__FILE__) . "/../../dba/init.php");
require_once(dirname(__FILE__) . "/../../inc/Util.class.php");
require_once(dirname(__FILE__) . "/../../inc/defines/config.php");

$FACTORIES = new Factory();

echo "Apply updates...\n";

echo "Check agent binaries... ";
Util::checkAgentVersion("python", "0.1.6");
Util::checkAgentVersion("csharp", "0.52.4");
echo "\n";

echo "Update agent stats table... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `AgentStat` CHANGE `time` `time` BIGINT(11) NOT NULL");
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `AgentStat` CHANGE `value` `value` VARCHAR(64) NOT NULL");
echo "OK\n";

echo "Add new config entries... ";
$config = new Config(0, 4, DConfig::AGENT_STAT_LIMIT, 100);
$FACTORIES::getConfigFactory()->save($config);
echo "OK\n";

echo "Update complete!\n";