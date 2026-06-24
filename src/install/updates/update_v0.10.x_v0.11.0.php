<?php

use Hashtopolis\dba\models\Config;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\Util;

if (!isset($TEST)) {
  require_once(dirname(__FILE__) . "/../../inc/StartupConfig.php");
  require_once(dirname(__FILE__) . "/../../dba/init.php");
  require_once(dirname(__FILE__) . "/../../inc/Util.php");
}
require_once(dirname(__FILE__) . "/../../inc/defines/DConfig.php");
require_once(dirname(__FILE__) . "/../../inc/defines/DLogEntry.php");

if (!isset($PRESENT["v0.10.x_conf1"])) {
  $config = new Config(null, 4, DConfig::AGENT_TEMP_THRESHOLD_1, '70');
  Factory::getConfigFactory()->save($config);
  $config = new Config(null, 4, DConfig::AGENT_TEMP_THRESHOLD_2, '80');
  Factory::getConfigFactory()->save($config);
  $EXECUTED["v0.10.x_conf1"] = true;
}

if (!isset($PRESENT["v0.10.x_conf2"])) {
  $config = new Config(null, 4, DConfig::AGENT_UTIL_THRESHOLD_1, '90');
  Factory::getConfigFactory()->save($config);
  $config = new Config(null, 4, DConfig::AGENT_UTIL_THRESHOLD_2, '75');
  Factory::getConfigFactory()->save($config);
  $EXECUTED["v0.10.x_conf2"] = true;
}

if (!isset($PRESENT["v0.10.x_agentBinaries"])) {
  Util::checkAgentVersionLegacy("python", "0.5.0", true);
  $EXECUTED["v0.10.x_agentBinaries"] = true;
}

if (!isset($PRESENT["v0.10.x_hashTypes"])) {
  $hashtypes = [
    new HashType(17200, 'PKZIP (Compressed)', 0, 0),
    new HashType(17210, 'PKZIP (Uncompressed)', 0, 0),
    new HashType(17220, 'PKZIP (Compressed Multi-File)', 0, 0),
    new HashType(17225, 'PKZIP (Mixed Multi-File)', 0, 0),
    new HashType(17230, 'PKZIP (Compressed Multi-File Checksum-Only)', 0, 0),
    new HashType(18200, 'Kerberos 5 AS-REP etype 23', 0, 1),
    new HashType(18300, 'Apple File System (APFS)', 0, 1),
    new HashType(18400, 'Open Document Format (ODF) 1.2 (SHA-256, AES)', 0, 1),
    new HashType(18500, 'sha1(md5(md5($pass)))', 0, 0),
    new HashType(18600, 'Open Document Format (ODF) 1.1 (SHA-1, Blowfish)', 0, 1),
    new HashType(18700, 'Java Object hashCode()', 0, 1),
    new HashType(18800, 'Blockchain, My Wallet, Second Password (SHA256)', 0, 1),
    new HashType(18900, 'Android Backup', 0, 1),
    new HashType(19000, 'QNX /etc/shadow (MD5)', 0, 1),
    new HashType(19100, 'QNX /etc/shadow (SHA256)', 0, 1),
    new HashType(19200, 'QNX /etc/shadow (SHA512)', 0, 1),
    new HashType(19300, 'sha1($salt1.$pass.$salt2)', 0, 0),
    new HashType(19500, 'Ruby on Rails Restful-Authentication', 0, 0),
    new HashType(19600, 'Kerberos 5 TGS-REP etype 17 (AES128-CTS-HMAC-SHA1-96)', 0, 1),
    new HashType(19700, 'Kerberos 5 TGS-REP etype 18 (AES256-CTS-HMAC-SHA1-96)', 0, 1),
    new HashType(19800, 'Kerberos 5, etype 17, Pre-Auth', 0, 1),
    new HashType(19900, 'Kerberos 5, etype 18, Pre-Auth', 0, 1),
    new HashType(20011, 'DiskCryptor SHA512 + XTS 512 bit (AES) / DiskCryptor SHA512 + XTS 512 bit (Twofish) / DiskCryptor SHA512 + XTS 512 bit (Serpent)', 0, 1),
    new HashType(20012, 'DiskCryptor SHA512 + XTS 1024 bit (AES-Twofish) / DiskCryptor SHA512 + XTS 1024 bit (Twofish-Serpent) / DiskCryptor SHA512 + XTS 1024 bit (Serpent-AES)', 0, 1),
    new HashType(20013, 'DiskCryptor SHA512 + XTS 1536 bit (AES-Twofish-Serpent)', 0, 1),
    new HashType(20200, 'Python passlib pbkdf2-sha512', 0, 1),
    new HashType(20300, 'Python passlib pbkdf2-sha256', 0, 1),
    new HashType(20400, 'Python passlib pbkdf2-sha1', 0, 1),
    new HashType(20500, 'PKZIP Master Key', 0, 0),
    new HashType(20510, 'PKZIP Master Key (6 byte optimization)', 0, 0),
    new HashType(99999, 'Plaintext', 0, 0)
  ];
  foreach ($hashtypes as $hashtype) {
    $check = Factory::getHashTypeFactory()->get($hashtype->getId());
    if ($check === null) {
      Factory::getHashTypeFactory()->save($hashtype);
    }
  }
  $EXECUTED["v0.10.x_hashTypes"] = true;
}