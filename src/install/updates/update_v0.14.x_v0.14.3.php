<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Factory;
use DBA\HashType;


if (!isset($PRESENT["v0.14.x_maxAgents_taskwrapper"])) {
  if (!Util::databaseColumnExists("TaskWrapper", "maxAgents")) {
    Factory::getFileFactory()->getDB()->query("ALTER TABLE `TaskWrapper` ADD `maxAgents` INT(11) NOT NULL;");
  }
  $EXECUTED["v0.14.x_maxAgents_taskwrapper"] = true;
}

if (!isset($PRESENT["v0.14.x_agentBinaries"])) {
  Util::checkAgentVersion("python", "0.7.2", true);
  $EXECUTED["v0.14.x_agentBinaries"] = true;
}


if (!isset($PRESENT["v0.14.x_attackCmd"])) {
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Task` MODIFY `attackCmd` TEXT NOT NULL;");
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Pretask` MODIFY `attackCmd` TEXT NOT NULL;");
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `HealthCheck` MODIFY `attackCmd` TEXT NOT NULL;");
  $EXECUTED["v0.14.x_attackCmd"] = true;
}
