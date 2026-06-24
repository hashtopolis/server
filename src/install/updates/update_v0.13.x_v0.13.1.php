<?php /** @noinspection SqlNoDataSourceInspection */

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\inc\Util;

if (!isset($TEST)) {
  require_once(dirname(__FILE__) . "/../../inc/StartupConfig.php");
  require_once(dirname(__FILE__) . "/../../dba/init.php");
  require_once(dirname(__FILE__) . "/../../inc/Util.php");
}
require_once(dirname(__FILE__) . "/../../inc/defines/DConfig.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DLogEntry.php");

if (!isset($PRESENT["v0.13.x_hashTypes_1"])) {
  $hashtypes = [
    new HashType(30, 'md5(utf16le($pass).$salt)', 1, 0),
    new HashType(40, 'md5($salt.utf16le($pass))', 1, 0),
    new HashType(130, 'sha1(utf16le($pass).$salt)', 1, 0),
    new HashType(140, 'sha1($salt.utf16le($pass))', 1, 0),
    new HashType(1430, 'sha256(utf16le($pass).$salt)', 1, 0),
    new HashType(1440, 'sha256($salt.utf16le($pass))', 1, 0),
    new HashType(1730, 'sha512(utf16le($pass).$salt)', 1, 0),
    new HashType(1740, 'sha512($salt.utf16le($pass))', 1, 0),
  ];
  foreach ($hashtypes as $hashtype) {
    $check = Factory::getHashTypeFactory()->get($hashtype->getId());
    if ($check === null) {
      continue;
    }
    if ($check->getDescription() == str_replace("utf16le", "unicode", $hashtype->getDescription())) {
      # the description still uses the old notation of "unicode" instead of "utf16le", so let's change it
      $check->setDescription($hashtype->getDescription());
      Factory::getHashTypeFactory()->update($check);
    }
  }
  $EXECUTED["v0.13.x_hashTypes_1"] = true;
}

if (!isset($PRESENT["v0.13.x_agentBinaries"])) {
  Util::checkAgentVersionLegacy("python", "0.7.1", true);
  $EXECUTED["v0.13.x_agentBinaries"] = true;
}
if (!isset($PRESENT["v0.13.x_hash_length"])) {
  $conn = Factory::getAgentFactory()->getDB();

  $hash_column_length = $conn->query("SELECT hash FROM Hash")->getColumnMeta(0)['len'];
  $zap_column_length = $conn->query("SELECT hash FROM Zap")->getColumnMeta(0)['len'];
  // TEXT == 65535
  if ($hash_column_length <= 65535) {
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Hash` MODIFY `hash` MEDIUMTEXT NOT NULL;");
  }
  if ($zap_column_length <= 65535) {
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `Zap` MODIFY `hash` MEDIUMTEXT NOT NULL;");
  }
  $EXECUTED["v0.13.x_hash_length"] = true;
}
