<?php /** @noinspection SqlNoDataSourceInspection */

use Hashtopolis\dba\models\ConfigSection;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Config;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\dba\models\AgentBinary;
use Composer\Semver\Comparator;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DServerLog;

if (!isset($TEST)) {
  /** @noinspection PhpIncludeInspection */
  require_once(dirname(__FILE__) . "/../../inc/db.php");
  require_once(dirname(__FILE__) . "/../../dba/init.php");
  require_once(dirname(__FILE__) . "/../../inc/Util.php");
  require_once(dirname(__FILE__) . "/../../inc/utils/AccessUtils.php");
}
require_once(dirname(__FILE__) . "/../../inc/defines/DConfig.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DLogEntry.php");

echo "NOTICE: After this update the Peppers for Encryption.class.php and CSRF.class.php are stored in the new config file. So if you didn't merge them you would have to put the old pepper values into inc/conf.php to make the log in working again. Read more on this on the specific release information.\n";

echo "Apply updates...\n";

if (!isset($TEST)) {
  echo "Moving db config... ";
  rename(dirname(__FILE__) . "/../../inc/db.php", dirname(__FILE__) . "/../../inc/conf.php");
  file_put_contents(dirname(__FILE__) . "/../../inc/conf.php", "\n" . '$PEPPER = ["__PEPPER1__","__PEPPER2__","__PEPPER3__","__CSRF__"];' . "\n", FILE_APPEND);
  echo "OK\n";

  echo "Check agent binaries... ";
  $qF = new QueryFilter("type", "python", "=");
  $binary = Factory::getAgentBinaryFactory()->filter([Factory::FILTER => $qF], true);
  if ($binary != null) {
    if (Comparator::lessThan($binary->getVersion(), "0.3.0")) {
      echo "update python version... ";
      $binary->setVersion("0.3.0");
      Factory::getAgentBinaryFactory()->update($binary);
      echo "OK";
    }
  }
  echo "\n";
}

echo "Add new config section for notifications... ";
$configSection = new ConfigSection(7, 'Notifications');
Factory::getConfigSectionFactory()->save($configSection);

// moving telegram bot token setting
$qF = new QueryFilter(Config::ITEM, DConfig::TELEGRAM_BOT_TOKEN, "=");
$config = Factory::getConfigFactory()->filter([Factory::FILTER => $qF], true);
if ($config != null) { // just to be sure we check
  $config->setConfigSectionId(7);
  Factory::getConfigFactory()->update($config);
}

$config = new Config(null, 7, DConfig::NOTIFICATIONS_PROXY_SERVER, '');
Factory::getConfigFactory()->save($config);
$config = new Config(null, 7, DConfig::NOTIFICATIONS_PROXY_PORT, '');
Factory::getConfigFactory()->save($config);
$config = new Config(null, 7, DConfig::NOTIFICATIONS_PROXY_TYPE, 'HTTP');
Factory::getConfigFactory()->save($config);
$config = new Config(null, 7, DConfig::NOTIFICATIONS_PROXY_ENABLE, '0');
Factory::getConfigFactory()->save($config);
echo "OK\n";

echo "Updating config values... ";
$config = new Config(null, 1, DConfig::DISABLE_TRIMMING, '0');
Factory::getConfigFactory()->save($config);
$config = new Config(null, 1, DConfig::PRIORITY_0_START, '0');
Factory::getConfigFactory()->save($config);
$config = new Config(null, 4, DConfig::SERVER_LOG_LEVEL, DServerLog::INFO);
Factory::getConfigFactory()->save($config);
$config = new Config(null, 4, DConfig::MAX_SESSION_LENGTH, '48');
Factory::getConfigFactory()->save($config);
echo "OK\n";

echo "Updating Hash tables... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Hash` ADD `crackPos` BIGINT NOT NULL");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `HashBinary` ADD `crackPos` BIGINT NOT NULL");
echo "OK\n";

echo "Adding Health Check tables... ";
Factory::getAgentFactory()->getDB()->query("
CREATE TABLE `HealthCheck` (
  `healthCheckId` int(11) NOT NULL,
  `time` bigint(20) NOT NULL,
  `status` int(11) NOT NULL,
  `checkType` int(11) NOT NULL,
  `hashtypeId` int(11) NOT NULL,
  `crackerBinaryId` int(11) NOT NULL,
  `expectedCracks` int(11) NOT NULL,
  `attackCmd` VARCHAR(256) NOT NULL
) ENGINE=InnoDB"
);
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `HealthCheck` ADD PRIMARY KEY (`healthCheckId`)");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `HealthCheck` MODIFY `healthCheckId` int(11) NOT NULL AUTO_INCREMENT");
Factory::getAgentFactory()->getDB()->query("
CREATE TABLE `HealthCheckAgent` (
  `healthCheckAgentId` int(11) NOT NULL,
  `healthCheckId` int(11) NOT NULL,
  `agentId` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `cracked` int(11) NOT NULL,
  `numGpus` int(11) NOT NULL,
  `start` bigint(20) NOT NULL,
  `end` bigint(20) NOT NULL,
  `errors` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB"
);
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `HealthCheckAgent` ADD PRIMARY KEY (`healthCheckAgentId`)");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `HealthCheckAgent` MODIFY `healthCheckAgentId` int(11) NOT NULL AUTO_INCREMENT");
echo "OK\n";

echo "Add hashlist notes... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Hashlist` ADD `notes` TEXT");
echo "OK\n";

echo "Add slow hash flag to hash types... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `HashType` ADD `isSlowHash` TINYINT(4) NOT NULL");
$slow = [2100, 2500, 2501, 5200, 6211, 6212, 6213, 6221, 6222, 6223, 6231, 6232, 6233, 6241, 6242, 6243, 6400, 6500, 6600, 6700, 6800, 7100, 7200, 8200, 8800, 9100, 9200, 9400, 9500, 9600, 10000, 10900, 11300, 11900, 12000, 12001, 12100, 12200, 12300, 12700, 12800, 12900, 13000, 13600, 13711, 13712, 13713, 13721, 13722, 13723, 13731, 13732, 13733, 13741, 13742, 13743, 13751, 13752, 13753, 13761, 13762, 13763, 14600, 14700, 14800, 15100, 15300, 15600, 15900, 16200, 16300, 16700, 16800, 16801, 16900];
Factory::getAgentFactory()->getDB()->query("UPDATE `HashType` SET isSlowHash=1 WHERE hashTypeId IN (" . implode(",", $slow) . ")");
echo "OK\n";

echo "Add new hashcat algorithms... ";
$hashtypes = [
  new HashType(17300, 'SHA3-224', 0, 0),
  new HashType(17400, 'SHA3-256', 0, 0),
  new HashType(17500, 'SHA3-384', 0, 0),
  new HashType(17600, 'SHA3-512', 0, 0),
  new HashType(17700, 'Keccak-224', 0, 0),
  new HashType(17800, 'Keccak-256', 0, 0),
  new HashType(17900, 'Keccak-384', 0, 0),
  new HashType(18000, 'Keccak-512', 0, 0),
  new HashType(18100, 'TOTP (HMAC-SHA1)', 1, 0)
];
foreach ($hashtypes as $hashtype) {
  $check = Factory::getHashTypeFactory()->get($hashtype->getId());
  if ($check === null) {
    Factory::getHashTypeFactory()->save($hashtype);
  }
}
echo "OK\n";

echo "Update complete!\n";
