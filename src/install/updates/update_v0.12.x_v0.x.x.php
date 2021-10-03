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

if (!isset($PRESENT["v0.12.x_hashTypes"])) {
  $hashtypes = [
    new HashType(2000, 'STDOUT', 0, 0),
    new HashType(2501, 'WPA-EAPOL-PMK', 0, 1),
    new HashType(4710, 'sha1(md5($pass).$salt)', 1, 0),
    new HashType(4711, 'Huawei sha1(md5($pass).$salt)', 1, 0),
    new HashType(7401, 'MySQL $A$ (sha256crypt)', 0, 0),
    new HashType(7701, 'SAP CODVN B (BCODE) from RFC_READ_TABLE', 0, 0),
    new HashType(7801, 'SAP CODVN F/G (PASSCODE) from RFC_READ_TABLE', 0, 0),
    new HashType(11750, 'HMAC-Streebog-256 (key = $pass), big-endian', 0, 0),
    new HashType(11760, 'HMAC-Streebog-256 (key = $salt), big-endian', 0, 0),
    new HashType(11850, 'HMAC-Streebog-512 (key = $pass), big-endian', 0, 0),
    new HashType(11860, 'HMAC-Streebog-512 (key = $salt), big-endian', 0, 0),
    new HashType(13741, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + boot-mode + AES', 0, 1),
    new HashType(13742, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + boot-mode + AES-Twofish', 0, 1),
    new HashType(13743, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + boot-mode + AES-Twofish-Serpent', 0, 1),
    new HashType(13761, 'VeraCrypt PBKDF2-HMAC-SHA256 + boot-mode (PIM + AES | Twofish)', 0, 1),
    new HashType(13762, 'VeraCrypt PBKDF2-HMAC-SHA256 + boot-mode + Serpent-AES', 0, 1),
    new HashType(13763, 'VeraCrypt PBKDF2-HMAC-SHA256 + boot-mode + Serpent-Twofish-AES', 0, 1),
    new HashType(13771, 'VeraCrypt Streebog-512 + XTS 512 bit', 0, 1),
    new HashType(13772, 'VeraCrypt Streebog-512 + XTS 1024 bit', 0, 1),
    new HashType(13773, 'VeraCrypt Streebog-512 + XTS 1536 bit', 0, 1),
    new HashType(22301, 'Telegram client app passcode (SHA256)', 0, 0),
    new HashType(22500, 'MultiBit Classic .key (MD5)', 0, 0),
  ];
  foreach ($hashtypes as $hashtype) {
    $check = Factory::getHashTypeFactory()->get($hashtype->getId());
    if ($check === null) {
      Factory::getHashTypeFactory()->save($hashtype);
    }
  }
  $EXECUTED["v0.12.x_hashTypes"] = true;
}

if (!isset($PRESENT["v0.12.x_hashTypes_2"])) {
  $hashtypes = [
    new HashType(22600, 'Telegram Desktop App Passcode (PBKDF2-HMAC-SHA1)', 0, 0),
    new HashType(22700, 'MultiBit HD (scrypt)', 0, 1),
    new HashType(23001, 'SecureZIP AES-128', 0, 0),
    new HashType(23002, 'SecureZIP AES-192', 0, 0),
    new HashType(23003, 'SecureZIP AES-256', 0, 0),
    new HashType(23100, 'Apple Keychain', 0, 1),
    new HashType(23200, 'XMPP SCRAM PBKDF2-SHA1', 0, 0)
  ];
  foreach ($hashtypes as $hashtype) {
    $check = Factory::getHashTypeFactory()->get($hashtype->getId());
    if ($check === null) {
      Factory::getHashTypeFactory()->save($hashtype);
    }
  }
  $EXECUTED["v0.12.x_hashTypes_2"] = true;
}

if (!isset($PRESENT["v0.12.x_hashTypes_3"])) {
  $hashtypes = [
    new HashType(23300, 'Apple iWork', 0, 1),
    new HashType(23500, 'AxCrypt 2 AES-128', 0, 1),
    new HashType(23600, 'AxCrypt 2 AES-256', 0, 1),
    new HashType(23900, 'BestCrypt v3 Volume Encryption', 0, 0),
    new HashType(23400, 'Bitwarden', 0, 1),
    new HashType(24900, 'Dahua Authentication MD5', 0, 0),
    new HashType(25900, 'KNX IP Secure - Device Authentication Code', 0, 1),
    new HashType(24100, 'MongoDB ServerKey SCRAM-SHA-1', 0, 1),
    new HashType(24200, 'MongoDB ServerKey SCRAM-SHA-256', 0, 1),
    new HashType(26000, 'Mozilla key3.db', 0, 0),
    new HashType(26100, 'Mozilla key4.db', 0, 1),
    new HashType(25300, 'MS Office 2016 - SheetProtection', 0, 1),
    new HashType(25400, 'PDF 1.4 - 1.6 (Acrobat 5 - 8) - edit password', 0, 0),
    new HashType(24410, 'PKCS#8 Private Keys (PBKDF2-HMAC-SHA1 + 3DES/AES)', 0, 1),
    new HashType(24420, 'PKCS#8 Private Keys (PBKDF2-HMAC-SHA256 + 3DES/AES)', 0, 1),
    new HashType(23700, 'RAR3-p (Uncompressed)', 0, 0),
    new HashType(23800, 'RAR3-p (Compressed)', 0, 0),
    new HashType(22911, 'RSA/DSA/EC/OPENSSH Private Keys ($0$)', 0, 0),
    new HashType(22921, 'RSA/DSA/EC/OPENSSH Private Keys ($6$)', 0, 0),
    new HashType(22931, 'RSA/DSA/EC/OPENSSH Private Keys ($1, $3$)', 0, 0),
    new HashType(22941, 'RSA/DSA/EC/OPENSSH Private Keys ($4$)', 0, 0),
    new HashType(22951, 'RSA/DSA/EC/OPENSSH Private Keys ($5$)', 0, 0),
    new HashType(21501, 'SolarWinds Orion v2', 0, 1),
    new HashType(24, 'SolarWinds Serv-U', 0, 0),
    new HashType(24600, 'SQLCipher', 0, 1),
    new HashType(25500, 'Stargazer Stellar Wallet XLM', 0, 1),
    new HashType(24700, 'Stuffit5', 0, 0),
    new HashType(24500, 'Telegram Desktop >= v2.1.14 (PBKDF2-HMAC-SHA512)', 0, 1),
    new HashType(24800, 'Umbraco HMAC-SHA1', 0, 0),
    new HashType(24300, 'sha1($salt.sha1($pass.$salt))', 1, 0),
    new HashType(4510, 'sha1(sha1($pass).$salt)', 1, 0),
  ];
  foreach ($hashtypes as $hashtype) {
    $check = Factory::getHashTypeFactory()->get($hashtype->getId());
    if ($check === null) {
      Factory::getHashTypeFactory()->save($hashtype);
    }
  }
  $EXECUTED["v0.12.x_hashTypes_3"] = true;
}

if (!isset($PRESENT["v0.12.x_agentBinaries"])) {
  Util::checkAgentVersion("python", "0.6.0.10", true);
  $EXECUTED["v0.12.x_agentBinaries"] = true;
}

if (!isset($PRESENT["v0.12.x_agentBinariesUpdateTrack"])) {
  $agentBinaries = Factory::getAgentBinaryFactory()->filter([]);
  foreach ($agentBinaries as $agentBinary) {
    if ($agentBinary->getUpdateTrack() == 'dev') {
      $agentBinary->setUpdateTrack('stable');
    }
    Factory::getAgentBinaryFactory()->update($agentBinary);
  }
  $EXECUTED["v0.12.x_agentBinariesUpdateTrack"] = true;
}

if (!isset($PRESENT["v0.12.x_TrustedVoucher"])) {
  if (!Util::databaseColumnExists("RegVoucher", "trusted")) {
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `RegVoucher` ADD `trusted` TINYINT(4) NOT NULL;");
  }
  $EXECUTED["v0.12.x_TrustedVoucher"] = true;
}
