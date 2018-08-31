<?php
use DBA\ConfigSection;
use DBA\Factory;

require_once(dirname(__FILE__) . "/../../inc/db.php");
require_once(dirname(__FILE__) . "/../../dba/init.php");
require_once(dirname(__FILE__) . "/../../inc/Util.class.php");
require_once(dirname(__FILE__) . "/../../inc/utils/AccessUtils.class.php");
require_once(dirname(__FILE__) . "/../../inc/defines/config.php");

echo "Apply updates...\n";

echo "Check agent binaries... ";
Util::checkAgentVersion("python", "0.2.0");
Util::checkAgentVersion("csharp", "0.52.4");
echo "\n";

echo "Update complete!\n";
