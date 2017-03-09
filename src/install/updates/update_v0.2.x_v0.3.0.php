<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 06.03.17
 * Time: 12:16
 */

require_once(dirname(__FILE__) . "/../../inc/load.php");

echo "Apply updates...\n";

// insert updates here
echo "Add skipKeyspace column... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `Task` ADD `skipKeyspace` BIGINT NOT NULL");
echo "OK\n";

echo "Add Notification Table and Settings...";
$FACTORIES::getAgentFactory()->getDB()->query("CREATE TABLE `NotificationSetting` (`notificationSettingId` int(11) NOT NULL, `action` varchar(50) COLLATE utf8_unicode_ci NOT NULL, `notification` varchar(50) COLLATE utf8_unicode_ci NOT NULL, `userId` int(11) NOT NULL, `receiver` varchar(200) COLLATE utf8_unicode_ci NOT NULL, `isActive` tinyint(4) NOT NULL) ENGINE=InnoDB");
echo "#";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `NotificationSetting` ADD PRIMARY KEY (`notificationSettingId`), ADD KEY `NotificationSetting_ibfk_1` (`userId`)");
echo "#";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `NotificationSetting` MODIFY `notificationSettingId` int(11) NOT NULL AUTO_INCREMENT");
echo "#";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `NotificationSetting` ADD CONSTRAINT `NotificationSetting_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`)");
echo "OK\n";

echo "Update complete!\n";
