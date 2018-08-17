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
Util::checkAgentVersion("python", "0.1.7");
Util::checkAgentVersion("csharp", "0.52.4");
echo "\n";

echo "Creating User API...";
$FACTORIES::getAgentFactory()->getDB()->query("
CREATE TABLE `ApiKey` (
  `apiKeyId` int(11) NOT NULL,
  `startValid` bigint(20) NOT NULL,
  `endValid` bigint(20) NOT NULL,
  `accessKey` varchar(256) NOT NULL,
  `accessCount` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `apiGroupId` int(11) NOT NULL
)");
$FACTORIES::getAgentFactory()->getDB()->query("
ALTER TABLE `ApiKey`
  ADD PRIMARY KEY (`apiKeyId`)");
$FACTORIES::getAgentFactory()->getDB()->query("
ALTER TABLE `ApiKey`
  MODIFY `apiKeyId` int(11) NOT NULL AUTO_INCREMENT");
$FACTORIES::getAgentFactory()->getDB()->query("
CREATE TABLE `ApiGroup` (
  `apiGroupId` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `permissions` text NOT NULL
)");
$FACTORIES::getAgentFactory()->getDB()->query("
ALTER TABLE `ApiGroup`
  ADD PRIMARY KEY (`apiGroupId`)");
$FACTORIES::getAgentFactory()->getDB()->query("
ALTER TABLE `ApiGroup`
  MODIFY `apiGroupId` int(11) NOT NULL AUTO_INCREMENT");
$FACTORIES::getAgentFactory()->getDB()->query("INSERT INTO `ApiGroup` ( `name`, `permissions`) VALUES ('Administrators', 'ALL')");
echo "OK\n";

echo "Adding new config settings...";
$entry = new Config(0, 1, 'ruleSplitSmallTasks', '0');
$FACTORIES::getConfigFactory()->save($entry);
$entry = new Config(0, 1, 'ruleSplitAlways', '0');
$FACTORIES::getConfigFactory()->save($entry);
$entry = new Config(0, 1, 'ruleSplitDisable', '0');
$FACTORIES::getConfigFactory()->save($entry);
echo "OK\n";

echo "Fix constraints on preconfigured tasks...";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `Pretask` CHANGE `color` `color` VARCHAR(20) NULL");
echo "OK\n";

echo "Add archiving and PRINCE flag to Tasks...";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `Task` ADD `isArchived` INT NOT NULL");
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `TaskWrapper` ADD `isArchived` INT NOT NULL");
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `Task` ADD `isPrince` INT NOT NULL");
echo "OK\n";

echo "Adding PRINCE settings...";
$config = new Config(0, 1, DConfig::PRINCE_LINK, 'https://github.com/hashcat/princeprocessor/releases/download/v0.22/princeprocessor-0.22.7z');
$FACTORIES::getConfigFactory()->save($config);
echo "OK\n";

echo "Update complete!\n";
