<?php
use DBA\ConfigSection;
use DBA\Factory;
use DBA\Config;
use DBA\QueryFilter;

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

echo "Add new config section for noticiations... ";
$configSection = new ConfigSection(7, 'Notifications');
Factory::getConfigSectionFactory()->save($configSection);

// moving telegram bot token setting
$qF = new QueryFilter(Config::ITEM, DConfig::TELEGRAM_BOT_TOKEN, "=");
$config = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
if($config != null){ // just to be sure we check
  $config->setConfigSectionId(7);
  Factory::getConfigFactory()->update($config);
}

//$config = new Config(null, 7, DConfig::TELEGRAM_);
echo "OK\n";

echo "Update complete!\n";
