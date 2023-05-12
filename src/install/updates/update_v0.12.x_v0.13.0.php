<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Factory;
use DBA\HashType;

if (!isset($TEST)) {
  require_once(dirname(__FILE__) . "/../../inc/confv2.php");
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

if (!isset($PRESENT["v0.12.x_hashTypes_4"])) {
  $hashtypes = [
    new HashType(26200, 'OpenEdge Progress Encode', 0, 0),
    new HashType(26300, 'FortiGate256 (FortiOS256)', 0, 0),
    new HashType(26401, 'AES-128-ECB NOKDF (PT = $salt, key = $pass)', 0, 0),
    new HashType(26402, 'AES-192-ECB NOKDF (PT = $salt, key = $pass)', 0, 0),
    new HashType(26403, 'AES-256-ECB NOKDF (PT = $salt, key = $pass)', 0, 0),
    new HashType(26500, 'iPhone passcode (UID key + System Keybag)', 0, 0),
    new HashType(26600, 'MetaMask Wallet', 0, 1),
    new HashType(26700, 'SNMPv3 HMAC-SHA224-128', 0, 0),
    new HashType(26800, 'SNMPv3 HMAC-SHA256-192', 0, 0),
    new HashType(26900, 'SNMPv3 HMAC-SHA384-256', 0, 0),
    new HashType(27000, 'NetNTLMv1 / NetNTLMv1+ESS (NT)', 0, 0),
    new HashType(27100, 'NetNTLMv2 (NT)', 0, 0),
    new HashType(27200, 'Ruby on Rails Restful Auth (one round, no sitekey)', 1, 0),
    new HashType(27300, 'SNMPv3 HMAC-SHA512-384', 0, 0),
    new HashType(27400, 'VMware VMX (PBKDF2-HMAC-SHA1 + AES-256-CBC)', 0, 0),
    new HashType(27500, 'VirtualBox (PBKDF2-HMAC-SHA256 & AES-128-XTS)', 0, 1),
    new HashType(27600, 'VirtualBox (PBKDF2-HMAC-SHA256 & AES-256-XTS)', 0, 1),
    new HashType(27700, 'MultiBit Classic .wallet (scrypt)', 0, 0),
    new HashType(27800, 'MurmurHash3', 1, 0),
    new HashType(27900, 'CRC32C', 1, 0),
    new HashType(28000, 'CRC64Jones', 1, 0),
    new HashType(28100, 'Windows Hello PIN/Password', 0, 1),
    new HashType(28200, 'Exodus Desktop Wallet (scrypt)', 0, 0),
    new HashType(28300, 'Teamspeak 3 (channel hash)', 0, 0),
    new HashType(28400, 'bcrypt(sha512($pass)) / bcryptsha512', 0, 0),
    new HashType(28600, 'PostgreSQL SCRAM-SHA-256', 0, 1),
    new HashType(28700, 'Amazon AWS4-HMAC-SHA256', 0, 0),
    new HashType(28800, 'Kerberos 5, etype 17, DB', 0, 1),
    new HashType(28900, 'Kerberos 5, etype 18, DB', 0, 1),
  ];
  foreach ($hashtypes as $hashtype) {
    $check = Factory::getHashTypeFactory()->get($hashtype->getId());
    if ($check === null) {
      Factory::getHashTypeFactory()->save($hashtype);
    }
  }
  $EXECUTED["v0.12.x_hashTypes_4"] = true;
}

if (!isset($PRESENT["v0.12.x_hashTypes_5"])) {
  $hashtypes = [
    new HashType(70, 'md5(utf16le($pass))', 0, 0),
    new HashType(170, 'sha1(utf16le($pass))', 0, 0),
    new HashType(610, 'BLAKE2b-512($pass.$salt)', 1, 0),
    new HashType(620, 'BLAKE2b-512($salt.$pass)', 1, 0),
    new HashType(1470, 'sha256(utf16le($pass))', 0, 0),
    new HashType(1770, 'sha512(utf16le($pass))', 0, 0),
    new HashType(3500, 'md5(md5(md5($pass)))', 0, 0),
    new HashType(4410, 'md5(sha1($pass).$salt)', 1, 0),
    new HashType(10810, 'sha384($pass.$salt)', 1, 0),
    new HashType(10820, 'sha384($salt.$pass)', 1, 0),
    new HashType(10830, 'sha384(utf16le($pass).$salt)', 1, 0),
    new HashType(10840, 'sha384($salt.utf16le($pass))', 1, 0),
    new HashType(10870, 'sha384(utf16le($pass))', 0, 0),
    new HashType(10901, 'RedHat 389-DS LDAP (PBKDF2-HMAC-SHA256)', 0, 1),
    new HashType(13781, 'VeraCrypt Streebog-512 + XTS 512 bit + boot-mode (legacy)', 0, 1),
    new HashType(13782, 'VeraCrypt Streebog-512 + XTS 1024 bit + boot-mode (legacy)', 0, 1),
    new HashType(13783, 'VeraCrypt Streebog-512 + XTS 1536 bit + boot-mode (legacy)', 0, 1),
    new HashType(14500, 'Linux Kernel Crypto API (2.4)', 0, 0),
    new HashType(15310, 'DPAPI masterkey file v1 (context 3)', 0, 1),
    new HashType(15910, 'DPAPI masterkey file v2 (context 3)', 0, 1),
    new HashType(17010, 'GPG (AES-128/AES-256 (SHA-1($pass)))', 0, 1),
    new HashType(20720, 'sha256($salt.sha256($pass))', 1, 0),
    new HashType(21420, 'sha256($salt.sha256_bin($pass))', 1, 0),
    new HashType(25000, 'SNMPv3 HMAC-MD5-96/HMAC-SHA1-96', 0, 1),
    new HashType(25100, 'SNMPv3 HMAC-MD5-96', 0, 1),
    new HashType(25200, 'SNMPv3 HMAC-SHA1-96', 0, 1),
    new HashType(25600, 'bcrypt(md5($pass)) / bcryptmd5', 0, 1),
    new HashType(25700, 'MurmurHash', 1, 0),
    new HashType(25800, 'bcrypt(sha1($pass)) / bcryptsha1', 0, 1),
    new HashType(28501, 'Bitcoin WIF private key (P2PKH), compressed', 0, 0),
    new HashType(28502, 'Bitcoin WIF private key (P2PKH), uncompressed', 0, 0),
    new HashType(28503, 'Bitcoin WIF private key (P2WPKH, Bech32), compressed', 0, 0),
    new HashType(28504, 'Bitcoin WIF private key (P2WPKH, Bech32), uncompressed', 0, 0),
    new HashType(28505, 'Bitcoin WIF private key (P2SH(P2WPKH)), compressed', 0, 0),
    new HashType(28506, 'Bitcoin WIF private key (P2SH(P2WPKH)), uncompressed', 0, 0),
    new HashType(29000, 'sha1($salt.sha1(utf16le($username).\':\'.utf16le($pass)))', 0, 0),
    new HashType(29100, 'Flask Session Cookie ($salt.$salt.$pass)', 0, 0),
    new HashType(29200, 'Radmin3', 0, 0),
    new HashType(29311, 'TrueCrypt RIPEMD160 + XTS 512 bit', 0, 0),
    new HashType(29312, 'TrueCrypt RIPEMD160 + XTS 1024 bit', 0, 0),
    new HashType(29313, 'TrueCrypt RIPEMD160 + XTS 1536 bit', 0, 0),
    new HashType(29321, 'TrueCrypt SHA512 + XTS 512 bit', 0, 0),
    new HashType(29322, 'TrueCrypt SHA512 + XTS 1024', 0, 0),
    new HashType(29323, 'TrueCrypt SHA512 + XTS 1536 bit', 0, 0),
    new HashType(29331, 'TrueCrypt Whirlpool + XTS 512 bit', 0, 0),
    new HashType(29332, 'TrueCrypt Whirlpool + XTS 1024 bit', 0, 0),
    new HashType(29333, 'TrueCrypt Whirlpool + XTS 1536 bit', 0, 0),
    new HashType(29341, 'TrueCrypt RIPEMD160 + XTS 512 bit + boot-mode', 0, 0),
    new HashType(29342, 'TrueCrypt RIPEMD160 + XTS 1024 bit + boot-mode', 0, 0),
    new HashType(29343, 'TrueCrypt RIPEMD160 + XTS 1536 bit + boot-mode', 0, 0),
    new HashType(29411, 'VeraCrypt RIPEMD160 + XTS 512 bit', 0, 0),
    new HashType(29412, 'VeraCrypt RIPEMD160 + XTS 1024 bit', 0, 0),
    new HashType(29413, 'VeraCrypt RIPEMD160 + XTS 1536 bit', 0, 0),
    new HashType(29421, 'VeraCrypt SHA512 + XTS 512 bit', 0, 0),
    new HashType(29422, 'VeraCrypt SHA512 + XTS 1024 bit', 0, 0),
    new HashType(29423, 'VeraCrypt SHA512 + XTS 1536 bit', 0, 0),
    new HashType(29431, 'VeraCrypt Whirlpool + XTS 512 bit', 0, 0),
    new HashType(29432, 'VeraCrypt Whirlpool + XTS 1024 bit', 0, 0),
    new HashType(29433, 'VeraCrypt Whirlpool + XTS 1536 bit', 0, 0),
    new HashType(29441, 'VeraCrypt RIPEMD160 + XTS 512 bit + boot-mode', 0, 0),
    new HashType(29442, 'VeraCrypt RIPEMD160 + XTS 1024 bit + boot-mode', 0, 0),
    new HashType(29443, 'VeraCrypt RIPEMD160 + XTS 1536 bit + boot-mode', 0, 0),
    new HashType(29451, 'VeraCrypt SHA256 + XTS 512 bit', 0, 0),
    new HashType(29452, 'VeraCrypt SHA256 + XTS 1024 bit', 0, 0),
    new HashType(29453, 'VeraCrypt SHA256 + XTS 1536 bit', 0, 0),
    new HashType(29461, 'VeraCrypt SHA256 + XTS 512 bit + boot-mode', 0, 0),
    new HashType(29462, 'VeraCrypt SHA256 + XTS 1024 bit + boot-mode', 0, 0),
    new HashType(29463, 'VeraCrypt SHA256 + XTS 1536 bit + boot-mode', 0, 0),
    new HashType(29471, 'VeraCrypt Streebog-512 + XTS 512 bit', 0, 0),
    new HashType(29472, 'VeraCrypt Streebog-512 + XTS 1024 bit', 0, 0),
    new HashType(29473, 'VeraCrypt Streebog-512 + XTS 1536 bit', 0, 0),
    new HashType(29481, 'VeraCrypt Streebog-512 + XTS 512 bit + boot-mode', 0, 0),
    new HashType(29482, 'VeraCrypt Streebog-512 + XTS 1024 bit + boot-mode', 0, 0),
    new HashType(29483, 'VeraCrypt Streebog-512 + XTS 1536 bit + boot-mode', 0, 0),
    new HashType(29511, 'LUKS v1 SHA-1 + AES', 0, 1),
    new HashType(29512, 'LUKS v1 SHA-1 + Serpent', 0, 1),
    new HashType(29513, 'LUKS v1 SHA-1 + Twofish', 0, 1),
    new HashType(29521, 'LUKS v1 SHA-256 + AES', 0, 1),
    new HashType(29522, 'LUKS v1 SHA-256 + Serpent', 0, 1),
    new HashType(29523, 'LUKS v1 SHA-256 + Twofish', 0, 1),
    new HashType(29531, 'LUKS v1 SHA-512 + AES', 0, 1),
    new HashType(29532, 'LUKS v1 SHA-512 + Serpent', 0, 1),
    new HashType(29533, 'LUKS v1 SHA-512 + Twofish', 0, 1),
    new HashType(29541, 'LUKS v1 RIPEMD-160 + AES', 0, 1),
    new HashType(29542, 'LUKS v1 RIPEMD-160 + Serpent', 0, 1),
    new HashType(29543, 'LUKS v1 RIPEMD-160 + Twofish', 0, 1),
    new HashType(29600, 'Terra Station Wallet (AES256-CBC(PBKDF2($pass)))', 0, 1),
    new HashType(29700, 'KeePass 1 (AES/Twofish) and KeePass 2 (AES) - keyfile only mode', 0, 1),
    new HashType(30000, 'Python Werkzeug MD5 (HMAC-MD5 (key = $salt))', 0, 0),
    new HashType(30120, 'Python Werkzeug SHA256 (HMAC-SHA256 (key = $salt))', 0, 0),
  ];
  foreach ($hashtypes as $hashtype) {
    $check = Factory::getHashTypeFactory()->get($hashtype->getId());
    if ($check === null) {
      Factory::getHashTypeFactory()->save($hashtype);
    }
  }
  $EXECUTED["v0.12.x_hashTypes_5"] = true;
}

if (!isset($PRESENT["v0.12.x_agentBinaries"])) {
  Util::checkAgentVersion("python", "0.6.0.10", true);
  $EXECUTED["v0.12.x_agentBinaries"] = true;
}

if (!isset($PRESENT["v0.12.x_agentBinaries_1"])) {
  Util::checkAgentVersion("python", "0.7.0", true);
  $EXECUTED["v0.12.x_agentBinaries_1"] = true;
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

if (!isset($PRESENT["v0.12.x_agentStatValue"])) {
  Factory::getFileFactory()->getDB()->query("ALTER TABLE `AgentStat` MODIFY `value` VARCHAR(128);");
  $EXECUTED["v0.12.x_agentStatValue"] = true;
}

if (!isset($PRESENT["v0.12.x_fileLineCount"])) {
  if (!Util::databaseColumnExists("File", "lineCount")) {
    Factory::getFileFactory()->getDB()->query("ALTER TABLE `File` ADD `lineCount` BIGINT NULL;");
  }
  $EXECUTED["v0.12.x_fileLineCount"] = true;
}

if (!isset($PRESENT["v0.12.x_maxAgents_pretask_task"])) {
  if (!Util::databaseColumnExists("Pretask", "maxAgents")) {
    Factory::getFileFactory()->getDB()->query("ALTER TABLE `Pretask` ADD `maxAgents` INT(11) NOT NULL;");
  }
  if (!Util::databaseColumnExists("Task", "maxAgents")) {
    Factory::getFileFactory()->getDB()->query("ALTER TABLE `Task` ADD `maxAgents` INT(11) NOT NULL;");
  }
  $EXECUTED["v0.12.x_maxAgents_pretask_task"] = true;
}

if (!isset($PRESENT["v0.12.x_hashlist_isArchived"])) {
  if (!Util::databaseColumnExists("Hashlist", "isArchived")) {
    Factory::getFileFactory()->getDB()->query("ALTER TABLE `Hashlist` ADD `isArchived` TINYINT(4) NOT NULL;");
  }
  $EXECUTED["v0.12.x_hashlist_isArchived"] = true;
}
