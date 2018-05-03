<?php

use DBA\AgentBinary;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/../../inc/load.php");

echo "Apply updates...\n";

echo "Check agent binaries... ";
$qF = new QueryFilter(AgentBinary::TYPE, "python", "=");
$binary = $FACTORIES::getAgentBinaryFactory()->filter(array($FACTORIES::FILTER => $qF), true);
if ($binary != null) {
  if (Util::versionComparison($binary->getVersion(), "0.1.4") == 1) {
    echo "update version... ";
    $binary->setVersion("0.1.4");
    $FACTORIES::getAgentBinaryFactory()->update($binary);
    echo "OK";
  }
}
echo "\n";

echo "Update complete!\n";
