<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Config;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\AgentBinary;

if (!isset($TEST)) {
  require_once(dirname(__FILE__) . "/../../inc/confv2.php");
  require_once(dirname(__FILE__) . "/../../inc/info.php");
  require_once(dirname(__FILE__) . "/../../dba/init.php");
  require_once(dirname(__FILE__) . "/../../inc/Util.class.php");
}
require_once(dirname(__FILE__) . "/../../inc/defines/config.php");
require_once(dirname(__FILE__) . "/../../inc/defines/log.php");

if (!isset($PRESENT["v0.9.0_conf1"])) {
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
  $EXECUTED["v0.9.0_conf1"] = true;
}

if (!isset($PRESENT["v0.9.0_brain"])) {
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Hashlist` ADD `brainId` INT NOT NULL;");
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Hashlist` ADD `brainFeatures` INT NOT NULL");
  $EXECUTED["v0.9.0_brain"] = true;
}

if (!isset($PRESENT["v0.9.0_agentError"])) {
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `AgentError` ADD `chunkId` INT NULL;");
  $EXECUTED["v0.9.0_agentError"] = true;
}

if (!isset($PRESENT["v0.9.0_chunk"])) {
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Chunk` CHANGE `progress` `progress` INT(11) NULL;");
  $EXECUTED["v0.9.0_chunk"] = true;
}

if (!isset($PRESENT["v0.9.0_taskWrapper"])) {
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `TaskWrapper` ADD `cracked` INT NOT NULL;");
  $EXECUTED["v0.9.0_taskWrapper"] = true;
}

if (!isset($PRESENT["v0.9.0_speed"])) {
  Factory::getAgentFactory()->getDB()->query("CREATE TABLE `Speed` (
      `speedId` INT(11)    NOT NULL,
      `agentId` INT(11)    NOT NULL,
      `taskId`  INT(11)    NOT NULL,
      `speed`   BIGINT(20) NOT NULL,
      `time`    BIGINT(20) NOT NULL
    ) ENGINE=InnoDB;"
  );
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Speed` ADD PRIMARY KEY (`speedId`);");
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Speed` MODIFY `speedId` INT(11) NOT NULL AUTO_INCREMENT;");
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Speed`
  ADD CONSTRAINT `Speed_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `Speed_ibfk_2` FOREIGN KEY (`taskId`)  REFERENCES `Task` (`taskId`);"
  );
  $EXECUTED["v0.9.0_speed"] = true;
}

if (!isset($PRESENT["v0.9.0_agentBinaries"])) {
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `AgentBinary` ADD `updateAvailable` VARCHAR(20) NOT NULL");
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `AgentBinary` ADD `updateTrack`     VARCHAR(20) NOT NULL");
  $qF = new QueryFilter("type", "python", "=");
  $agent = Factory::getAgentBinaryFactory()->filter([Factory::FILTER => $qF], true);
  if ($agent != null) {
    $agent->setUpdateTrack('stable');
    Factory::getAgentBinaryFactory()->update($agent);
  }
  
  Util::checkAgentVersion("python", "0.4.0", true);
  $EXECUTED["v0.9.0_agentBinaries"] = true;
}
