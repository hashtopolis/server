<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 23.11.16
 * Time: 23:12
 */

require_once(dirname(__FILE__) . "/../inc/load.php");

$sql = file_get_contents(dirname(__FILE__)."/db.sql");
$FACTORIES::getSessionFactory()->getDB()->query($sql);

echo "Filled!\n";
