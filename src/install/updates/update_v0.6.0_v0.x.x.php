<?php

use DBA\AgentBinary;
use DBA\QueryFilter;
use DBA\Factory;
use DBA\Config;

require_once(dirname(__FILE__) . "/../../inc/db.php");
require_once(dirname(__FILE__) . "/../../dba/init.php");
require_once(dirname(__FILE__) . "/../../inc/Util.class.php");

$FACTORIES = new Factory();

echo "Apply updates...\n";

echo "Check agent binaries... ";
$qF = new QueryFilter(AgentBinary::TYPE, "python", "=");
$binary = $FACTORIES::getAgentBinaryFactory()->filter(array($FACTORIES::FILTER => $qF), true);
if ($binary != null) {
  if (Util::versionComparison($binary->getVersion(), "0.1.4") == 1) {
    echo "update python version... ";
    $binary->setVersion("0.1.4");
    $FACTORIES::getAgentBinaryFactory()->update($binary);
    echo "OK";
  }
}
$qF = new QueryFilter(AgentBinary::TYPE, "csharp", "=");
$binary = $FACTORIES::getAgentBinaryFactory()->filter(array($FACTORIES::FILTER => $qF), true);
if ($binary != null) {
  if (Util::versionComparison($binary->getVersion(), "0.52.2") == 1) {
    echo "update csharp version... ";
    $binary->setVersion("0.52.2");
    $FACTORIES::getAgentBinaryFactory()->update($binary);
    echo "OK";
  }
}
echo "\n";

echo "Creating User API...";
$FACTORIES::getAgentFactory()->getDB()->query("
CREATE TABLE `apikey` (
  `apiKeyId` int(11) NOT NULL,
  `startValid` bigint(20) NOT NULL,
  `endValid` bigint(20) NOT NULL,
  `accessKey` varchar(256) NOT NULL,
  `accessCount` int(11) NOT NULL,
  `permissions` text NOT NULL
)");
$FACTORIES::getAgentFactory()->getDB()->query("
ALTER TABLE `ApiKey`
  ADD PRIMARY KEY (`apiKeyId`)");
$FACTORIES::getAgentFactory()->getDB()->query("
ALTER TABLE `ApiKey`
  MODIFY `apiKeyId` int(11) NOT NULL AUTO_INCREMENT");
$FACTORIES::getAgentFactory()->getDB()->query("
CREATE TABLE `apikeyuser` (
  `apiKeyUserId` int(11) NOT NULL,
  `apiKeyId` int(11) NOT NULL,
  `userId` int(11) NOT NULL
)");
$FACTORIES::getAgentFactory()->getDB()->query("
ALTER TABLE `ApiKeyUser`
  ADD PRIMARY KEY (`apiKeyUserId`)");
$FACTORIES::getAgentFactory()->getDB()->query("
ALTER TABLE `ApiKeyUser` 
  MODIFY `apiKeyUserId` INT(11) NOT NULL AUTO_INCREMENT");
echo "OK\n";

echo "Adding new config settings...";
$entry = new Config(0, 1, 'ruleSplitSmallTasks', '0');
$FACTORIES::getConfigFactory()->save($entry);
$entry = new Config(0, 1, 'ruleSplitAlways', '0');
$FACTORIES::getConfigFactory()->save($entry);
$entry = new Config(0, 1, 'ruleSplitDisable', '0');
$FACTORIES::getConfigFactory()->save($entry);
echo "OK\n";

echo "Update complete!\n";
