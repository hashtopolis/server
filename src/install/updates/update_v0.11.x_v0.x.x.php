<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Factory;

if (!isset($TEST)) {
  /** @noinspection PhpIncludeInspection */
  require_once(dirname(__FILE__) . "/../../inc/conf.php");
  require_once(dirname(__FILE__) . "/../../inc/info.php");
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

if (!isset($PRESENT["v0.11.x_preprocessors"])) {
  if (!Util::databaseTableExists("Preprocessor")) {
    Factory::getAgentFactory()->getDB()->query("CREATE TABLE `Preprocessor` (
        `preprocessorId`  INT(11)      NOT NULL,
        `name`            VARCHAR(256) NOT NULL,
        `url`             VARCHAR(512) NOT NULL,
        `binaryName`      VARCHAR(256) NOT NULL,
        `keyspaceCommand` VARCHAR(256) NOT NULL,
        `skipCommand`     VARCHAR(256) NOT NULL,
        `limitCommand`    VARCHAR(256) NOT NULL
      ) ENGINE=InnoDB;"
    );
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Preprocessor` ADD PRIMARY KEY (`preprocessorId`);");
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Preprocessor` MODIFY `preprocessorId` INT(11) NOT NULL AUTO_INCREMENT;");
  }
  $EXECUTED["v0.11.x_preprocessors"] = true;
}