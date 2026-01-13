<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Factory;

require_once(dirname(__FILE__) . "/../../inc/startup/load.php");

echo "Apply updates...\n";

echo "Update Zap table... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Zap` CHANGE `agentId` `agentId` INT(11) NULL");
echo "OK\n";

echo "Update complete!\n";
