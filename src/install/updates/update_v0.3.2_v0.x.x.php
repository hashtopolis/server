<?php

use DBA\AgentBinary;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/../../inc/load.php");

echo "Apply updates...\n";

echo "Add new config... ";
$FACTORIES::getAgentFactory()->getDB()->query("INSERT INTO `Config` (`configId`, `item`, `value`) VALUES (15, 'disptolerance', '20'), (16, 'batchSize', '10000')");
echo "OK\n";

echo "Change zap table... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `Zap` CHANGE `agentId` `agentId` INT(11) NULL");
echo "OK\n";

echo "Add hash index... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `Hash` ADD INDEX(`hash`);");
echo "OK\n";

echo "Increase hash length... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `Hash` CHANGE `hash` `hash` VARCHAR(1024) NOT NULL;");
echo "OK\n";

echo "Add Yubikey... ";
$FACTORIES::getAgentFactory()->getDB()->query("INSERT INTO `Config` (`configId`, `item`, `value`) VALUES (17, 'yubikey_id', '')");
$FACTORIES::getAgentFactory()->getDB()->query("INSERT INTO `Config` (`configId`, `item`, `value`) VALUES (18, 'yubikey_key', '')");
$FACTORIES::getAgentFactory()->getDB()->query("INSERT INTO `Config` (`configId`, `item`, `value`) VALUES (19, 'yubikey_url', 'https://api.yubico.com/wsapi/2.0/verify')");
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `User` ADD yubikey INT(1) NOT NULL, ADD otp1 VARCHAR(50) NOT NULL, ADD otp2 VARCHAR(50) NOT NULL, ADD otp3 VARCHAR(50) NOT NULL, ADD otp4 VARCHAR(50) NOT NULL;");
echo "OK\n";

echo "Add new Hashtypes... ";
$FACTORIES::getAgentFactory()->getDB()->query("INSERT INTO HashType (hashTypeId, description, isSalted) VALUES
  (600,'BLAKE2b-512',0),
  (9710,'MS Office <= 2003 $0/$1, MD5 + RC4, collider #1',0),
  (9720,'MS Office <= 2003 $0/$1, MD5 + RC4, collider #2',0),
  (9810,'MS Office <= 2003 $3, SHA1 + RC4, collider #1',0),
  (9820,'MS Office <= 2003 $3, SHA1 + RC4, collider #2',0),
  (10410,'PDF 1.1 - 1.3 (Acrobat 2 - 4), collider #1',0),
  (10420,'PDF 1.1 - 1.3 (Acrobat 2 - 4), collider #2',0),
  (12001,'Atlassian (PBKDF2-HMAC-SHA1)',0),
  (13711,'VeraCrypt PBKDF2-HMAC-RIPEMD160 + AES, Serpent, Twofish',0),
  (13712,'VeraCrypt PBKDF2-HMAC-RIPEMD160 + AES-Twofish, Serpent-AES, Twofish-Serpent',0),
  (13713,'VeraCrypt PBKDF2-HMAC-RIPEMD160 + Serpent-Twofish-AES',0),
  (13721,'VeraCrypt PBKDF2-HMAC-SHA512 + AES, Serpent, Twofish',0),
  (13722,'VeraCrypt PBKDF2-HMAC-SHA512 + AES-Twofish, Serpent-AES, Twofish-Serpent',0),
  (13723,'VeraCrypt PBKDF2-HMAC-SHA512 + Serpent-Twofish-AES',0),
  (13731,'VeraCrypt PBKDF2-HMAC-Whirlpool + AES, Serpent, Twofish',0),
  (13732,'VeraCrypt PBKDF2-HMAC-Whirlpool + AES-Twofish, Serpent-AES, Twofish-Serpent',0),
  (13733,'VeraCrypt PBKDF2-HMAC-Whirlpool + Serpent-Twofish-AES',0),
  (13751,'VeraCrypt PBKDF2-HMAC-SHA256 + AES, Serpent, Twofish',0),
  (13752,'VeraCrypt PBKDF2-HMAC-SHA256 + AES-Twofish, Serpent-AES, Twofish-Serpent',0),
  (13753,'VeraCrypt PBKDF2-HMAC-SHA256 + Serpent-Twofish-AES',0),
  (15000,'FileZilla Server >= 0.9.55',0),
  (15100,'Juniper/NetBSD sha1crypt',0),
  (15200,'Blockchain, My Wallet, V2',0),
  (15300,'DPAPI masterkey file v1 and v2',0),
  (15400,'ChaCha20',0),
  (15500,'JKS Java Key Store Private Keys (SHA1)',0),
  (15600,'Ethereum Wallet, PBKDF2-HMAC-SHA256',0),
  (15700,'Ethereum Wallet, SCRYPT',0);");
echo "OK\n";

echo "Update Task table... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `Task` ADD taskType INT(11);");
echo "OK\n";

echo "Create TaskTask table... ";
$FACTORIES::getAgentFactory()->getDB()->query("CREATE TABLE `TaskTask` (`taskTaskId` int(11) NOT NULL, `taskId` int(11) NOT NULL, `subtaskId` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
echo ".";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `TaskTask` ADD PRIMARY KEY (`taskTaskId`), ADD KEY `taskId` (`taskId`), ADD KEY `subtaskId` (`subtaskId`);");
echo ".";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `TaskTask` MODIFY `taskTaskId` int(11) NOT NULL AUTO_INCREMENT;");
echo ".";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `TaskTask` ADD CONSTRAINT FOREIGN KEY (`subtaskId`) REFERENCES `Task` (`taskId`);");
echo ".";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `TaskTask` ADD CONSTRAINT FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`);");
echo "OK\n";

echo "Check csharp binary... ";
$qF = new QueryFilter(AgentBinary::TYPE, "csharp", "=");
$binary = $FACTORIES::getAgentBinaryFactory()->filter(array($FACTORIES::FILTER => $qF), true);
if ($binary != null) {
  if (Util::versionComparison($binary->getVersion(), "0.43.13") == 1) {
    echo "update version... ";
    $binary->setVersion("0.43.4");
    $FACTORIES::getAgentBinaryFactory()->update($binary);
    echo "OK";
  }
}
echo "\n";

echo "Update complete!\n";
