<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Config;
use DBA\Factory;
use DBA\HashType;
use DBA\QueryFilter;

if (!isset($TEST)) {
  require_once(dirname(__FILE__) . "/../../inc/StartupConfig.class.php");
  require_once(dirname(__FILE__) . "/../../dba/init.php");
  require_once(dirname(__FILE__) . "/../../inc/Util.class.php");
}
require_once(dirname(__FILE__) . "/../../inc/defines/config.php");
require_once(dirname(__FILE__) . "/../../inc/defines/log.php");

if (!isset($PRESENT["v0.11.x_tasks"])) {
  if (Util::databaseColumnExists("Task", "isPrince")) {
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Task` ADD `usePreprocessor` TINYINT(4) NOT NULL;");
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Task` ADD `preprocessorCommand` VARCHAR(256) NOT NULL;");
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Task` DROP COLUMN `isPrince`;");
  }
  $EXECUTED["v0.11.x_tasks"] = true;
}

if (!isset($PRESENT["v0.11.x_agentBinaries"])) {
  Util::checkAgentVersionLegacy("python", "0.6.0", true);
  $EXECUTED["v0.11.x_agentBinaries"] = true;
}

if (!isset($PRESENT["v0.11.x_preprocessors"])) {
  if (!Util::databaseTableExists("Preprocessor")) {
    Factory::getAgentFactory()->getDB()->query("CREATE TABLE `Preprocessor` (
        `preprocessorId`  INT(11)      NOT NULL,
        `name`            VARCHAR(256) NOT NULL,
        `url`             VARCHAR(512) NOT NULL,
        `binaryName`      VARCHAR(256) NOT NULL,
        `keyspaceCommand` VARCHAR(256) NULL,
        `skipCommand`     VARCHAR(256) NULL,
        `limitCommand`    VARCHAR(256) NULL
      ) ENGINE=InnoDB;"
    );
    Factory::getAgentFactory()->getDB()->query("INSERT INTO `Preprocessor` ( `preprocessorId`, `name`, `url`, `binaryName`, `keyspaceCommand`, `skipCommand`, `limitCommand`) VALUES (1, 'Prince', 'https://github.com/hashcat/princeprocessor/releases/download/v0.22/princeprocessor-0.22.7z', 'pp', '--keyspace', '--skip', '--limit');");
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Preprocessor` ADD PRIMARY KEY (`preprocessorId`);");
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Preprocessor` MODIFY `preprocessorId` INT(11) NOT NULL AUTO_INCREMENT;");
  }
  $EXECUTED["v0.11.x_preprocessors"] = true;
}

if (!isset($PRESENT["v0.11.x_prince"])) {
  $qF = new QueryFilter(Config::ITEM, "princeLink", "=");
  Factory::getConfigFactory()->massDeletion([Factory::FILTER => $qF]);
  $EXECUTED["v0.11.x_prince"] = true;
}

if (!isset($PRESENT["v0.11.x_speed-index"])) {
  if (!Util::databaseIndexExists("Speed", "agentId")) {
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Speed` ADD KEY `agentId` (`agentId`);");
  }
  if (!Util::databaseIndexExists("Speed", "taskId")) {
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Speed` ADD KEY `taskId` (`taskId`);");
  }
  $EXECUTED["v0.11.x_speed-index"] = true;
}

if (!isset($PRESENT["v0.11.x_conf1"])) {
  $qF = new QueryFilter(Config::ITEM, DConfig::UAPI_SEND_TASK_IS_COMPLETE, "=");
  $item = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
  if (!$item) {
    $config = new Config(null, 3, DConfig::UAPI_SEND_TASK_IS_COMPLETE, '0');
    Factory::getConfigFactory()->save($config);
  }
  $EXECUTED["v0.11.x_conf1"] = true;
}

if (!isset($PRESENT["v0.11.x_conf2"])) {
  $qF = new QueryFilter(Config::ITEM, "telegramProxyEnable", "=");
  $item1 = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
  $qF = new QueryFilter(Config::ITEM, DConfig::NOTIFICATIONS_PROXY_ENABLE, "=");
  $item2 = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
  if ($item1 && !$item2) {
    Factory::getConfigFactory()->set($item1, Config::ITEM, DConfig::NOTIFICATIONS_PROXY_ENABLE);
  }
  
  $qF = new QueryFilter(Config::ITEM, "telegramProxyServer", "=");
  $item1 = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
  $qF = new QueryFilter(Config::ITEM, DConfig::NOTIFICATIONS_PROXY_SERVER, "=");
  $item2 = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
  if ($item1 && !$item2) {
    Factory::getConfigFactory()->set($item1, Config::ITEM, DConfig::NOTIFICATIONS_PROXY_SERVER);
  }
  
  $qF = new QueryFilter(Config::ITEM, "telegramProxyPort", "=");
  $item1 = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
  $qF = new QueryFilter(Config::ITEM, DConfig::NOTIFICATIONS_PROXY_PORT, "=");
  $item2 = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
  if ($item1 && !$item2) {
    Factory::getConfigFactory()->set($item1, Config::ITEM, DConfig::NOTIFICATIONS_PROXY_PORT);
  }
  
  $qF = new QueryFilter(Config::ITEM, "telegramProxyType", "=");
  $item1 = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
  $qF = new QueryFilter(Config::ITEM, DConfig::NOTIFICATIONS_PROXY_TYPE, "=");
  $item2 = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
  if ($item1 && !$item2) {
    Factory::getConfigFactory()->set($item1, Config::ITEM, DConfig::NOTIFICATIONS_PROXY_TYPE);
  }
  $EXECUTED["v0.11.x_conf2"] = true;
}

if (!isset($PRESENT["v0.11.x_conf3"])) {
  $qF = new QueryFilter(Config::ITEM, DConfig::HC_ERROR_IGNORE, "=");
  $item = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
  if (!$item) {
    $config = new Config(null, 1, DConfig::HC_ERROR_IGNORE, 'DeviceGetFanSpeed');
    Factory::getConfigFactory()->save($config);
  }
  $EXECUTED["v0.11.x_conf3"] = true;
}

if (!isset($PRESENT["v0.11.x_hashTypes"])) {
  $hashtypes = [
    new HashType(00000, 'Plaintext', 0, 1),
    new HashType(20600, 'Oracle Transportation Management (SHA256)', 0, 0),
    new HashType(20710, 'sha256(sha256($pass).$salt)', 1, 0),
    new HashType(20711, 'AuthMe sha256', 0, 0),
    new HashType(20800, 'sha256(md5($pass))', 0, 0),
    new HashType(20900, 'md5(sha1($pass).md5($pass).sha1($pass))', 0, 0),
    new HashType(21000, 'BitShares v0.x - sha512(sha512_bin(pass))', 0, 0),
    new HashType(21100, 'sha1(md5($pass.$salt))', 1, 0),
    new HashType(21200, 'md5(sha1($salt).md5($pass))', 1, 0),
    new HashType(21300, 'md5($salt.sha1($salt.$pass))', 1, 0),
    new HashType(21400, 'sha256(sha256_bin(pass))', 0, 0),
    new HashType(21500, 'SolarWinds Orion', 0, 1),
    new HashType(21600, 'Web2py pbkdf2-sha512', 0, 0),
    new HashType(21700, 'Electrum Wallet (Salt-Type 4)', 0, 0),
    new HashType(21800, 'Electrum Wallet (Salt-Type 5)', 0, 0),
    new HashType(22000, 'WPA-PBKDF2-PMKID+EAPOL', 0, 0),
    new HashType(22001, 'WPA-PMK-PMKID+EAPOL', 0, 0),
    new HashType(22100, 'BitLocker', 0, 0),
    new HashType(22200, 'Citrix NetScaler (SHA512)', 0, 0),
    new HashType(22300, 'sha256($salt.$pass.$salt)', 1, 0),
    new HashType(22400, 'AES Crypt (SHA256)', 0, 0)
  ];
  foreach ($hashtypes as $hashtype) {
    $check = Factory::getHashTypeFactory()->get($hashtype->getId());
    if ($check === null) {
      Factory::getHashTypeFactory()->save($hashtype);
    }
  }
  $EXECUTED["v0.11.x_hashTypes"] = true;
}

