<?php
use DBA\Config;
use DBA\Factory;

if (!isset($TEST)) {
  require_once(dirname(__FILE__) . "/../../inc/conf.php");
  require_once(dirname(__FILE__) . "/../../dba/init.php");
  require_once(dirname(__FILE__) . "/../../inc/Util.class.php");
}
require_once(dirname(__FILE__) . "/../../inc/defines/config.php");
require_once(dirname(__FILE__) . "/../../inc/defines/log.php");

echo "Apply updates...\n";

echo "Check agent binaries... ";
Util::checkAgentVersion("python", "0.3.0");
Util::checkAgentVersion("csharp", "0.52.4");
echo "\n";

echo "Adding new config values... ";
$config = new Config(null, 1, DConfig::HASHCAT_BRAIN_ENABLE, '0');
Factory::getConfigFactory()->save($config);
$config = new Config(null, 1, DConfig::HASHCAT_BRAIN_HOST, '');
Factory::getConfigFactory()->save($config);
$config = new Config(null, 1, DConfig::HASHCAT_BRAIN_PORT, '0');
Factory::getConfigFactory()->save($config);
$config = new Config(null, 1, DConfig::HASHCAT_BRAIN_PASS, '');
Factory::getConfigFactory()->save($config);
$config = new Config(null, 1, DConfig::HASHLIST_IMPORT_CHECK, '0');
Factory::getConfigFactory()->save($config);
$config = new Config(null, 5, DConfig::ALLOW_DEREGISTER, '1');
Factory::getConfigFactory()->save($config);
echo "OK\n";

echo "Add brain settings... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Hashlist` ADD `brainId` INT NOT NULL;");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Hashlist` ADD `brainFeatures` INT NOT NULL");
echo "OK\n";

echo "Update agent error table... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `AgentError` ADD `chunkId` INT NULL;");
echo "OK\n";

echo "Update chunk table... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Chunk` CHANGE `progress` `progress` INT(11) NULL;");
echo "OK\n";

echo "Update taskwrapper table... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `TaskWrapper` ADD `cracked` INT NOT NULL;");
echo "OK\n";

echo "Add speed table... ";
Factory::getAgentFactory()->getDB()->query("CREATE TABLE `Speed` (
  `speedId` int(11) NOT NULL,
  `agentId` int(11) NOT NULL,
  `taskId` int(11) NOT NULL,
  `speed` bigint(20) NOT NULL,
  `time` bigint(20) NOT NULL
) ENGINE=InnoDB;");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Speed` ADD PRIMARY KEY (`speedId`);");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Speed` MODIFY `speedId` int(11) NOT NULL AUTO_INCREMENT;");
echo "OK\n";

echo "Update agent binary table... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `AgentBinary` ADD `updateAvailable` VARCHAR(20) NOT NULL");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `AgentBinary` ADD `updateTrack` VARCHAR(20) NOT NULL");
echo "OK\n";

echo "Update complete!\n";
