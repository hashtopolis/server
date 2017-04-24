<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 06.03.17
 * Time: 12:16
 */

use DBA\AgentBinary;
use DBA\Config;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/../../inc/load.php");

echo "Apply updates...\n";

// insert updates here
echo "Add skipKeyspace column... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `Task` ADD `skipKeyspace` BIGINT NOT NULL");
echo "OK\n";

echo "Add Notification Table and Settings...";
$FACTORIES::getAgentFactory()->getDB()->query("CREATE TABLE `NotificationSetting` (`notificationSettingId` INT(11) NOT NULL, `action` VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL, `objectId` INT(11) NOT NULL, `notification` VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL, `userId` INT(11) NOT NULL, `receiver` VARCHAR(200) COLLATE utf8_unicode_ci NOT NULL, `isActive` TINYINT(4) NOT NULL) ENGINE=InnoDB");
echo "#";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `NotificationSetting` ADD PRIMARY KEY (`notificationSettingId`), ADD KEY `NotificationSetting_ibfk_1` (`userId`)");
echo "#";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `NotificationSetting` MODIFY `notificationSettingId` INT(11) NOT NULL AUTO_INCREMENT");
echo "#";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `NotificationSetting` ADD CONSTRAINT `NotificationSetting_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`)");
echo "OK\n";

echo "Applying new zapping...\n";
echo "Dropping old zap table... ";
$FACTORIES::getAgentFactory()->getDB()->query("DROP TABLE `Zap`");
echo "OK\n";
echo "Creating new zap table... ";
$FACTORIES::getAgentFactory()->getDB()->query("CREATE TABLE `Zap` (`zapId` INT(11) AUTO_INCREMENT PRIMARY KEY NOT NULL,`hash` VARCHAR(512) NOT NULL,`solveTime` INT(11) NOT NULL,`agentId` INT(11) NOT NULL,`hashlistId` INT(11) NOT NULL)");
echo "OK\n";
echo "Creating agentZap table... ";
$FACTORIES::getAgentFactory()->getDB()->query("CREATE TABLE `AgentZap` (`agentId` INT(11) AUTO_INCREMENT PRIMARY KEY NOT NULL, `lastZapId` INT(11) NOT NULL)");
echo "OK\n";
echo "New zapping changes applied!\n";

echo "Check csharp binary... ";
$qF = new QueryFilter(AgentBinary::TYPE, "csharp", "=");
$binary = $FACTORIES::getAgentBinaryFactory()->filter(array($FACTORIES::FILTER => $qF), true);
if ($binary != null) {
  if (Util::versionComparison($binary->getVersion(), "0.43") == 1) {
    echo "update version... ";
    $binary->setVersion("0.43");
    $FACTORIES::getAgentBinaryFactory()->update($binary);
    echo "OK";
  }
}
echo "\n";

echo "Please enter the base URL of the webpage (without protocol and hostname, just relatively to the root / of the domain):\n";
$url = readline();
$qF = new QueryFilter(Config::ITEM, DConfig::BASE_URL, "=");
$entry = $FACTORIES::getConfigFactory()->filter(array($FACTORIES::FILTER => $qF), true);
echo "applying... ";
if ($entry == null) {
  $entry = new Config(0, DConfig::BASE_URL, $url);
  $FACTORIES::getConfigFactory()->save($entry);
}
else {
  $entry->setValue($url);
  $FACTORIES::getConfigFactory()->update($entry);
}
echo "OK\n";

echo "Update complete!\n";
