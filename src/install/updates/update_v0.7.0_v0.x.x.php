<?php

use DBA\Factory;
use DBA\File;

require_once(dirname(__FILE__) . "/../../inc/db.php");
require_once(dirname(__FILE__) . "/../../dba/init.php");
require_once(dirname(__FILE__) . "/../../inc/Util.class.php");
require_once(dirname(__FILE__) . "/../../inc/utils/AccessUtils.class.php");
require_once(dirname(__FILE__) . "/../../inc/defines/config.php");

$FACTORIES = new Factory();

echo "Apply updates...\n";

echo "Check agent binaries... ";
Util::checkAgentVersion("python", "0.1.6");
Util::checkAgentVersion("csharp", "0.52.4");
echo "\n";

echo "Add debug output tables... ";
$FACTORIES::getAgentFactory()->getDB()->query("
CREATE TABLE `TaskDebugOutput` (
  `taskDebugOutputId` int(11) NOT NULL,
  `taskId` int(11) NOT NULL,
  `output` varchar(256) NOT NULL
) ENGINE=InnoDB");
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `TaskDebugOutput` ADD PRIMARY KEY (`taskDebugOutputId`)");
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `TaskDebugOutput` MODIFY `taskDebugOutputId` int(11) NOT NULL AUTO_INCREMENT");
echo "OK\n";

echo "Add file access groups... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `File` ADD `accessGroupId` INT NOT NULL");
$uS = new UpdateSet(File::ACCESS_GROUP_ID, AccessUtils::getOrCreateDefaultAccessGroup()->getId());
$FACTORIES::getFileFactory()->massUpdate(array($FACTORIES::UPDATE => $uS));
echo "OK\n";

echo "Update complete!\n";
