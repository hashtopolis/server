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

echo "NOTICE: After this update the Peppers for Encryption.class.php and CSRF.class.php are stored in the new config file. So if you didn't merge them you would have to put the old pepper values into inc/conf.php to make the log in working again. Read more on this on the specific release information.\n";

echo "Apply updates...\n";

echo "Moving db config... ";
rename(dirname(__FILE__)."/../../inc/db.php", dirname(__FILE__)."/../../inc/conf.php");
file_put_contents(dirname(__FILE__)."/../../inc/conf.php", "\n".'$PEPPER = ["__PEPPER1__","__PEPPER2__","__PEPPER3__","__CSRF__"];'."\n", FILE_APPEND);
echo "OK\n";

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

$config = new Config(null, 7, DConfig::TELEGRAM_PROXY_SERVER, '');
Factory::getConfigFactory()->save($config);
$config = new Config(null, 7, DConfig::TELEGRAM_PROXY_PORT, '');
Factory::getConfigFactory()->save($config);
$config = new Config(null, 7, DConfig::TELEGRAM_PROXY_TYPE, 'CURLPROXY_HTTP');
Factory::getConfigFactory()->save($config);
$config = new Config(null, 7, DConfig::TELEGRAM_PROXY_ENABLE, '0');
Factory::getConfigFactory()->save($config);
echo "OK\n";

echo "Updating Hash tables... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Hash` ADD `crackPos` BIGINT NOT NULL");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `HashBinary` ADD `crackPos` BIGINT NOT NULL");
echo "OK\n";

echo "Update complete!\n";
