<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 03.03.17
 * Time: 14:54
 */

require_once(dirname(__FILE__) . "/../../inc/load.php");

$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `LogEntry` CHANGE `level` `level` VARCHAR(20) NOT NULL");
