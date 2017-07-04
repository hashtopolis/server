<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 06.03.17
 * Time: 12:16
 */

use DBA\AgentBinary;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/../../inc/load.php");

echo "Apply updates...\n";

echo "Add new config... ";
$FACTORIES::getAgentFactory()->getDB()->query("INSERT INTO `Config` (`configId`, `item`, `value`) VALUES (15, 'disptolerance', '20'), (16, 'batchSize', '10000')");
echo "OK\n";

echo "Change zap table... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `Zap` CHANGE `agentId` `agentId` INT(11) NULL");
echo "OK\n";

echo "Add hash index... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `Hash` ADD INDEX(`hash`);");
echo "OK\n";

echo "Increase hash length... ";
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `Hash` CHANGE `hash` `hash` VARCHAR(1024) NOT NULL;");
echo "OK\n";

echo "Add Yubikey... ";
$FACTORIES::getAgentFactory()->getDB()->query("INSERT INTO `Config` (`configId`, `item`, `value`) VALUES (17, 'yubikey_id', '')");
$FACTORIES::getAgentFactory()->getDB()->query("INSERT INTO `Config` (`configId`, `item`, `value`) VALUES (18, 'yubikey_key', '')");
$FACTORIES::getAgentFactory()->getDB()->query("INSERT INTO `Config` (`configId`, `item`, `value`) VALUES (19, 'yubikey_url', 'https://api.yubico.com/wsapi/2.0/verify')");
$FACTORIES::getAgentFactory()->getDB()->query("ALTER TABLE `User` ADD yubikey INT(1) NOT NULL, ADD otp1 VARCHAR(50) NOT NULL, ADD otp2 VARCHAR(50) NOT NULL, ADD otp3 VARCHAR(50) NOT NULL, ADD otp4 VARCHAR(50) NOT NULL;");
echo "OK\n";

echo "Check csharp binary... ";
$qF = new QueryFilter(AgentBinary::TYPE, "csharp", "=");
$binary = $FACTORIES::getAgentBinaryFactory()->filter(array($FACTORIES::FILTER => $qF), true);
if ($binary != null) {
  if (Util::versionComparison($binary->getVersion(), "0.43.13") == 1) {
    echo "update version... ";
    $binary->setVersion("0.43.4");
    $FACTORIES::getAgentBinaryFactory()->update($binary);
    echo "OK";
  }
}
echo "\n";

echo "Update complete!\n";
