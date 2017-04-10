<?php

use DBA\AgentBinary;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/../../inc/load.php");

echo "Apply updates...\n";

echo "Update Task table... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `Task` ADD taskType INT(11);");
echo "OK\n";

echo "Create TaskTask table... ";
$FACTORIES::getAgentFactory()->getDB()->query("CREATE TABLE `TaskTask` (`taskTaskId` int(11) NOT NULL, `taskId` int(11) NOT NULL, `subtaskId` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
echo ".";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `TaskTask` ADD PRIMARY KEY (`taskTaskId`), ADD KEY `taskId` (`taskId`), ADD KEY `subtaskId` (`subtaskId`);");
echo ".";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `TaskTask` MODIFY `taskTaskId` int(11) NOT NULL AUTO_INCREMENT;");
echo ".";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `TaskTask` ADD CONSTRAINT FOREIGN KEY (`subtaskId`) REFERENCES `Task` (`taskId`);");
echo ".";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `TaskTask` ADD CONSTRAINT FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`);");
echo "OK\n";

echo "Check csharp binary... ";
$qF = new QueryFilter(AgentBinary::TYPE, "csharp", "=");
$binary = $FACTORIES::getAgentBinaryFactory()->filter(array($FACTORIES::FILTER => $qF), true);
if($binary != null){
  if(Util::versionComparison($binary->getVersion(), "0.43.12") == 1){
    echo "update version... ";
    $binary->setVersion("0.43.12");
    $FACTORIES::getAgentBinaryFactory()->update($binary);
    echo "OK";
  }
}
echo "\n";

echo "Update complete!\n";
