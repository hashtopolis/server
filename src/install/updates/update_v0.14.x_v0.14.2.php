<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Factory;

if (!isset($PRESENT["v0.14.x_maxAgents_taskwrapper"])) {
  if (!Util::databaseColumnExists("TaskWrapper", "maxAgents")) {
    Factory::getFileFactory()->getDB()->query("ALTER TABLE `TaskWrapper` ADD `maxAgents` INT(11) NOT NULL;");
  }
  $EXECUTED["v0.14.x_maxAgents_taskwrapper"] = true;
}

if (!isset($PRESENT["v0.14.x_agentBinaries"])) {
  Util::checkAgentVersionLegacy("python", "0.7.2", true);
  $EXECUTED["v0.14.x_agentBinaries"] = true;
}
