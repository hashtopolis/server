<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\AgentBinary;
use DBA\QueryFilter;
use DBA\Factory;
use Composer\Semver\Comparator;

require_once(dirname(__FILE__) . "/../../inc/startup/load.php");

echo "Apply updates...\n";

echo "Update Zap table... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Zap` CHANGE `agentId` `agentId` INT(11) NULL");
echo "OK\n";

echo "Check csharp binary... ";
$qF = new QueryFilter("type", "csharp", "=");
$binary = Factory::getAgentBinaryFactory()->filter([Factory::FILTER => $qF], true);
if ($binary != null) {
  if (Comparator::lessThan($binary->getVersion(), "0.43.13")) {
    echo "update version... ";
    $binary->setVersion("0.43.13");
    Factory::getAgentBinaryFactory()->update($binary);
    echo "OK";
  }
}
echo "\n";

echo "Update complete!\n";
