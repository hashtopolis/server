<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 03.03.17
 * Time: 14:54
 */

use DBA\AgentBinary;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/../../inc/load.php");

echo "Apply updates...\n";

echo "Change logEntry level length... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `LogEntry` CHANGE `level` `level` VARCHAR(20) NOT NULL");
echo "OK\n";

echo "Change plaintext error on BinaryHash... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `HashBinary` CHANGE `plaintext` `plaintext` VARCHAR(200) NULL DEFAULT NULL;");
echo "OK\n";

echo "Check csharp binary... ";
$qF = new QueryFilter(AgentBinary::TYPE, "csharp", "=");
$binary = $FACTORIES::getAgentBinaryFactory()->filter(array($FACTORIES::FILTER => $qF), true);
if ($binary != null) {
  if (Util::versionComparison($binary->getVersion(), "0.40") == 1) {
    echo "update version... ";
    $binary->setVersion("0.40");
    $FACTORIES::getAgentBinaryFactory()->update($binary);
    echo "OK";
  }
}
echo "\n";

echo "Update complete!\n";
