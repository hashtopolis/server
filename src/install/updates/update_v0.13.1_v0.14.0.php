<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Factory;
use DBA\HashType;


if (!isset($PRESENT["v0.14.x_maxAgents_taskwrapper"])) {
  if (!Util::databaseColumnExists("TaskWrapper", "maxAgents")) {
    Factory::getFileFactory()->getDB()->query("ALTER TABLE `TaskWrapper` ADD `maxAgents` INT(11) NOT NULL;");
  }
  $EXECUTED["v0.14.x_maxAgents_taskwrapper"] = true;
}