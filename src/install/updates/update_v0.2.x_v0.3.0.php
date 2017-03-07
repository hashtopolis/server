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

echo "Update complete!\n";

