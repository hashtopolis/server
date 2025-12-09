<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Factory;
use DBA\File;
use DBA\Config;
use DBA\ConfigSection;
use DBA\UpdateSet;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__) . "/../../inc/db.php");
require_once(dirname(__FILE__) . "/../../dba/init.php");
require_once(dirname(__FILE__) . "/../../inc/Util.class.php");
require_once(dirname(__FILE__) . "/../../inc/utils/AccessUtils.class.php");
require_once(dirname(__FILE__) . "/../../inc/defines/config.php");

echo "Apply updates...\n";

echo "Check agent binaries... ";
Util::checkAgentVersionLegacy("python", "0.2.0");
Util::checkAgentVersionLegacy("csharp", "0.52.4");
echo "\n";

echo "Add debug output tables... ";
Factory::getAgentFactory()->getDB()->query("
CREATE TABLE `TaskDebugOutput` (
  `taskDebugOutputId` int(11) NOT NULL,
  `taskId` int(11) NOT NULL,
  `output` varchar(256) NOT NULL
) ENGINE=InnoDB");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `TaskDebugOutput` ADD PRIMARY KEY (`taskDebugOutputId`)");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `TaskDebugOutput` MODIFY `taskDebugOutputId` int(11) NOT NULL AUTO_INCREMENT");
echo "OK\n";

echo "Add file access groups... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `File` ADD `accessGroupId` INT NOT NULL");
$uS = new UpdateSet(File::ACCESS_GROUP_ID, AccessUtils::getOrCreateDefaultAccessGroup()->getId());
Factory::getFileFactory()->massUpdate([Factory::UPDATE => $uS]);
echo "OK\n";

echo "Update agent stats table... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `AgentStat` CHANGE `time` `time` BIGINT(11) NOT NULL");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `AgentStat` CHANGE `value` `value` VARCHAR(64) NOT NULL");
echo "OK\n";

echo "Add new config entries... ";
$config = new Config(null, 4, DConfig::AGENT_STAT_LIMIT, 100);
Factory::getConfigFactory()->save($config);
$config = new Config(null, 1, DConfig::AGENT_DATA_LIFETIME, 3600);
Factory::getConfigFactory()->save($config);
$config = new Config(null, 4, DConfig::AGENT_STAT_TENSION, 0);
Factory::getConfigFactory()->save($config);

$configSection = new ConfigSection(6, 'Multicast');
Factory::getConfigSectionFactory()->save($configSection);
$config = new Config(null, 6, DConfig::MULTICAST_ENABLE, 0);
Factory::getConfigFactory()->save($config);
$config = new Config(null, 6, DConfig::MULTICAST_DEVICE, 'eth0');
Factory::getConfigFactory()->save($config);
$config = new Config(null, 6, DConfig::MULTICAST_TR_ENABLE, 0);
Factory::getConfigFactory()->save($config);
$config = new Config(null, 6, DConfig::MULTICAST_TR, 50000);
Factory::getConfigFactory()->save($config);
echo "OK\n";

echo "Add file distribution tables... ";
Factory::getAgentFactory()->getDB()->query("CREATE TABLE `FileDownload` (
  `fileDownloadId` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `fileId` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `FileDownload` ADD PRIMARY KEY (`fileDownloadId`)");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `FileDownload` MODIFY `fileDownloadId` int(11) NOT NULL AUTO_INCREMENT");
echo "OK\n";

echo "Add task notes... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Task` ADD `notes` TEXT");
echo "OK\n";

echo "Add static chunking to tasks... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Task` ADD `staticChunks` INT NOT NULL, ADD `chunkSize` INT NOT NULL");
echo "OK\n";

echo "Add file deletetion table... ";
Factory::getAgentFactory()->getDB()->query("
CREATE TABLE `FileDelete` (
  `fileDeleteId` int(11) NOT NULL,
  `filename` varchar(256) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `FileDelete` ADD PRIMARY KEY (`fileDeleteId`)");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `FileDelete` MODIFY `fileDeleteId` int(11) NOT NULL AUTO_INCREMENT");
echo "OK\n";

echo "Add force pipe to tasks... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Task` ADD `forcePipe` INT(11)");
echo "OK\n";

echo "Update complete!\n";
