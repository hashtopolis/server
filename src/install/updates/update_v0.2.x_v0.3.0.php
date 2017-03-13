<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 06.03.17
 * Time: 12:16
 */

require_once(dirname(__FILE__) . "/../../inc/load.php");

echo "Apply updates...\n";

// insert updates here
echo "Add skipKeyspace column... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `Task` ADD `skipKeyspace` BIGINT NOT NULL");
echo "OK\n";

echo "Applying new zapping...\n";
echo "Dropping old zap table... ";
$FACTORIES::getAgentFactory()->getDB()->query("DROP TABLE `Zap`");
echo "OK\n";
echo "Creating new zap table... ";
$FACTORIES::getAgentFactory()->getDB()->query("CREATE TABLE `Zap` (`zapId` INT(11) AUTO_INCREMENT PRIMARY KEY NOT NULL,`hash` INT(11) NOT NULL,`solveTime` INT(11) NOT NULL,`hashlistId` INT(11) NOT NULL)");
echo "OK\n";
echo "Creating agentZap table... ";
$FACTORIES::getAgentFactory()->getDB()->query("CREATE TABLE `AgentZap` (`agentId` INT(11) AUTO_INCREMENT PRIMARY KEY NOT NULL, `lastZapId` INT(11) NOT NULL)");
echo "OK\n";
echo "New zapping changes applied!\n";

echo "Update complete!\n";

