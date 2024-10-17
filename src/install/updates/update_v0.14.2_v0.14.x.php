<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Factory;

if (!isset($PRESENT["v0.14.x_attackCmd"])) {
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Task` MODIFY `attackCmd` TEXT NOT NULL;");
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Pretask` MODIFY `attackCmd` TEXT NOT NULL;");
  Factory::getAgentFactory()->getDB()->query("ALTER TABLE `HealthCheck` MODIFY `attackCmd` TEXT NOT NULL;");
  $EXECUTED["v0.14.x_attackCmd"] = true;
}
