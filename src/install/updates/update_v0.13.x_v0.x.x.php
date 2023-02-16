<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Factory;
use DBA\HashType;

if (!isset($TEST)) {
  /** @noinspection PhpIncludeInspection */
  require_once(dirname(__FILE__) . "/../../inc/conf.php");
  require_once(dirname(__FILE__) . "/../../inc/info.php");
  require_once(dirname(__FILE__) . "/../../dba/init.php");
  require_once(dirname(__FILE__) . "/../../inc/Util.class.php");
}
require_once(dirname(__FILE__) . "/../../inc/defines/config.php");
require_once(dirname(__FILE__) . "/../../inc/defines/log.php");

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

