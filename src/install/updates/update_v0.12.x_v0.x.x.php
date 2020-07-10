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

