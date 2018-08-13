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
$config = new Config(0, 1, DConfig::AGENT_DATA_LIFETIME, 3600);
$FACTORIES::getConfigFactory()->save($config);
$config = new Config(0, 4, DConfig::AGENT_STAT_TENSION, 0);
$FACTORIES::getConfigFactory()->save($config);
echo "OK\n";

echo "Add file distribution tables... ";
$FACTORIES::getAgentFactory()->getDB()->query("CREATE TABLE `FileDownload` (
  `fileDownloadId` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `fileId` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB");
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `FileDownload` ADD PRIMARY KEY (`fileDownloadId`)");
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `FileDownload` MODIFY `fileDownloadId` int(11) NOT NULL AUTO_INCREMENT");
echo "OK\n";

echo "Update complete!\n";