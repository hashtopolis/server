<?php

use DBA\AgentFactory;
use DBA\FilePretask;
use DBA\Pretask;
use DBA\SupertaskPretask;

@include(dirname(__FILE__) . "/../../inc/db.php");
include(dirname(__FILE__) . "/../../dba/init.php");

echo "WARNING!!!!\n";
echo "This update contains some drastic changes and running tasks and chunks will be removed, the config section will be reset to default!\n";
echo "Backup the database before applying this update, in case something does not run as expected!\n";
echo "Enter 'AGREE' to continue... \n";
$confirm = trim(fgets(STDIN));
if ($confirm != 'AGREE') {
  die("Aborted!\n");
}

$aF = new AgentFactory();
$DB = $aF->getDB();
$DB->beginTransaction();

echo "Apply updates...\n";

echo "Add ConfigSection table... ";
$DB->exec("CREATE TABLE `ConfigSection` ( `configSectionId` INT(11) NOT NULL, `sectionName` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL) ENGINE=InnoDB");
$DB->exec("ALTER TABLE `ConfigSection` ADD PRIMARY KEY (`configSectionId`);");
$DB->exec("ALTER TABLE `ConfigSection` MODIFY `configSectionId` INT(11) NOT NULL AUTO_INCREMENT;");
echo "OK\n";

echo "Fill ConfigSection table... ";
$DB->exec("INSERT INTO `ConfigSection` (`configSectionId`, `sectionName`) VALUES (1, 'Cracking/Tasks'), (2, 'Yubikey'), (3, 'Finetuning'), (4, 'UI'), (5, 'Server');");
echo "OK\n";

echo "Read Config table... ";
$stmt = $DB->query("SELECT * FROM `Config` WHERE 1");
$configs = $stmt->fetchAll();
// read some important values
$saved = array();
foreach ($configs as $config) {
  $saved[$config['item']] = $config['value'];
}
echo "OK\n";

echo "Clear and Update Config table... ";
$DB->exec("TRUNCATE `Config`;");
$DB->exec("ALTER TABLE `Config` ADD `configSectionId` INT(11) NOT NULL;");
$DB->exec("ALTER TABLE `Config` ADD KEY `configSectionId` (`configSectionId`);");
$DB->exec("ALTER TABLE `Config` ADD CONSTRAINT `Config_ibfk_1` FOREIGN KEY (`configSectionId`) REFERENCES `ConfigSection` (`configSectionId`);");
echo "OK\n";

echo "Refill Config table... ";
$DB->exec("INSERT INTO `Config` (`configId`, `configSectionId`, `item`, `value`) VALUES
(1, 1, 'agenttimeout', '30'),
(2, 1, 'benchtime', '30'),
(3, 1, 'chunktime', '600'),
(4, 1, 'chunktimeout', '30'),
(9, 1, 'fieldseparator', ':'),
(10, 1, 'hashlistAlias', '#HL#'),
(11, 1, 'statustimer', '5'),
(12, 4, 'timefmt', 'd.m.Y, H:i:s'),
(13, 1, 'blacklistChars', '&|`\"\''),
(14, 3, 'numLogEntries', '5000'),
(15, 1, 'disptolerance', '20'),
(16, 3, 'batchSize', '10000'),
(18, 2, 'yubikey_id', '{$saved['yubikey_id']}'),
(19, 2, 'yubikey_key', '{$saved['yubikey_key']}'),
(20, 2, 'yubikey_url', '{$saved['yubikey_url']}'),
(21, 4, 'donateOff', '0'),
(22, 3, 'pagingSize', '5000'),
(23, 3, 'hashlistDownloadChunkSize', '5000'),
(24, 3, 'plainTextMaxLength', '200'),
(25, 3, 'hashMaxLength', '1024'),
(26, 5, 'emailSender', 'hashtopussy@example.org'),
(27, 5, 'baseHost', '{$saved['baseHost']}'),
(28, 3, 'maxHashlistSize', '5000000'),
(29, 4, 'hideImportMasks', '1'),
(30, 5, 'telegramBotToken', '{$saved['telegramBotToken']}'),
(31, 5, 'contactEmail', 's3inlc@hashes.org'),
(32, 5, 'voucherDeletion', '0'),
(33, 4, 'hashesPerPage', '1000'),
(34, 4, 'hideIpInfo', '1'),
(35, 5, 'baseUrl', '{$saved['baseUrl']}');"
);
echo "OK\n";

echo "Reload full include... ";
require_once(dirname(__FILE__) . "/../../inc/load.php");
echo "OK\n";

echo "Create AccessGroup tables... ";
$DB->exec("CREATE TABLE `AccessGroup` (`accessGroupId` INT(11) NOT NULL, `groupName` VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL) ENGINE=InnoDB;");
$DB->exec("CREATE TABLE `AccessGroupAgent` (`accessGroupAgentId` INT(11) NOT NULL, `accessGroupId` INT(11) NOT NULL, `agentId` INT(11) NOT NULL) ENGINE=InnoDB;");
$DB->exec("CREATE TABLE `AccessGroupUser` (`accessGroupUserId` INT(11) NOT NULL, `accessGroupId` INT(11) NOT NULL, `userId` INT(11) NOT NULL) ENGINE=InnoDB;");
echo "OK\n";

echo "Apply Indexes to AccessGroup tables... ";
$DB->exec("ALTER TABLE `AccessGroup` ADD PRIMARY KEY (`accessGroupId`);");
$DB->exec("ALTER TABLE `AccessGroup` MODIFY `accessGroupId` INT(11) NOT NULL AUTO_INCREMENT;");
$DB->exec("ALTER TABLE `AccessGroupAgent` ADD PRIMARY KEY (`accessGroupAgentId`), ADD KEY `accessGroupId` (`accessGroupId`), ADD KEY `agentId` (`agentId`);");
$DB->exec("ALTER TABLE `AccessGroupAgent` MODIFY `accessGroupAgentId` INT(11) NOT NULL AUTO_INCREMENT;");
$DB->exec("ALTER TABLE `AccessGroupAgent` ADD CONSTRAINT `AccessGroupAgent_ibfk_1` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`), ADD CONSTRAINT `AccessGroupAgent_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);");
$DB->exec("ALTER TABLE `AccessGroupUser` ADD PRIMARY KEY (`accessGroupUserId`), ADD KEY `accessGroupId` (`accessGroupId`), ADD KEY `userId` (`userId`);");
$DB->exec("ALTER TABLE `AccessGroupUser` MODIFY `accessGroupUserId` INT(11) NOT NULL AUTO_INCREMENT;");
$DB->exec("ALTER TABLE `AccessGroupUser` ADD CONSTRAINT `AccessGroupUser_ibfk_1` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`), ADD CONSTRAINT `AccessGroupUser_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);");
echo "OK\n";

echo "Create default access group... ";
$DB->exec("INSERT INTO `AccessGroup` (`accessGroupId`, `groupName`) VALUES (1, 'Default Group');");
echo "OK\n";

echo "Update Agent table... ";
$DB->exec("ALTER TABLE `Agent` CHANGE `gpus` `devices` TEXT NOT NULL;");
$DB->exec("ALTER TABLE `Agent` DROP COLUMN `hcVersion`;");
$DB->exec("ALTER TABLE `Agent` ADD `clientSignature` VARCHAR(50);");
echo "OK\n";

echo "Add AgentStat page... ";
$DB->exec("CREATE TABLE `AgentStat` (`agentStatId` INT(11) NOT NULL, `agentId` INT(11) NOT NULL, `statType` INT(11) NOT NULL, `time` INT(11) NOT NULL, `value` INT(11) NOT NULL) ENGINE=InnoDB");
$DB->exec("ALTER TABLE `AgentStat` ADD PRIMARY KEY (`agentStatId`), ADD KEY `agentId` (`agentId`);");
$DB->exec("ALTER TABLE `AgentStat` MODIFY `agentStatId` INT(11) NOT NULL AUTO_INCREMENT;");
$DB->exec("ALTER TABLE `AgentStat` ADD CONSTRAINT `AgentStat_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);");
echo "OK\n";

echo "Update AgentZap table... ";
$DB->exec("DROP TABLE `AgentZap`;");
$DB->exec("CREATE TABLE `AgentZap` ( `agentZapId` INT(11) NOT NULL, `agentId` INT(11) NOT NULL, `lastZapId` INT(11) DEFAULT NULL) ENGINE=InnoDB;");
$DB->exec("ALTER TABLE `AgentZap` ADD PRIMARY KEY (`agentZapId`), ADD KEY `agentId` (`agentId`), ADD KEY `lastZapId` (`lastZapId`);");
$DB->exec("ALTER TABLE `AgentZap` MODIFY `agentZapId` INT(11) NOT NULL AUTO_INCREMENT;");
$DB->exec("ALTER TABLE `AgentZap` ADD CONSTRAINT `AgentZap_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`), ADD CONSTRAINT `AgentZap_ibfk_2` FOREIGN KEY (`lastZapId`) REFERENCES `Zap` (`zapId`);");
echo "OK\n";

echo "Update Hash and HashBinary table... ";
$DB->exec("UPDATE `Hash` SET `chunkId`=NULL WHERE 1");
$DB->exec("UPDATE `HashBinary` SET `chunkId`=NULL WHERE 1");
echo "OK\n";

echo "Clear chunk table... ";
$DB->exec("DELETE FROM `Chunk` WHERE 1;");
echo "OK\n";

echo "Update Chunk table... ";
$DB->exec("ALTER TABLE `Chunk` CHANGE `progress` `checkpoint` BIGINT(20) NOT NULL;");
$DB->exec("ALTER TABLE `Chunk` CHANGE `rprogress` `progress` INT(11) NOT NULL;");
echo "OK\n";

echo "Add CrackerBinaryType table... ";
$DB->exec("CREATE TABLE `CrackerBinaryType` ( `crackerBinaryTypeId` INT(11) NOT NULL, `typeName` VARCHAR(30) COLLATE utf8_unicode_ci NOT NULL, `isChunkingAvailable` INT(11) NOT NULL) ENGINE=InnoDB;");
$DB->exec("ALTER TABLE `CrackerBinaryType` ADD PRIMARY KEY (`crackerBinaryTypeId`);");
$DB->exec("ALTER TABLE `CrackerBinaryType` MODIFY `crackerBinaryTypeId` INT(11) NOT NULL AUTO_INCREMENT;");
echo "OK\n";

echo "Add Hashcat to CrackerBinaryType table... ";
$DB->exec("INSERT INTO `CrackerBinaryType` (`crackerBinaryTypeId`, `typeName`, `isChunkingAvailable`) VALUES (1, 'Hashcat', 1);");
echo "OK\n";

echo "Add CrackerBinary table... ";
$DB->exec("CREATE TABLE `CrackerBinary` ( `crackerBinaryId` INT(11) NOT NULL, `crackerBinaryTypeId` INT(11) NOT NULL, `version` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL, `downloadUrl` VARCHAR(150) COLLATE utf8_unicode_ci NOT NULL, `binaryName` VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL) ENGINE=InnoDB;");
$DB->exec("ALTER TABLE `CrackerBinary` ADD PRIMARY KEY (`crackerBinaryId`), ADD KEY `crackerBinaryTypeId` (`crackerBinaryTypeId`);");
$DB->exec("ALTER TABLE `CrackerBinary` MODIFY `crackerBinaryId` INT(11) NOT NULL AUTO_INCREMENT");
$DB->exec("ALTER TABLE `CrackerBinary` ADD CONSTRAINT `CrackerBinary_ibfk_1` FOREIGN KEY (`crackerBinaryTypeId`) REFERENCES `CrackerBinaryType` (`crackerBinaryTypeId`);");
echo "OK\n";

echo "Update File table... ";
$DB->exec("ALTER TABLE `File` CHANGE `secret` `isSecret` INT(11) NOT NULL;");
echo "OK\n";

echo "Update Hash table... ";
$DB->exec("ALTER TABLE `Hash` CHANGE `time` `timeCracked` INT(11) NOT NULL;");
echo "OK\n";

echo "Update HashBinary table... ";
$DB->exec("ALTER TABLE `HashBinary` CHANGE `time` `timeCracked` INT(11) NOT NULL;");
echo "OK\n";

echo "Drop HashcatRelease table... ";
$DB->exec("DROP TABLE HashcatRelease;");
echo "OK\n";

echo "Update Hashlist table... ";
$DB->exec("ALTER TABLE `Hashlist` CHANGE `secret` `isSecret` INT(11) NOT NULL;");
$DB->exec("ALTER TABLE `Hashlist` ADD `accessGroupId` INT(11) NOT NULL");
$DB->exec("UPDATE `Hashlist` SET `accessGroupId`=1 WHERE 1");
$DB->exec("ALTER TABLE `Hashlist` ADD CONSTRAINT `Hashlist_ibfk_2` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`);");
echo "OK\n";

echo "Drop HashlistAgent table... ";
$DB->exec("DROP TABLE `HashlistAgent`");
echo "OK\n";

echo "Add Pretask table... ";
$DB->exec("CREATE TABLE `Pretask` ( `pretaskId` INT(11) NOT NULL, `taskName` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL, `attackCmd` VARCHAR(256) COLLATE utf8_unicode_ci NOT NULL, `chunkTime` INT(11) NOT NULL, `statusTimer` INT(11) NOT NULL, `color` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL, `isSmall` TINYINT(4) NOT NULL, `isCpuTask` TINYINT(4) NOT NULL, `useNewBench` TINYINT(4) NOT NULL, `priority` INT(11) NOT NULL, `isMaskImport` TINYINT(4) NOT NULL, `crackerBinaryTypeId` INT(11) NOT NULL) ENGINE=InnoDB;");
$DB->exec("ALTER TABLE `Pretask` ADD PRIMARY KEY (`pretaskId`);");
$DB->exec("ALTER TABLE `Pretask` MODIFY `pretaskId` INT(11) NOT NULL AUTO_INCREMENT;");
echo "OK\n";

echo "Update HashlistHashlist table... ";
$DB->exec("ALTER TABLE `SuperHashlistHashlist` RENAME `HashlistHashlist`;");
$DB->exec("ALTER TABLE `HashlistHashlist` CHANGE `superHashlistId` `parentHashlistId` INT(11) NOT NULL;");
$DB->exec("ALTER TABLE `HashlistHashlist` CHANGE `superHashlistHashlistId` `hashlistHashlistId` INT(11) NOT NULL;");
echo "OK\n";

echo "Extract SupertaskTask table... ";
$stmt = $DB->query("SELECT * FROM SupertaskTask WHERE 1;");
$supertaskTasks = $stmt->fetchAll();
echo "OK\n";

echo "Update SupertaskPretask table... ";
$DB->exec("DROP TABLE SupertaskTask;");
$DB->exec("CREATE TABLE `SupertaskPretask` (`supertaskPretaskId` INT(11) NOT NULL, `supertaskId` INT(11) NOT NULL, `pretaskId` INT(11) NOT NULL) ENGINE=InnoDB;");
$DB->exec("ALTER TABLE `SupertaskPretask` ADD PRIMARY KEY (`supertaskPretaskId`), ADD KEY `supertaskId` (`supertaskId`), ADD KEY `pretaskId` (`pretaskId`);");
$DB->exec("ALTER TABLE `SupertaskPretask` MODIFY `supertaskPretaskId` INT(11) NOT NULL AUTO_INCREMENT");
$DB->exec("ALTER TABLE `SupertaskPretask` ADD CONSTRAINT `SupertaskPretask_ibfk_1` FOREIGN KEY (`supertaskId`) REFERENCES `Supertask` (`supertaskId`), ADD CONSTRAINT `SupertaskPretask_ibfk_2` FOREIGN KEY (`pretaskId`) REFERENCES `Pretask` (`pretaskId`);");
echo "OK\n";

echo "Refill SupertaskPretask table... ";
foreach ($supertaskTasks as $supertaskTask) {
  $supertaskPretask = new SupertaskPretask(0, $supertaskTask['supertaskId'], $supertaskTask['taskId']);
}
echo "OK\n";

echo "Cache all Task entries... ";
$stmt = $DB->query("SELECT * FROM `Task` WHERE 1");
$tasks = $stmt->fetchAll();
$DB->exec("DELETE FROM `Task` WHERE 1;");
echo "OK\n";

echo "Cache all TaskFile entries... ";
$stmt = $DB->query("SELECT * FROM `TaskFile` WHERE 1");
$taskFiles = $stmt->fetchAll();
$DB->exec("DROP TABLE `TaskFile`");
echo "OK\n";

echo "Drop TaskTask table... ";
$DB->exec("DROP TABLE `TaskTask`");
echo "OK\n";

echo "Add FileTask table... ";
$DB->exec("CREATE TABLE `FileTask` (`fileTaskId` INT(11) NOT NULL, `fileId` INT(11) NOT NULL, `taskId` INT(11) NOT NULL) ENGINE=InnoDB;");
$DB->exec("ALTER TABLE `FileTask` ADD PRIMARY KEY (`fileTaskId`), ADD KEY `fileId` (`fileId`), ADD KEY `taskId` (`taskId`);");
$DB->exec("ALTER TABLE `FileTask` MODIFY `fileTaskId` INT(11) NOT NULL AUTO_INCREMENT;");
$DB->exec("ALTER TABLE `FileTask` ADD CONSTRAINT `FileTask_ibfk_1` FOREIGN KEY (`fileId`) REFERENCES `File` (`fileId`), ADD CONSTRAINT `FileTask_ibfk_2` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`);");
echo "OK\n";

echo "Add FilePretask table... ";
$DB->exec("CREATE TABLE `FilePretask` (`filePretaskId` INT(11) NOT NULL, `fileId` INT(11) NOT NULL, `pretaskId` INT(11) NOT NULL) ENGINE=InnoDB;");
$DB->exec("ALTER TABLE `FilePretask` ADD PRIMARY KEY (`filePretaskId`), ADD KEY `fileId` (`fileId`), ADD KEY `pretaskId` (`pretaskId`);");
$DB->exec("ALTER TABLE `FilePretask` MODIFY `filePretaskId` INT(11) NOT NULL AUTO_INCREMENT;");
$DB->exec("ALTER TABLE `FilePretask` ADD CONSTRAINT `FilePretask_ibfk_1` FOREIGN KEY (`fileId`) REFERENCES `File` (`fileId`), ADD CONSTRAINT `FilePretask_ibfk_2` FOREIGN KEY (`pretaskId`) REFERENCES `Pretask` (`pretaskId`);");
echo "OK\n";

echo "Add TaskWrapper table... ";
$DB->exec("CREATE TABLE `TaskWrapper` (`taskWrapperId` INT(11) NOT NULL, `priority` INT(11) NOT NULL, `taskType` INT(11) NOT NULL, `hashlistId` INT(11) NOT NULL, `accessGroupId` INT(11) DEFAULT NULL, `taskWrapperName` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL) ENGINE=InnoDB;");
$DB->exec("ALTER TABLE `TaskWrapper` ADD PRIMARY KEY (`taskWrapperId`), ADD KEY `hashlistId` (`hashlistId`), ADD KEY `accessGroupId` (`accessGroupId`);");
$DB->exec("ALTER TABLE `TaskWrapper` MODIFY `taskWrapperId` INT(11) NOT NULL AUTO_INCREMENT;");
$DB->exec("ALTER TABLE `TaskWrapper` ADD CONSTRAINT `TaskWrapper_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`), ADD CONSTRAINT `TaskWrapper_ibfk_2` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`);");
echo "OK\n";

echo "Update Task table... ";
$DB->exec("ALTER TABLE `Task` DROP `hashlistId`");
$DB->exec("ALTER TABLE `Task` DROP `taskType`");
$DB->exec("ALTER TABLE `Task` CHANGE `keyspaceProgress` `progress` BIGINT(20) NOT NULL;");
$DB->exec("ALTER TABLE `Task` ADD `crackerBinaryId` INT(11) NOT NULL");
$DB->exec("ALTER TABLE `Task` ADD `crackerBinaryTypeId` INT(11) NOT NULL");
$DB->exec("ALTER TABLE `Task` ADD `taskWrapperId` INT(11) NOT NULL");
$DB->exec("ALTER TABLE `Task` ADD CONSTRAINT `Task_ibfk_1` FOREIGN KEY (`crackerBinaryId`) REFERENCES `CrackerBinary` (`crackerBinaryId`);");
$DB->exec("ALTER TABLE `Task` ADD CONSTRAINT `Task_ibfk_2` FOREIGN KEY (`crackerBinaryTypeId`) REFERENCES `CrackerBinaryType` (`crackerBinaryTypeId`);");
$DB->exec("ALTER TABLE `Task` ADD CONSTRAINT `Task_ibfk_3` FOREIGN KEY (`taskWrapperId`) REFERENCES `TaskWrapper` (`taskWrapperId`);");
echo "OK\n";

echo "Update Zap table... ";
$DB->exec("ALTER TABLE `Zap` ADD CONSTRAINT `Zap_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`), ADD CONSTRAINT `Zap_ibfk_2` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`);");
echo "OK\n";

echo "Inserting pretasks... ";
foreach ($tasks as $task) {
  if ($task['hashlistId'] == null) {
    // pretask
    $pretask = new Pretask($task['taskId'], $task['taskName'], $task['attackCmd'], $task['chunkTime'], $task['statusTimer'], $task['color'], $task['isSmall'], $task['isCpuTask'], $task['useNewBench'], $task['priority'], 0, 1);
    $FACTORIES::getPretaskFactory()->save($pretask);
  }
}
echo "OK\n";

echo "Inserting filePretasks... ";
foreach ($taskFiles as $taskFile) {
  $fileTask = new FilePretask($taskFile['taskFileId'], $taskFile['fileId'], $taskFile['taskId']);
  $FACTORIES::getFilePretaskFactory()->save($fileTask);
}
echo "OK\n";

$DB->commit();

echo "Update complete!";