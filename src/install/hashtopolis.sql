SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Create tables and insert default entries
CREATE TABLE `AccessGroup` (
  `accessGroupId` INT(11)     NOT NULL,
  `groupName`     VARCHAR(50) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `AccessGroupAgent` (
  `accessGroupAgentId` INT(11) NOT NULL,
  `accessGroupId`      INT(11) NOT NULL,
  `agentId`            INT(11) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `AccessGroupUser` (
  `accessGroupUserId` INT(11) NOT NULL,
  `accessGroupId`     INT(11) NOT NULL,
  `userId`            INT(11) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `Agent` (
  `agentId`         INT(11)      NOT NULL,
  `agentName`       VARCHAR(100) NOT NULL,
  `uid`             VARCHAR(100) NOT NULL,
  `os`              INT(11)      NOT NULL,
  `devices`         TEXT         NOT NULL,
  `cmdPars`         TEXT         NOT NULL,
  `ignoreErrors`    TINYINT(4)   NOT NULL,
  `isActive`        TINYINT(4)   NOT NULL,
  `isTrusted`       TINYINT(4)   NOT NULL,
  `token`           VARCHAR(30)  NOT NULL,
  `lastAct`         VARCHAR(50)  NOT NULL,
  `lastTime`        BIGINT       NOT NULL,
  `lastIp`          VARCHAR(50)  NOT NULL,
  `userId`          INT(11)      DEFAULT NULL,
  `cpuOnly`         TINYINT(4)   NOT NULL,
  `clientSignature` VARCHAR(50)  NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `AgentBinary` (
  `agentBinaryId`    INT(11)     NOT NULL,
  `type`             VARCHAR(20) NOT NULL,
  `version`          VARCHAR(20) NOT NULL,
  `operatingSystems` VARCHAR(50) NOT NULL,
  `filename`         VARCHAR(50) NOT NULL,
  `updateTrack`      VARCHAR(20) NOT NULL,
  `updateAvailable`  VARCHAR(20) NOT NULL
) ENGINE = InnoDB;

INSERT INTO `AgentBinary` (`agentBinaryId`, `type`, `version`, `operatingSystems`, `filename`, `updateTrack`, `updateAvailable`) VALUES
  (1, 'python', '0.7.4', 'Windows, Linux, OS X', 'hashtopolis.zip', 'stable', '');

CREATE TABLE `AgentError` (
  `agentErrorId` INT(11) NOT NULL,
  `agentId`      INT(11) NOT NULL,
  `taskId`       INT(11) DEFAULT NULL,
  `time`         BIGINT  NOT NULL,
  `error`        TEXT    NOT NULL,
  `chunkId`      INT(11) NULL
) ENGINE = InnoDB;

CREATE TABLE `AgentStat` (
  `agentStatId` INT(11)     NOT NULL,
  `agentId`     INT(11)     NOT NULL,
  `statType`    INT(11)     NOT NULL,
  `time`        BIGINT      NOT NULL,
  `value`       VARCHAR(128) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `AgentZap` (
  `agentZapId` INT(11) NOT NULL,
  `agentId`    INT(11) NOT NULL,
  `lastZapId`  INT(11) NULL
) ENGINE = InnoDB;

CREATE TABLE `Assignment` (
  `assignmentId` INT(11)     NOT NULL,
  `taskId`       INT(11)     NOT NULL,
  `agentId`      INT(11)     NOT NULL,
  `benchmark`    VARCHAR(50) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `Chunk` (
  `chunkId`      INT(11)    NOT NULL,
  `taskId`       INT(11)    NOT NULL,
  `skip`         BIGINT(20) UNSIGNED NOT NULL,
  `length`       BIGINT(20) UNSIGNED NOT NULL,
  `agentId`      INT(11)    NULL,
  `dispatchTime` BIGINT     NOT NULL,
  `solveTime`    BIGINT     NOT NULL,
  `checkpoint`   BIGINT(20) UNSIGNED NOT NULL,
  `progress`     INT(11)    NULL,
  `state`        INT(11)    NOT NULL,
  `cracked`      INT(11)    NOT NULL,
  `speed`        BIGINT(20) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `Config` (
  `configId`        INT(11)     NOT NULL,
  `configSectionId` INT(11)     NOT NULL,
  `item`            VARCHAR(80) NOT NULL,
  `value`           TEXT        NOT NULL
) ENGINE = InnoDB;

INSERT INTO `Config` (`configId`, `configSectionId`, `item`, `value`) VALUES
  (1, 1, 'agenttimeout', '30'),
  (2, 1, 'benchtime', '30'),
  (3, 1, 'chunktime', '600'),
  (4, 1, 'chunktimeout', '30'),
  (9, 1, 'fieldseparator', ':'),
  (10, 1, 'hashlistAlias', '#HL#'),
  (11, 1, 'statustimer', '5'),
  (12, 4, 'timefmt', 'd.m.Y, H:i:s'),
  (13, 1, 'blacklistChars', '&|`\"\'{}()[]$<>;'),
  (14, 3, 'numLogEntries', '5000'),
  (15, 1, 'disptolerance', '20'),
  (16, 3, 'batchSize', '50000'),
  (18, 2, 'yubikey_id', ''),
  (19, 2, 'yubikey_key', ''),
  (20, 2, 'yubikey_url', 'https://api.yubico.com/wsapi/2.0/verify'),
  (22, 3, 'pagingSize', '5000'),
  (23, 3, 'plainTextMaxLength', '200'),
  (24, 3, 'hashMaxLength', '1024'),
  (25, 5, 'emailSender', 'hashtopolis@example.org'),
  (26, 5, 'emailSenderName', 'Hashtopolis'),
  (27, 5, 'baseHost', ''),
  (28, 3, 'maxHashlistSize', '5000000'),
  (29, 4, 'hideImportMasks', '1'),
  (30, 7, 'telegramBotToken', ''),
  (31, 5, 'contactEmail', ''),
  (32, 5, 'voucherDeletion', '0'),
  (33, 4, 'hashesPerPage', '1000'),
  (34, 4, 'hideIpInfo', '0'),
  (35, 1, 'defaultBenchmark', '1'),
  (36, 4, 'showTaskPerformance', '0'),
  (37, 1, 'ruleSplitSmallTasks', '0'),
  (38, 1, 'ruleSplitAlways', '0'),
  (39, 1, 'ruleSplitDisable', '1'),
  (41, 4, 'agentStatLimit', '100'),
  (42, 1, 'agentDataLifetime', '3600'),
  (43, 4, 'agentStatTension', '0'),
  (44, 6, 'multicastEnable', '0'),
  (45, 6, 'multicastDevice', 'eth0'),
  (46, 6, 'multicastTransferRateEnable', '0'),
  (47, 6, 'multicastTranserRate', '500000'),
  (48, 1, 'disableTrimming', '0'),
  (49, 5, 'serverLogLevel', '20'),
  (50, 7, 'notificationsProxyEnable', '0'),
  (60, 7, 'notificationsProxyServer', ''),
  (61, 7, 'notificationsProxyPort', '8080'),
  (62, 7, 'notificationsProxyType', 'HTTP'),
  (63, 1, 'priority0Start', '0'),
  (64, 5, 'baseUrl', ''),
  (65, 4, 'maxSessionLength', '48'),
  (66, 1, 'hashcatBrainEnable', '0'),
  (67, 1, 'hashcatBrainHost', ''),
  (68, 1, 'hashcatBrainPort', '0'),
  (69, 1, 'hashcatBrainPass', ''),
  (70, 1, 'hashlistImportCheck', '0'),
  (71, 5, 'allowDeregister', '0'),
  (72, 4, 'agentTempThreshold1', '70'),
  (73, 4, 'agentTempThreshold2', '80'),
  (74, 4, 'agentUtilThreshold1', '90'),
  (75, 4, 'agentUtilThreshold2', '75'),
  (76, 3, 'uApiSendTaskIsComplete', '0'),
  (77, 1, 'hcErrorIgnore', 'DeviceGetFanSpeed');

CREATE TABLE `ConfigSection` (
  `configSectionId` INT(11)      NOT NULL,
  `sectionName`     VARCHAR(100) NOT NULL
) ENGINE = InnoDB;

INSERT INTO `ConfigSection` (`configSectionId`, `sectionName`) VALUES
  (1, 'Cracking/Tasks'),
  (2, 'Yubikey'),
  (3, 'Finetuning'),
  (4, 'UI'),
  (5, 'Server'),
  (6, 'Multicast'),
  (7, 'Notifications');

CREATE TABLE `CrackerBinary` (
  `crackerBinaryId`     INT(11)      NOT NULL,
  `crackerBinaryTypeId` INT(11)      NOT NULL,
  `version`             VARCHAR(20)  NOT NULL,
  `downloadUrl`         VARCHAR(150) NOT NULL,
  `binaryName`          VARCHAR(50)  NOT NULL
) ENGINE = InnoDB;

INSERT INTO `CrackerBinary` (`crackerBinaryId`, `crackerBinaryTypeId`, `version`, `downloadUrl`, `binaryName`) VALUES
  (1, 1, '7.1.2', 'https://hashcat.net/files/hashcat-7.1.2.7z', 'hashcat');

CREATE TABLE `CrackerBinaryType` (
  `crackerBinaryTypeId` INT(11)     NOT NULL,
  `typeName`            VARCHAR(30) NOT NULL,
  `isChunkingAvailable` TINYINT(4)  NOT NULL
) ENGINE = InnoDB;

INSERT INTO `CrackerBinaryType` (`crackerBinaryTypeId`, `typeName`, `isChunkingAvailable`) VALUES
  (1, 'hashcat', 1);

CREATE TABLE `File` (
  `fileId`        INT(11)      NOT NULL,
  `filename`      VARCHAR(100) NOT NULL,
  `size`          BIGINT(20)   NOT NULL,
  `isSecret`      TINYINT(4)   NOT NULL,
  `fileType`      INT(11)      NOT NULL,
  `accessGroupId` INT(11)      NOT NULL,
  `lineCount`     BIGINT(20)   DEFAULT NULL
) ENGINE = InnoDB;

CREATE TABLE `FilePretask` (
  `filePretaskId` INT(11) NOT NULL,
  `fileId`        INT(11) NOT NULL,
  `pretaskId`     INT(11) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `FileTask` (
  `fileTaskId` INT(11) NOT NULL,
  `fileId`     INT(11) NOT NULL,
  `taskId`     INT(11) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `FileDelete` (
  `fileDeleteId` INT(11)      NOT NULL,
  `filename`     VARCHAR(256) NOT NULL,
  `time`         BIGINT       NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `Hash` (
  `hashId`      INT(11)      NOT NULL,
  `hashlistId`  INT(11)      NOT NULL,
  `hash`        MEDIUMTEXT   NOT NULL,
  `salt`        VARCHAR(256) DEFAULT NULL,
  `plaintext`   VARCHAR(256) DEFAULT NULL,
  `timeCracked` BIGINT       DEFAULT NULL,
  `chunkId`     INT(11)      DEFAULT NULL,
  `isCracked`   TINYINT(4)   NOT NULL,
  `crackPos`    BIGINT       NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `HashBinary` (
  `hashBinaryId` INT(11)       NOT NULL,
  `hashlistId`   INT(11)       NOT NULL,
  `essid`        VARCHAR(100)  NOT NULL,
  `hash`         LONGTEXT    NOT NULL,
  `plaintext`    VARCHAR(1024) DEFAULT NULL,
  `timeCracked`  BIGINT        DEFAULT NULL,
  `chunkId`      INT(11)       DEFAULT NULL,
  `isCracked`    TINYINT(4)    NOT NULL,
  `crackPos`     BIGINT        NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `Hashlist` (
  `hashlistId`    INT(11)      NOT NULL,
  `hashlistName`  VARCHAR(100) NOT NULL,
  `format`        INT(11)      NOT NULL,
  `hashTypeId`    INT(11)      NOT NULL,
  `hashCount`     INT(11)      NOT NULL,
  `saltSeparator` VARCHAR(10)  DEFAULT NULL,
  `cracked`       INT(11)      NOT NULL,
  `isSecret`      TINYINT(4)   NOT NULL,
  `hexSalt`       TINYINT(4)   NOT NULL,
  `isSalted`      TINYINT(4)   NOT NULL,
  `accessGroupId` INT(11)      NOT NULL,
  `notes`         TEXT         NOT NULL,
  `brainId`       INT(11)      NOT NULL,
  `brainFeatures` TINYINT(4)   NOT NULL,
  `isArchived`    TINYINT(4)   NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `HashlistHashlist` (
  `hashlistHashlistId` INT(11) NOT NULL,
  `parentHashlistId`   INT(11) NOT NULL,
  `hashlistId`         INT(11) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `HashType` (
  `hashTypeId`  INT(11)      NOT NULL,
  `description` VARCHAR(256) NOT NULL,
  `isSalted`    TINYINT(4)   NOT NULL,
  `isSlowHash`  TINYINT(4)   NOT NULL
) ENGINE = InnoDB;

INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`) VALUES
  (0,     'MD5', 0, 0),
  (10,    'md5($pass.$salt)', 1, 0),
  (11,    'Joomla < 2.5.18', 1, 0),
  (12,    'PostgreSQL', 1, 0),
  (20,    'md5($salt.$pass)', 1, 0),
  (21,    'osCommerce, xt:Commerce', 1, 0),
  (22,    'Juniper Netscreen/SSG (ScreenOS)', 1, 0),
  (23,    'Skype', 1, 0),
  (24,    'SolarWinds Serv-U', 0, 0),
  (30,    'md5(utf16le($pass).$salt)', 1, 0),
  (40,    'md5($salt.utf16le($pass))', 1, 0),
  (50,    'HMAC-MD5 (key = $pass)', 1, 0),
  (60,    'HMAC-MD5 (key = $salt)', 1, 0),
  (70,    'md5(utf16le($pass))', 0, 0),
  (100,   'SHA1', 0, 0),
  (101,   'nsldap, SHA-1(Base64), Netscape LDAP SHA', 0, 0),
  (110,   'sha1($pass.$salt)', 1, 0),
  (111,   'nsldaps, SSHA-1(Base64), Netscape LDAP SSHA', 0, 0),
  (112,   'Oracle S: Type (Oracle 11+)', 1, 0),
  (120,   'sha1($salt.$pass)', 1, 0),
  (121,   'SMF >= v1.1', 1, 0),
  (122,   'OS X v10.4, v10.5, v10.6', 0, 0),
  (124,   'Django (SHA-1)', 0, 0),
  (125,   'ArubaOS', 0, 0),
  (130,   'sha1(utf16le($pass).$salt)', 1, 0),
  (131,   'MSSQL(2000)', 0, 0),
  (132,   'MSSQL(2005)', 0, 0),
  (133,   'PeopleSoft', 0, 0),
  (140,   'sha1($salt.utf16le($pass))', 1, 0),
  (141,   'EPiServer 6.x < v4', 0, 0),
  (150,   'HMAC-SHA1 (key = $pass)', 1, 0),
  (160,   'HMAC-SHA1 (key = $salt)', 1, 0),
  (170,   'sha1(utf16le($pass))', 0, 0),
  (200,   'MySQL323', 0, 0),
  (300,   'MySQL4.1/MySQL5+', 0, 0),
  (400,   'phpass, MD5(Wordpress), MD5(Joomla), MD5(phpBB3)', 0, 0),
  (500,   'md5crypt, MD5(Unix), FreeBSD MD5, Cisco-IOS MD5 2', 0, 0),
  (501,   'Juniper IVE', 0, 0),
  (600,   'BLAKE2b-512', 0, 0),
  (610,   'BLAKE2b-512($pass.$salt)', 1, 0),
  (620,   'BLAKE2b-512($salt.$pass)', 1, 0),
  (900,   'MD4', 0, 0),
  (1000,  'NTLM', 0, 0),
  (1100,  'Domain Cached Credentials (DCC), MS Cache', 1, 0),
  (1300,  'SHA-224', 0, 0),
  (1310,  'sha224($pass.$salt)', 1, 0),
  (1320,  'sha224($salt.$pass)', 1, 0),
  (1400,  'SHA256', 0, 0),
  (1410,  'sha256($pass.$salt)', 1, 0),
  (1411,  'SSHA-256(Base64), LDAP {SSHA256}', 0, 0),
  (1420,  'sha256($salt.$pass)', 1, 0),
  (1421,  'hMailServer', 0, 0),
  (1430,  'sha256(utf16le($pass).$salt)', 1, 0),
  (1440,  'sha256($salt.utf16le($pass))', 1, 0),
  (1441,  'EPiServer 6.x >= v4', 0, 0),
  (1450,  'HMAC-SHA256 (key = $pass)', 1, 0),
  (1460,  'HMAC-SHA256 (key = $salt)', 1, 0),
  (1470,  'sha256(utf16le($pass))', 0, 0),
  (1500,  'descrypt, DES(Unix), Traditional DES', 0, 0),
  (1600,  'md5apr1, MD5(APR), Apache MD5', 0, 0),
  (1700,  'SHA512', 0, 0),
  (1710,  'sha512($pass.$salt)', 1, 0),
  (1711,  'SSHA-512(Base64), LDAP {SSHA512}', 0, 0),
  (1720,  'sha512($salt.$pass)', 1, 0),
  (1722,  'OS X v10.7', 0, 0),
  (1730,  'sha512(utf16le($pass).$salt)', 1, 0),
  (1731,  'MSSQL(2012), MSSQL(2014)', 0, 0),
  (1740,  'sha512($salt.utf16le($pass))', 1, 0),
  (1750,  'HMAC-SHA512 (key = $pass)', 1, 0),
  (1760,  'HMAC-SHA512 (key = $salt)', 1, 0),
  (1770,  'sha512(utf16le($pass))', 0, 0),
  (1800,  'sha512crypt, SHA512(Unix)', 0, 0),
  (2000,  'STDOUT', 0, 0),
  (2100,  'Domain Cached Credentials 2 (DCC2), MS Cache', 0, 1),
  (2400,  'Cisco-PIX MD5', 0, 0),
  (2410,  'Cisco-ASA MD5', 1, 0),
  (2500,  'WPA/WPA2', 0, 1),
  (2501,  'WPA-EAPOL-PMK', 0, 1),
  (2600,  'md5(md5($pass))', 0, 0),
  (2611,  'vBulletin < v3.8.5', 1, 0),
  (2612,  'PHPS', 0, 0),
  (2630,   'md5(md5($pass.$salt))', 1, 0),
  (2711,  'vBulletin >= v3.8.5', 1, 0),
  (2811,  'IPB2+, MyBB1.2+', 1, 0),
  (3000,  'LM', 0, 0),
  (3100,  'Oracle H: Type (Oracle 7+), DES(Oracle)', 1, 0),
  (3200,  'bcrypt, Blowfish(OpenBSD)', 0, 0),
  (3500,  'md5(md5(md5($pass)))', 0, 0),
  (3610,  'md5(md5(md5($pass)).$salt)', 1, 0),
  (3710,  'md5($salt.md5($pass))', 1, 0),
  (3711,  'Mediawiki B type', 0, 0),
  (3730,  'md5($salt1.strtoupper(md5($salt2.$pass)))', 0, 0),
  (3800,  'md5($salt.$pass.$salt)', 1, 0),
  (3910,  'md5(md5($pass).md5($salt))', 1, 0),
  (4010,  'md5($salt.md5($salt.$pass))', 1, 0),
  (4110,  'md5($salt.md5($pass.$salt))', 1, 0),
  (4300,  'md5(strtoupper(md5($pass)))', 0, 0),
  (4400,  'md5(sha1($pass))', 0, 0),
  (4410,  'md5(sha1($pass).$salt)', 1, 0),
  (4420,  'md5(sha1($pass.$salt))', 1, 0),
  (4430,  'md5(sha1($salt.$pass))', 1, 0),
  (4500,  'sha1(sha1($pass))', 0, 0),
  (4510,  'sha1(sha1($pass).$salt)', 1, 0),
  (4520,  'sha1($salt.sha1($pass))', 1, 0),
  (4521,  'Redmine Project Management Web App', 0, 0),
  (4522,  'PunBB', 0, 0),
  (4700,  'sha1(md5($pass))', 0, 0),
  (4710,  'sha1(md5($pass).$salt)', 1, 0),
  (4711,  'Huawei sha1(md5($pass).$salt)', 1, 0),
  (4800,  'MD5(Chap), iSCSI CHAP authentication', 1, 0),
  (4900,  'sha1($salt.$pass.$salt)', 1, 0),
  (5000,  'SHA-3(Keccak)', 0, 0),
  (5100,  'Half MD5', 0, 0),
  (5200,  'Password Safe v3', 0, 1),
  (5300,  'IKE-PSK MD5', 0, 0),
  (5400,  'IKE-PSK SHA1', 0, 0),
  (5500,  'NetNTLMv1-VANILLA / NetNTLMv1+ESS', 0, 0),
  (5600,  'NetNTLMv2', 0, 0),
  (5700,  'Cisco-IOS SHA256', 0, 0),
  (5720,  'Cisco-ISE Hashed Password (SHA256)', 0, 0),
  (5800,  'Samsung Android Password/PIN', 1, 0),
  (6000,  'RipeMD160', 0, 0),
  (6050,  'HMAC-RIPEMD160 (key = $pass)', 1, 0),
  (6060,  'HMAC-RIPEMD160 (key = $salt)', 1, 0),
  (6100,  'Whirlpool', 0, 0),
  (6211,  'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES/Serpent/Twofish', 0, 1),
  (6212,  'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish/Serpent-AES/Twofish-Serpent', 0, 1),
  (6213,  'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish-Serpent/Serpent-Twofish-AES', 0, 1),
  (6221,  'TrueCrypt 5.0+ SHA512 + AES/Serpent/Twofish', 0, 1),
  (6222,  'TrueCrypt 5.0+ SHA512 + AES-Twofish/Serpent-AES/Twofish-Serpent', 0, 1),
  (6223,  'TrueCrypt 5.0+ SHA512 + AES-Twofish-Serpent/Serpent-Twofish-AES', 0, 1),
  (6231,  'TrueCrypt 5.0+ Whirlpool + AES/Serpent/Twofish', 0, 1),
  (6232,  'TrueCrypt 5.0+ Whirlpool + AES-Twofish/Serpent-AES/Twofish-Serpent', 0, 1),
  (6233,  'TrueCrypt 5.0+ Whirlpool + AES-Twofish-Serpent/Serpent-Twofish-AES', 0, 1),
  (6241,  'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES/Serpent/Twofish + boot', 0, 1),
  (6242,  'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish/Serpent-AES/Twofish-Serpent + boot', 0, 1),
  (6243,  'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish-Serpent/Serpent-Twofish-AES + boot', 0, 1),
  (6300,  'AIX {smd5}', 0, 0),
  (6400,  'AIX {ssha256}', 0, 1),
  (6500,  'AIX {ssha512}', 0, 1),
  (6600,  '1Password, Agile Keychain', 0, 1),
  (6700,  'AIX {ssha1}', 0, 1),
  (6800,  'Lastpass', 1, 1),
  (6900,  'GOST R 34.11-94', 0, 0),
  (7000,  'Fortigate (FortiOS)', 0, 0),
  (7100,  'OS X v10.8 / v10.9', 0, 1),
  (7200,  'GRUB 2', 0, 1),
  (7300,  'IPMI2 RAKP HMAC-SHA1', 1, 0),
  (7350,  'IPMI2 RAKP HMAC-MD5', 0, 0),
  (7400,  'sha256crypt, SHA256(Unix)', 0, 0),
  (7401,  'MySQL $A$ (sha256crypt)', 0, 0),
  (7500,  'Kerberos 5 AS-REQ Pre-Auth', 0, 0),
  (7700,  'SAP CODVN B (BCODE)', 0, 0),
  (7701,  'SAP CODVN B (BCODE) from RFC_READ_TABLE', 0, 0),
  (7800,  'SAP CODVN F/G (PASSCODE)', 0, 0),
  (7801,  'SAP CODVN F/G (PASSCODE) from RFC_READ_TABLE', 0, 0),
  (7900,  'Drupal7', 0, 0),
  (8000,  'Sybase ASE', 0, 0),
  (8100,  'Citrix Netscaler', 0, 0),
  (8200,  '1Password, Cloud Keychain', 0, 1),
  (8300,  'DNSSEC (NSEC3)', 1, 0),
  (8400,  'WBB3, Woltlab Burning Board 3', 1, 0),
  (8500,  'RACF', 0, 0),
  (8501,  'AS/400 DES', 0, 0),
  (8600,  'Lotus Notes/Domino 5', 0, 0),
  (8700,  'Lotus Notes/Domino 6', 0, 0),
  (8800,  'Android FDE <= 4.3', 0, 1),
  (8900,  'scrypt', 1, 0),
  (9000,  'Password Safe v2', 0, 0),
  (9100,  'Lotus Notes/Domino', 0, 1),
  (9200,  'Cisco $8$', 0, 1),
  (9300,  'Cisco $9$', 0, 0),
  (9400,  'Office 2007', 0, 1),
  (9500,  'Office 2010', 0, 1),
  (9600,  'Office 2013', 0, 1),
  (9700,  'MS Office ⇐ 2003 MD5 + RC4, oldoffice$0, oldoffice$1', 0, 0),
  (9710,  'MS Office <= 2003 $0/$1, MD5 + RC4, collider #1', 0, 0),
  (9720,  'MS Office <= 2003 $0/$1, MD5 + RC4, collider #2', 0, 0),
  (9800,  'MS Office ⇐ 2003 SHA1 + RC4, oldoffice$3, oldoffice$4', 0, 0),
  (9810,  'MS Office <= 2003 $3, SHA1 + RC4, collider #1', 0, 0),
  (9820,  'MS Office <= 2003 $3, SHA1 + RC4, collider #2', 0, 0),
  (9900,  'Radmin2', 0, 0),
  (10000, 'Django (PBKDF2-SHA256)', 0, 1),
  (10100, 'SipHash', 1, 0),
  (10200, 'Cram MD5', 0, 0),
  (10300, 'SAP CODVN H (PWDSALTEDHASH) iSSHA-1', 0, 0),
  (10400, 'PDF 1.1 - 1.3 (Acrobat 2 - 4)', 0, 0),
  (10410, 'PDF 1.1 - 1.3 (Acrobat 2 - 4), collider #1', 0, 0),
  (10420, 'PDF 1.1 - 1.3 (Acrobat 2 - 4), collider #2', 0, 0),
  (10500, 'PDF 1.4 - 1.6 (Acrobat 5 - 8)', 0, 0),
  (10510, 'PDF 1.3 - 1.6 (Acrobat 4 - 8) w/ RC4-40', 0, 1),
  (10600, 'PDF 1.7 Level 3 (Acrobat 9)', 0, 0),
  (10700, 'PDF 1.7 Level 8 (Acrobat 10 - 11)', 0, 0),
  (10800, 'SHA384', 0, 0),
  (10810, 'sha384($pass.$salt)', 1, 0),
  (10820, 'sha384($salt.$pass)', 1, 0),
  (10830, 'sha384(utf16le($pass).$salt)', 1, 0),
  (10840, 'sha384($salt.utf16le($pass))', 1, 0),
  (10870, 'sha384(utf16le($pass))', 0, 0),
  (10900, 'PBKDF2-HMAC-SHA256', 0, 1),
  (10901, 'RedHat 389-DS LDAP (PBKDF2-HMAC-SHA256)', 0, 1),
  (11000, 'PrestaShop', 1, 0),
  (11100, 'PostgreSQL Challenge-Response Authentication (MD5)', 0, 0),
  (11200, 'MySQL Challenge-Response Authentication (SHA1)', 0, 0),
  (11300, 'Bitcoin/Litecoin wallet.dat', 0, 1),
  (11400, 'SIP digest authentication (MD5)', 0, 0),
  (11500, 'CRC32', 1, 0),
  (11600, '7-Zip', 0, 0),
  (11700, 'GOST R 34.11-2012 (Streebog) 256-bit', 0, 0),
  (11750, 'HMAC-Streebog-256 (key = $pass), big-endian', 0, 0),
  (11760, 'HMAC-Streebog-256 (key = $salt), big-endian', 0, 0),
  (11800, 'GOST R 34.11-2012 (Streebog) 512-bit', 0, 0),
  (11850, 'HMAC-Streebog-512 (key = $pass), big-endian', 0, 0),
  (11860, 'HMAC-Streebog-512 (key = $salt), big-endian', 0, 0),
  (11900, 'PBKDF2-HMAC-MD5', 0, 1),
  (12000, 'PBKDF2-HMAC-SHA1', 0, 1),
  (12001, 'Atlassian (PBKDF2-HMAC-SHA1)', 0, 1),
  (12100, 'PBKDF2-HMAC-SHA512', 0, 1),
  (12150, 'Apache Shiro 1 SHA-512', 0, 1),
  (12200, 'eCryptfs', 0, 1),
  (12300, 'Oracle T: Type (Oracle 12+)', 0, 1),
  (12400, 'BSDiCrypt, Extended DES', 0, 0),
  (12500, 'RAR3-hp', 0, 0),
  (12600, 'ColdFusion 10+', 1, 0),
  (12700, 'Blockchain, My Wallet', 0, 1),
  (12800, 'MS-AzureSync PBKDF2-HMAC-SHA256', 0, 1),
  (12900, 'Android FDE (Samsung DEK)', 0, 1),
  (13000, 'RAR5', 0, 1),
  (13100, 'Kerberos 5 TGS-REP etype 23', 0, 0),
  (13200, 'AxCrypt', 0, 0),
  (13300, 'AxCrypt in memory SHA1', 0, 0),
  (13400, 'Keepass 1/2 AES/Twofish with/without keyfile', 0, 0),
  (13500, 'PeopleSoft PS_TOKEN', 1, 0),
  (13600, 'WinZip', 0, 1),
  (13711, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + AES, Serpent, Twofish', 0, 1),
  (13712, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + AES-Twofish, Serpent-AES, Twofish-Serpent', 0, 1),
  (13713, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + Serpent-Twofish-AES', 0, 1),
  (13721, 'VeraCrypt PBKDF2-HMAC-SHA512 + AES, Serpent, Twofish', 0, 1),
  (13722, 'VeraCrypt PBKDF2-HMAC-SHA512 + AES-Twofish, Serpent-AES, Twofish-Serpent', 0, 1),
  (13723, 'VeraCrypt PBKDF2-HMAC-SHA512 + Serpent-Twofish-AES', 0, 1),
  (13731, 'VeraCrypt PBKDF2-HMAC-Whirlpool + AES, Serpent, Twofish', 0, 1),
  (13732, 'VeraCrypt PBKDF2-HMAC-Whirlpool + AES-Twofish, Serpent-AES, Twofish-Serpent', 0, 1),
  (13733, 'VeraCrypt PBKDF2-HMAC-Whirlpool + Serpent-Twofish-AES', 0, 1),
  (13741, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + boot-mode + AES', 0, 1),
  (13742, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + boot-mode + AES-Twofish', 0, 1),
  (13743, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + boot-mode + AES-Twofish-Serpent', 0, 1),
  (13751, 'VeraCrypt PBKDF2-HMAC-SHA256 + AES, Serpent, Twofish', 0, 1),
  (13752, 'VeraCrypt PBKDF2-HMAC-SHA256 + AES-Twofish, Serpent-AES, Twofish-Serpent', 0, 1),
  (13753, 'VeraCrypt PBKDF2-HMAC-SHA256 + Serpent-Twofish-AES', 0, 1),
  (13761, 'VeraCrypt PBKDF2-HMAC-SHA256 + boot-mode (PIM + AES | Twofish)', 0, 1),
  (13762, 'VeraCrypt PBKDF2-HMAC-SHA256 + boot-mode + Serpent-AES', 0, 1),
  (13763, 'VeraCrypt PBKDF2-HMAC-SHA256 + boot-mode + Serpent-Twofish-AES', 0, 1),
  (13771, 'VeraCrypt Streebog-512 + XTS 512 bit', 0, 1),
  (13772, 'VeraCrypt Streebog-512 + XTS 1024 bit', 0, 1),
  (13773, 'VeraCrypt Streebog-512 + XTS 1536 bit', 0, 1),
  (13781, 'VeraCrypt Streebog-512 + XTS 512 bit + boot-mode (legacy)', 0, 1),
  (13782, 'VeraCrypt Streebog-512 + XTS 1024 bit + boot-mode (legacy)', 0, 1),
  (13783, 'VeraCrypt Streebog-512 + XTS 1536 bit + boot-mode (legacy)', 0, 1),
  (13800, 'Windows 8+ phone PIN/Password', 1, 0),
  (13900, 'OpenCart', 1, 0),
  (14000, 'DES (PT = $salt, key = $pass)', 1, 0),
  (14100, '3DES (PT = $salt, key = $pass)', 1, 0),
  (14200, 'RACF KDFAES', 0, 1),
  (14400, 'sha1(CX)', 1, 0),
  (14500, 'Linux Kernel Crypto API (2.4)', 0, 0),
  (14600, 'LUKS 10', 0, 1),
  (14700, 'iTunes Backup < 10.0 11', 0, 1),
  (14800, 'iTunes Backup >= 10.0 11', 0, 1),
  (14900, 'Skip32 12', 1, 0),
  (15000, 'FileZilla Server >= 0.9.55', 1, 0),
  (15100, 'Juniper/NetBSD sha1crypt', 0, 1),
  (15200, 'Blockchain, My Wallet, V2', 0, 0),
  (15300, 'DPAPI masterkey file v1 and v2', 0, 1),
  (15310, 'DPAPI masterkey file v1 (context 3)', 0, 1),
  (15400, 'ChaCha20', 0, 0),
  (15500, 'JKS Java Key Store Private Keys (SHA1)', 0, 0),
  (15600, 'Ethereum Wallet, PBKDF2-HMAC-SHA256', 0, 1),
  (15700, 'Ethereum Wallet, SCRYPT', 0, 0),
  (15900, 'DPAPI master key file version 2 + Active Directory domain context', 0, 1),
  (15910, 'DPAPI masterkey file v2 (context 3)', 0, 1),
  (16000, 'Tripcode', 0, 0),
  (16100, 'TACACS+', 0, 0),
  (16200, 'Apple Secure Notes', 0, 1),
  (16300, 'Ethereum Pre-Sale Wallet, PBKDF2-HMAC-SHA256', 0, 1),
  (16400, 'CRAM-MD5 Dovecot', 0, 0),
  (16500, 'JWT (JSON Web Token)', 0, 0),
  (16501, 'Perl Mojolicious session cookie (HMAC-SHA256, >= v9.19)', 0, 0),
  (16600, 'Electrum Wallet (Salt-Type 1-3)', 0, 0),
  (16700, 'FileVault 2', 0, 1),
  (16800, 'WPA-PMKID-PBKDF2', 0, 1),
  (16801, 'WPA-PMKID-PMK', 0, 1),
  (16900, 'Ansible Vault', 0, 1),
  (17010, 'GPG (AES-128/AES-256 (SHA-1($pass)))', 0, 1),
  (17020, 'GPG (AES-128/AES-256 (SHA-512($pass)))', 0, 1),
  (17030, 'GPG (AES-128/AES-256 (SHA-256($pass)))', 0, 1),
  (17040, 'GPG (CAST5 (SHA-1($pass)))', 0, 1),
  (17200, 'PKZIP (Compressed)', 0, 0),
  (17210, 'PKZIP (Uncompressed)', 0, 0),
  (17220, 'PKZIP (Compressed Multi-File)', 0, 0),
  (17225, 'PKZIP (Mixed Multi-File)', 0, 0),
  (17230, 'PKZIP (Compressed Multi-File Checksum-Only)', 0, 0),
  (17300, 'SHA3-224', 0, 0),
  (17400, 'SHA3-256', 0, 0),
  (17500, 'SHA3-384', 0, 0),
  (17600, 'SHA3-512', 0, 0),
  (17700, 'Keccak-224', 0, 0),
  (17800, 'Keccak-256', 0, 0),
  (17900, 'Keccak-384', 0, 0),
  (18000, 'Keccak-512', 0, 0),
  (18100, 'TOTP (HMAC-SHA1)', 1, 0),
  (18200, 'Kerberos 5 AS-REP etype 23', 0, 1),
  (18300, 'Apple File System (APFS)', 0, 1),
  (18400, 'Open Document Format (ODF) 1.2 (SHA-256, AES)', 0, 1),
  (18500, 'sha1(md5(md5($pass)))', 0, 0),
  (18600, 'Open Document Format (ODF) 1.1 (SHA-1, Blowfish)', 0, 1),
  (18700, 'Java Object hashCode()', 0, 1),
  (18800, 'Blockchain, My Wallet, Second Password (SHA256)', 0, 1),
  (18900, 'Android Backup', 0, 1),
  (19000, 'QNX /etc/shadow (MD5)', 0, 1),
  (19100, 'QNX /etc/shadow (SHA256)', 0, 1),
  (19200, 'QNX /etc/shadow (SHA512)', 0, 1),
  (19210, 'QNX 7 /etc/shadow (SHA512)', 0, 1),
  (19300, 'sha1($salt1.$pass.$salt2)', 0, 0),
  (19500, 'Ruby on Rails Restful-Authentication', 0, 0),
  (19600, 'Kerberos 5 TGS-REP etype 17 (AES128-CTS-HMAC-SHA1-96)', 0, 1),
  (19700, 'Kerberos 5 TGS-REP etype 18 (AES256-CTS-HMAC-SHA1-96)', 0, 1),
  (19800, 'Kerberos 5, etype 17, Pre-Auth', 0, 1),
  (19900, 'Kerberos 5, etype 18, Pre-Auth', 0, 1),
  (20011, 'DiskCryptor SHA512 + XTS 512 bit (AES) / DiskCryptor SHA512 + XTS 512 bit (Twofish) / DiskCryptor SHA512 + XTS 512 bit (Serpent)', 0, 1),
  (20012, 'DiskCryptor SHA512 + XTS 1024 bit (AES-Twofish) / DiskCryptor SHA512 + XTS 1024 bit (Twofish-Serpent) / DiskCryptor SHA512 + XTS 1024 bit (Serpent-AES)', 0, 1),
  (20013, 'DiskCryptor SHA512 + XTS 1536 bit (AES-Twofish-Serpent)', 0, 1),
  (20200, 'Python passlib pbkdf2-sha512', 0, 1),
  (20300, 'Python passlib pbkdf2-sha256', 0, 1),
  (20400, 'Python passlib pbkdf2-sha1', 0, 0),
  (20500, 'PKZIP Master Key', 0, 0),
  (20510, 'PKZIP Master Key (6 byte optimization)', 0, 0),
  (20600, 'Oracle Transportation Management (SHA256)', 0, 0),
  (20710, 'sha256(sha256($pass).$salt)', 1, 0),
  (20711, 'AuthMe sha256', 0, 0),
  (20712, 'RSA Security Analytics / NetWitness (sha256)', 1, 0),
  (20720, 'sha256($salt.sha256($pass))', 1, 0),
  (20730, 'sha256(sha256($pass.$salt))', 1, 0),
  (20800, 'sha256(md5($pass))', 0, 0),
  (20900, 'md5(sha1($pass).md5($pass).sha1($pass))', 0, 0),
  (21000, 'BitShares v0.x - sha512(sha512_bin(pass))', 0, 0),
  (21100, 'sha1(md5($pass.$salt))', 1, 0),
  (21200, 'md5(sha1($salt).md5($pass))', 1, 0),
  (21300, 'md5($salt.sha1($salt.$pass))', 1, 0),
  (21310, 'md5($salt1.sha1($salt2.$pass))', 1, 0),
  (21400, 'sha256(sha256_bin(pass))', 0, 0),
  (21420, 'sha256($salt.sha256_bin($pass))', 1, 0),
  (21500, 'SolarWinds Orion', 0, 0),
  (21501, 'SolarWinds Orion v2', 0, 0),
  (21600, 'Web2py pbkdf2-sha512', 0, 0),
  (21700, 'Electrum Wallet (Salt-Type 4)', 0, 0),
  (21800, 'Electrum Wallet (Salt-Type 5)', 0, 0),
  (21900, 'md5(md5(md5($pass.$salt1)).$salt2)', 0, 0),
  (22000, 'WPA-PBKDF2-PMKID+EAPOL', 0, 0),
  (22001, 'WPA-PMK-PMKID+EAPOL', 0, 0),
  (22100, 'BitLocker', 0, 0),
  (22200, 'Citrix NetScaler (SHA512)', 0, 0),
  (22300, 'sha256($salt.$pass.$salt)', 1, 0),
  (22301, 'Telegram client app passcode (SHA256)', 0, 0),
  (22400, 'AES Crypt (SHA256)', 0, 0),
  (22500, 'MultiBit Classic .key (MD5)', 0, 0),
  (22600, 'Telegram Desktop App Passcode (PBKDF2-HMAC-SHA1)', 0, 0),
  (22700, 'MultiBit HD (scrypt)', 0, 1),
  (22800, 'Simpla CMS - md5($salt.$pass.md5($pass))', 1, 0),
  (22911, 'RSA/DSA/EC/OPENSSH Private Keys ($0$)', 0, 0),
  (22921, 'RSA/DSA/EC/OPENSSH Private Keys ($6$)', 0, 0),
  (22931, 'RSA/DSA/EC/OPENSSH Private Keys ($1, $3$)', 0, 0),
  (22941, 'RSA/DSA/EC/OPENSSH Private Keys ($4$)', 0, 0),
  (22951, 'RSA/DSA/EC/OPENSSH Private Keys ($5$)', 0, 0),
  (23001, 'SecureZIP AES-128', 0, 0),
  (23002, 'SecureZIP AES-192', 0, 0),
  (23003, 'SecureZIP AES-256', 0, 0),
  (23100, 'Apple Keychain', 0, 1),
  (23200, 'XMPP SCRAM PBKDF2-SHA1', 0, 0),
  (23300, 'Apple iWork', 0, 0),
  (23400, 'Bitwarden', 0, 0),
  (23500, 'AxCrypt 2 AES-128', 0, 0),
  (23600, 'AxCrypt 2 AES-256', 0, 0),
  (23700, 'RAR3-p (Uncompressed)', 0, 0),
  (23800, 'RAR3-p (Compressed)', 0, 0),
  (23900, 'BestCrypt v3 Volume Encryption', 0, 0),
  (24000, 'BestCrypt v4 Volume Encryption', 0, 1),
  (24100, 'MongoDB ServerKey SCRAM-SHA-1', 0, 0),
  (24200, 'MongoDB ServerKey SCRAM-SHA-256', 0, 0),
  (24300, 'sha1($salt.sha1($pass.$salt))', 1, 0),
  (24410, 'PKCS#8 Private Keys (PBKDF2-HMAC-SHA1 + 3DES/AES)', 0, 0),
  (24420, 'PKCS#8 Private Keys (PBKDF2-HMAC-SHA256 + 3DES/AES)', 0, 0),
  (24500, 'Telegram Desktop >= v2.1.14 (PBKDF2-HMAC-SHA512)', 0, 0),
  (24600, 'SQLCipher', 0, 0),
  (24700, 'Stuffit5', 0, 0),
  (24800, 'Umbraco HMAC-SHA1', 0, 0),
  (24900, 'Dahua Authentication MD5', 0, 0),
  (25000, 'SNMPv3 HMAC-MD5-96/HMAC-SHA1-96', 0, 1),
  (25100, 'SNMPv3 HMAC-MD5-96', 0, 1),
  (25200, 'SNMPv3 HMAC-SHA1-96', 0, 1),
  (25300, 'MS Office 2016 - SheetProtection', 0, 0),
  (25400, 'PDF 1.4 - 1.6 (Acrobat 5 - 8) - edit password', 0, 0),
  (25500, 'Stargazer Stellar Wallet XLM', 0, 0),
  (25600, 'bcrypt(md5($pass)) / bcryptmd5', 0, 1),
  (25700, 'MurmurHash', 1, 0),
  (25800, 'bcrypt(sha1($pass)) / bcryptsha1', 0, 1),
  (25900, 'KNX IP Secure - Device Authentication Code', 0, 0),
  (26000, 'Mozilla key3.db', 0, 0),
  (26100, 'Mozilla key4.db', 0, 0),
  (26200, 'OpenEdge Progress Encode', 0, 0),
  (26300, 'FortiGate256 (FortiOS256)', 0, 0),
  (26401, 'AES-128-ECB NOKDF (PT = $salt, key = $pass)', 0, 0),
  (26402, 'AES-192-ECB NOKDF (PT = $salt, key = $pass)', 0, 0),
  (26403, 'AES-256-ECB NOKDF (PT = $salt, key = $pass)', 0, 0),
  (26500, 'iPhone passcode (UID key + System Keybag)', 0, 0),
  (26600, 'MetaMask Wallet', 0, 1),
  (26610, 'MetaMask Wallet (short hash, plaintext check)', 0, 1),
  (26700, 'SNMPv3 HMAC-SHA224-128', 0, 0),
  (26800, 'SNMPv3 HMAC-SHA256-192', 0, 0),
  (26900, 'SNMPv3 HMAC-SHA384-256', 0, 0),
  (27000, 'NetNTLMv1 / NetNTLMv1+ESS (NT)', 0, 0),
  (27100, 'NetNTLMv2 (NT)', 0, 0),
  (27200, 'Ruby on Rails Restful Auth (one round, no sitekey)', 1, 0),
  (27300, 'SNMPv3 HMAC-SHA512-384', 0, 0),
  (27400, 'VMware VMX (PBKDF2-HMAC-SHA1 + AES-256-CBC)', 0, 0),
  (27500, 'VirtualBox (PBKDF2-HMAC-SHA256 & AES-128-XTS)', 0, 1),
  (27600, 'VirtualBox (PBKDF2-HMAC-SHA256 & AES-256-XTS)', 0, 1),
  (27700, 'MultiBit Classic .wallet (scrypt)', 0, 0),
  (27800, 'MurmurHash3', 1, 0),
  (27900, 'CRC32C', 1, 0),
  (28000, 'CRC64Jones', 1, 0),
  (28100, 'Windows Hello PIN/Password', 0, 1),
  (28200, 'Exodus Desktop Wallet (scrypt)', 0, 0),
  (28300, 'Teamspeak 3 (channel hash)', 0, 0),
  (28400, 'bcrypt(sha512($pass)) / bcryptsha512', 0, 0),
  (28501, 'Bitcoin WIF private key (P2PKH), compressed', 0, 0),
  (28502, 'Bitcoin WIF private key (P2PKH), uncompressed', 0, 0),
  (28503, 'Bitcoin WIF private key (P2WPKH, Bech32), compressed', 0, 0),
  (28504, 'Bitcoin WIF private key (P2WPKH, Bech32), uncompressed', 0, 0),
  (28505, 'Bitcoin WIF private key (P2SH(P2WPKH)), compressed', 0, 0),
  (28506, 'Bitcoin WIF private key (P2SH(P2WPKH)), uncompressed', 0, 0),
  (28600, 'PostgreSQL SCRAM-SHA-256', 0, 1),
  (28700, 'Amazon AWS4-HMAC-SHA256', 0, 0),
  (28800, 'Kerberos 5, etype 17, DB', 0, 1),
  (28900, 'Kerberos 5, etype 18, DB', 0, 1),
  (29000, 'sha1($salt.sha1(utf16le($username).'':''.utf16le($pass)))', 0, 0),
  (29100, 'Flask Session Cookie ($salt.$salt.$pass)', 0, 0),
  (29200, 'Radmin3', 0, 0),
  (29311, 'TrueCrypt RIPEMD160 + XTS 512 bit', 0, 0),
  (29312, 'TrueCrypt RIPEMD160 + XTS 1024 bit', 0, 0),
  (29313, 'TrueCrypt RIPEMD160 + XTS 1536 bit', 0, 0),
  (29321, 'TrueCrypt SHA512 + XTS 512 bit', 0, 0),
  (29322, 'TrueCrypt SHA512 + XTS 1024 bit', 0, 0),
  (29323, 'TrueCrypt SHA512 + XTS 1536 bit', 0, 0),
  (29331, 'TrueCrypt Whirlpool + XTS 512 bit', 0, 0),
  (29332, 'TrueCrypt Whirlpool + XTS 1024 bit', 0, 0),
  (29333, 'TrueCrypt Whirlpool + XTS 1536 bit', 0, 0),
  (29341, 'TrueCrypt RIPEMD160 + XTS 512 bit + boot-mode', 0, 0),
  (29342, 'TrueCrypt RIPEMD160 + XTS 1024 bit + boot-mode', 0, 0),
  (29343, 'TrueCrypt RIPEMD160 + XTS 1536 bit + boot-mode', 0, 0),
  (29411, 'VeraCrypt RIPEMD160 + XTS 512 bit', 0, 0),
  (29412, 'VeraCrypt RIPEMD160 + XTS 1024 bit', 0, 0),
  (29413, 'VeraCrypt RIPEMD160 + XTS 1536 bit', 0, 0),
  (29421, 'VeraCrypt SHA512 + XTS 512 bit', 0, 0),
  (29422, 'VeraCrypt SHA512 + XTS 1024 bit', 0, 0),
  (29423, 'VeraCrypt SHA512 + XTS 1536 bit', 0, 0),
  (29431, 'VeraCrypt Whirlpool + XTS 512 bit', 0, 0),
  (29432, 'VeraCrypt Whirlpool + XTS 1024 bit', 0, 0),
  (29433, 'VeraCrypt Whirlpool + XTS 1536 bit', 0, 0),
  (29441, 'VeraCrypt RIPEMD160 + XTS 512 bit + boot-mode', 0, 0),
  (29442, 'VeraCrypt RIPEMD160 + XTS 1024 bit + boot-mode', 0, 0),
  (29443, 'VeraCrypt RIPEMD160 + XTS 1536 bit + boot-mode', 0, 0),
  (29451, 'VeraCrypt SHA256 + XTS 512 bit', 0, 0),
  (29452, 'VeraCrypt SHA256 + XTS 1024 bit', 0, 0),
  (29453, 'VeraCrypt SHA256 + XTS 1536 bit', 0, 0),
  (29461, 'VeraCrypt SHA256 + XTS 512 bit + boot-mode', 0, 0),
  (29462, 'VeraCrypt SHA256 + XTS 1024 bit + boot-mode', 0, 0),
  (29463, 'VeraCrypt SHA256 + XTS 1536 bit + boot-mode', 0, 0),
  (29471, 'VeraCrypt Streebog-512 + XTS 512 bit', 0, 0),
  (29472, 'VeraCrypt Streebog-512 + XTS 1024 bit', 0, 0),
  (29473, 'VeraCrypt Streebog-512 + XTS 1536 bit', 0, 0),
  (29481, 'VeraCrypt Streebog-512 + XTS 512 bit + boot-mode', 0, 0),
  (29482, 'VeraCrypt Streebog-512 + XTS 1024 bit + boot-mode', 0, 0),
  (29483, 'VeraCrypt Streebog-512 + XTS 1536 bit + boot-mode', 0, 0),
  (29511, 'LUKS v1 SHA-1 + AES', 0, 1),
  (29512, 'LUKS v1 SHA-1 + Serpent', 0, 1),
  (29513, 'LUKS v1 SHA-1 + Twofish', 0, 1),
  (29521, 'LUKS v1 SHA-256 + AES', 0, 1),
  (29522, 'LUKS v1 SHA-256 + Serpent', 0, 1),
  (29523, 'LUKS v1 SHA-256 + Twofish', 0, 1),
  (29531, 'LUKS v1 SHA-512 + AES', 0, 1),
  (29532, 'LUKS v1 SHA-512 + Serpent', 0, 1),
  (29533, 'LUKS v1 SHA-512 + Twofish', 0, 1),
  (29541, 'LUKS v1 RIPEMD-160 + AES', 0, 1),
  (29542, 'LUKS v1 RIPEMD-160 + Serpent', 0, 1),
  (29543, 'LUKS v1 RIPEMD-160 + Twofish', 0, 1),
  (29600, 'Terra Station Wallet (AES256-CBC(PBKDF2($pass)))', 0, 1),
  (29700, 'KeePass 1 (AES/Twofish) and KeePass 2 (AES) - keyfile only mode', 0, 1),
  (29800, 'Bisq .wallet (scrypt)', 0, 1),
  (29910, 'ENCsecurity Datavault (PBKDF2/no keychain)', 0, 1),
  (29920, 'ENCsecurity Datavault (PBKDF2/keychain)', 0, 1),
  (29930, 'ENCsecurity Datavault (MD5/no keychain)', 0, 1),
  (29940, 'ENCsecurity Datavault (MD5/keychain)', 0, 1),
  (30000, 'Python Werkzeug MD5 (HMAC-MD5 (key = $salt))', 0, 0),
  (30120, 'Python Werkzeug SHA256 (HMAC-SHA256 (key = $salt))', 0, 0),
  (30420, 'DANE RFC7929/RFC8162 SHA2-256', 0, 0),
  (30500, 'md5(md5($salt).md5(md5($pass)))', 1, 0),
  (30600, 'bcrypt(sha256($pass))', 0, 1),
  (30601, 'bcrypt(HMAC-SHA256($pass))', 0, 1),
  (30700, 'Anope IRC Services (enc_sha256)', 0, 0),
  (30901, 'Bitcoin raw private key (P2PKH), compressed', 0, 0),
  (30902, 'Bitcoin raw private key (P2PKH), uncompressed', 0, 0),
  (30903, 'Bitcoin raw private key (P2WPKH, Bech32), compressed', 0, 0),
  (30904, 'Bitcoin raw private key (P2WPKH, Bech32), uncompressed', 0, 0),
  (30905, 'Bitcoin raw private key (P2SH(P2WPKH)), compressed', 0, 0),
  (30906, 'Bitcoin raw private key (P2SH(P2WPKH)), uncompressed', 0, 0),
  (31000, 'BLAKE2s-256', 0, 0),
  (31100, 'ShangMi 3 (SM3)', 0, 0),
  (31200, 'Veeam VBK', 0, 1),
  (31300, 'MS SNTP', 0, 0),
  (31400, 'SecureCRT MasterPassphrase v2', 0, 0),
  (31500, 'Domain Cached Credentials (DCC), MS Cache (NT)', 1, 1),
  (31600, 'Domain Cached Credentials 2 (DCC2), MS Cache 2, (NT)', 0, 1),
  (31700, 'md5(md5(md5($pass).$salt1).$salt2)', 1, 0),
  (31800, '1Password, mobilekeychain (1Password 8)', 0, 1),
  (31900, 'MetaMask Mobile Wallet', 0, 1),
  (32000, 'NetIQ SSPR (MD5)', 0, 1),
  (32010, 'NetIQ SSPR (SHA1)', 0, 1),
  (32020, 'NetIQ SSPR (SHA-1 with Salt)', 0, 1),
  (32030, 'NetIQ SSPR (SHA-256 with Salt)', 0, 1),
  (32031, 'Adobe AEM (SSPR, SHA-256 with Salt)', 0, 1),
  (32040, 'NetIQ SSPR (SHA-512 with Salt)', 0, 1),
  (32041, 'Adobe AEM (SSPR, SHA-512 with Salt)', 0, 1),
  (32050, 'NetIQ SSPR (PBKDF2WithHmacSHA1)', 0, 1),
  (32060, 'NetIQ SSPR (PBKDF2WithHmacSHA256)', 0, 1),
  (32070, 'NetIQ SSPR (PBKDF2WithHmacSHA512)', 0, 1),
  (32100, 'Kerberos 5, etype 17, AS-REP', 0, 1),
  (32200, 'Kerberos 5, etype 18, AS-REP', 0, 1),
  (32300, 'Empire CMS (Admin password)', 1, 0),
  (32410, 'sha512(sha512($pass).$salt)', 1, 0),
  (32420, 'sha512(sha512_bin($pass).$salt)', 1, 0),
  (32500, 'Dogechain.info Wallet', 0, 1),
  (32600, 'CubeCart (whirlpool($salt.$pass.$salt))', 1, 0),
  (32700, 'Kremlin Encrypt 3.0 w/NewDES', 0, 1),
  (32800, 'md5(sha1(md5($pass)))', 0, 0),
  (32900, 'PBKDF1-SHA1', 1, 1),
  (33000, 'md5($salt1.$pass.$salt2)', 1, 0),
  (33100, 'md5($salt.md5($pass).$salt)', 1, 0),
  (33300, 'HMAC-BLAKE2S (key = $pass)', 1, 0),
  (33400, 'mega.nz password-protected link (PBKDF2-HMAC-SHA512)', 0, 1),
  (33500, 'RC4 40-bit DropN', 0, 0),
  (33501, 'RC4 72-bit DropN', 0, 0),
  (33502, 'RC4 104-bit DropN', 0, 0),
  (33600, 'RIPEMD-320', 0, 0),
  (33650, 'HMAC-RIPEMD320 (key = $pass)', 1, 0),
  (33660, 'HMAC-RIPEMD320 (key = $salt)', 1, 0),
  (33700, 'Microsoft Online Account (PBKDF2-HMAC-SHA256 + AES256)', 0, 1),
  (33800, 'WBB4 (Woltlab Burning Board) [bcrypt(bcrypt($pass))]', 0, 1),
  (33900, 'Citrix NetScaler (PBKDF2-HMAC-SHA256)', 0, 1),
  (34000, 'Argon2', 0, 1),
  (34100, 'LUKS v2 argon2 + SHA-256 + AES', 0, 1),
  (34200, 'MurmurHash64A', 1, 0),
  (34201, 'MurmurHash64A (zero seed)', 0, 0),
  (34211, 'MurmurHash64A truncated (zero seed)', 0, 0),
  (34300, 'KeePass (KDBX v4)', 0, 1),
  (34400, 'sha224(sha224($pass))', 0, 0),
  (34500, 'sha224(sha1($pass))', 0, 0),
  (34600, 'MD6 (256)', 0, 0),
  (34700, 'Blockchain, My Wallet, Legacy Wallets', 0, 0),
  (34800, 'BLAKE2b-256', 0, 0),
  (34810, 'BLAKE2b-256($pass.$salt)', 1, 0),
  (34820, 'BLAKE2b-256($salt.$pass)', 1, 0),
  (35000, 'SAP CODVN H (PWDSALTEDHASH) isSHA512', 1, 1),
  (35100, 'sm3crypt $sm3$, SM3 (Unix)', 1, 1),
  (35200, 'AS/400 SSHA1', 1, 0),
  (70000, 'Argon2id [Bridged: reference implementation + tunings]', 0, 1),
  (70100, 'scrypt [Bridged: Scrypt-Jane SMix]', 0, 1),
  (70200, 'scrypt [Bridged: Scrypt-Yescrypt]', 0, 1),
  (72000, 'Generic Hash [Bridged: Python Interpreter free-threading]', 0, 1),
  (73000, 'Generic Hash [Bridged: Python Interpreter with GIL]', 0, 1),
  (99999, 'Plaintext', 0, 0);

CREATE TABLE `LogEntry` (
  `logEntryId` INT(11)     NOT NULL,
  `issuer`     VARCHAR(50) NOT NULL,
  `issuerId`   VARCHAR(50) NOT NULL,
  `level`      VARCHAR(50) NOT NULL,
  `message`    TEXT        NOT NULL,
  `time`       BIGINT      NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `NotificationSetting` (
  `notificationSettingId` INT(11)      NOT NULL,
  `action`                VARCHAR(50)  NOT NULL,
  `objectId`              INT(11)      NULL,
  `notification`          VARCHAR(50)  NOT NULL,
  `userId`                INT(11)      NOT NULL,
  `receiver`              VARCHAR(256) NOT NULL,
  `isActive`              TINYINT(4)   NOT NULL
)ENGINE = InnoDB;

CREATE TABLE `Pretask` (
  `pretaskId`           INT(11)      NOT NULL,
  `taskName`            VARCHAR(100) NOT NULL,
  `attackCmd`           TEXT         NOT NULL,
  `chunkTime`           INT(11)      NOT NULL,
  `statusTimer`         INT(11)      NOT NULL,
  `color`               VARCHAR(20)  NULL,
  `isSmall`             TINYINT(4)   NOT NULL,
  `isCpuTask`           TINYINT(4)   NOT NULL,
  `useNewBench`         TINYINT(4)   NOT NULL,
  `priority`            INT(11)      NOT NULL,
  `maxAgents`           INT(11)      NOT NULL,
  `isMaskImport`        TINYINT(4)   NOT NULL,
  `crackerBinaryTypeId` INT(11)      NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `RegVoucher` (
  `regVoucherId` INT(11)      NOT NULL,
  `voucher`      VARCHAR(100) NOT NULL,
  `time`         BIGINT       NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `RightGroup` (
  `rightGroupId` INT(11)     NOT NULL,
  `groupName`    VARCHAR(50) NOT NULL,
  `permissions`  TEXT        NOT NULL
) ENGINE = InnoDB;

INSERT INTO `RightGroup` (`rightGroupId`, `groupName`, `permissions`) VALUES
  (1, 'Administrator', 'ALL');

CREATE TABLE `Session` (
  `sessionId`        INT(11)      NOT NULL,
  `userId`           INT(11)      NOT NULL,
  `sessionStartDate` BIGINT       NOT NULL,
  `lastActionDate`   BIGINT       NOT NULL,
  `isOpen`           TINYINT(4)   NOT NULL,
  `sessionLifetime`  INT(11)      NOT NULL,
  `sessionKey`       VARCHAR(256) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `Speed` (
  `speedId` INT(11)    NOT NULL,
  `agentId` INT(11)    NOT NULL,
  `taskId`  INT(11)    NOT NULL,
  `speed`   BIGINT(20) NOT NULL,
  `time`    BIGINT(20) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `StoredValue` (
  `storedValueId` VARCHAR(50)  NOT NULL,
  `val`           VARCHAR(256) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `Supertask` (
  `supertaskId`   INT(11)     NOT NULL,
  `supertaskName` VARCHAR(50) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `SupertaskPretask` (
  `supertaskPretaskId` INT(11) NOT NULL,
  `supertaskId`        INT(11) NOT NULL,
  `pretaskId`          INT(11) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `Task` (
  `taskId`              INT(11)      NOT NULL,
  `taskName`            VARCHAR(256) NOT NULL,
  `attackCmd`           TEXT         NOT NULL,
  `chunkTime`           INT(11)      NOT NULL,
  `statusTimer`         INT(11)      NOT NULL,
  `keyspace`            BIGINT(20)   NOT NULL,
  `keyspaceProgress`    BIGINT(20)   NOT NULL,
  `priority`            INT(11)      NOT NULL,
  `maxAgents`           INT(11)      NOT NULL,
  `color`               VARCHAR(20)  NULL,
  `isSmall`             TINYINT(4)   NOT NULL,
  `isCpuTask`           TINYINT(4)   NOT NULL,
  `useNewBench`         TINYINT(4)   NOT NULL,
  `skipKeyspace`        BIGINT(20)   NOT NULL,
  `crackerBinaryId`     INT(11)      DEFAULT NULL,
  `crackerBinaryTypeId` INT(11)      NULL,
  `taskWrapperId`       INT(11)      NOT NULL,
  `isArchived`          TINYINT(4)   NOT NULL,
  `notes`               TEXT         NOT NULL,
  `staticChunks`        INT(11)      NOT NULL,
  `chunkSize`           BIGINT(20)   NOT NULL,
  `forcePipe`           TINYINT(4)   NOT NULL,
  `usePreprocessor`     TINYINT(4)   NOT NULL,
  `preprocessorCommand` VARCHAR(256) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `TaskDebugOutput` (
  `taskDebugOutputId` INT(11)      NOT NULL,
  `taskId`            INT(11)      NOT NULL,
  `output`            VARCHAR(256) NOT NULL
) ENGINE=InnoDB;
 
CREATE TABLE `TaskWrapper` (
  `taskWrapperId`   INT(11)      NOT NULL,
  `priority`        INT(11)      NOT NULL,
  `maxAgents`       INT(11)      NOT NULL,
  `taskType`        INT(11)      NOT NULL,
  `hashlistId`      INT(11)      NOT NULL,
  `accessGroupId`   INT(11)      DEFAULT NULL,
  `taskWrapperName` VARCHAR(100) NOT NULL,
  `isArchived`      TINYINT(4)   NOT NULL,
  `cracked`         INT(11)      NOT NULL
)ENGINE = InnoDB;

CREATE TABLE `User` (
  `userId`             INT(11)      NOT NULL,
  `username`           VARCHAR(100) NOT NULL,
  `email`              VARCHAR(150) NOT NULL,
  `passwordHash`       VARCHAR(256) NOT NULL,
  `passwordSalt`       VARCHAR(256) NOT NULL,
  `isValid`            TINYINT(4)   NOT NULL,
  `isComputedPassword` TINYINT(4)   NOT NULL,
  `lastLoginDate`      BIGINT       NOT NULL,
  `registeredSince`    BIGINT       NOT NULL,
  `sessionLifetime`    INT(11)      NOT NULL,
  `rightGroupId`       INT(11)      NOT NULL,
  `yubikey`            VARCHAR(256) DEFAULT NULL,
  `otp1`               VARCHAR(256) DEFAULT NULL,
  `otp2`               VARCHAR(256) DEFAULT NULL,
  `otp3`               VARCHAR(256) DEFAULT NULL,
  `otp4`               VARCHAR(256) DEFAULT NULL
) ENGINE = InnoDB;

CREATE TABLE `Zap` (
  `zapId`      INT(11)    NOT NULL,
  `hash`       MEDIUMTEXT NOT NULL,
  `solveTime`  BIGINT     NOT NULL,
  `agentId`    INT(11)    NULL,
  `hashlistId` INT(11)    NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `ApiKey` (
  `apiKeyId`    INT(11)      NOT NULL,
  `startValid`  BIGINT(20)   NOT NULL,
  `endValid`    BIGINT(20)   NOT NULL,
  `accessKey`   VARCHAR(256) NOT NULL,
  `accessCount` INT(11)      NOT NULL,
  `userId`      INT(11)      NOT NULL,
  `apiGroupId`  INT(11)      NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `ApiGroup` (
  `apiGroupId`  INT(11)      NOT NULL,
  `name`        VARCHAR(100) NOT NULL,
  `permissions` TEXT         NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `FileDownload` (
  `fileDownloadId` INT(11) NOT NULL,
  `time`           BIGINT  NOT NULL,
  `fileId`         INT(11) NOT NULL,
  `status`         INT(11) NOT NULL
) ENGINE=InnoDB;

INSERT INTO `ApiGroup` ( `apiGroupId`, `name`, `permissions`) VALUES
  (1, 'Administrators', 'ALL');

CREATE TABLE `HealthCheck` (
  `healthCheckId`   INT(11)      NOT NULL,
  `time`            BIGINT(20)   NOT NULL,
  `status`          INT(11)      NOT NULL,
  `checkType`       INT(11)      NOT NULL,
  `hashtypeId`      INT(11)      NOT NULL,
  `crackerBinaryId` INT(11)      NOT NULL,
  `expectedCracks`  INT(11)      NOT NULL,
  `attackCmd`       TEXT         NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `HealthCheckAgent` (
  `healthCheckAgentId` INT(11)    NOT NULL,
  `healthCheckId`      INT(11)    NOT NULL,
  `agentId`            INT(11)    NOT NULL,
  `status`             INT(11)    NOT NULL,
  `cracked`            INT(11)    NOT NULL,
  `numGpus`            INT(11)    NOT NULL,
  `start`              BIGINT(20) NOT NULL,
  `end`                BIGINT(20) NOT NULL,
  `errors`             TEXT       NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `Preprocessor` (
  `preprocessorId`  INT(11)      NOT NULL,
  `name`            VARCHAR(256) NOT NULL,
  `url`             VARCHAR(512) NOT NULL,
  `binaryName`      VARCHAR(256) NOT NULL,
  `keyspaceCommand` VARCHAR(256) NULL,
  `skipCommand`     VARCHAR(256) NULL,
  `limitCommand`    VARCHAR(256) NULL
) ENGINE=InnoDB;

INSERT INTO `Preprocessor` ( `preprocessorId`, `name`, `url`, `binaryName`, `keyspaceCommand`, `skipCommand`, `limitCommand`) VALUES
  (1, 'Prince', 'https://github.com/hashcat/princeprocessor/releases/download/v0.22/princeprocessor-0.22.7z', 'pp', '--keyspace', '--skip', '--limit');

-- Add Indexes
ALTER TABLE `AccessGroup`
  ADD PRIMARY KEY (`accessGroupId`);

ALTER TABLE `AccessGroupAgent`
  ADD PRIMARY KEY (`accessGroupAgentId`),
  ADD KEY `accessGroupId` (`accessGroupId`),
  ADD KEY `agentId` (`agentId`);

ALTER TABLE `AccessGroupUser`
  ADD PRIMARY KEY (`accessGroupUserId`),
  ADD KEY `accessGroupId` (`accessGroupId`),
  ADD KEY `userId` (`userId`);

ALTER TABLE `Agent`
  ADD PRIMARY KEY (`agentId`),
  ADD KEY `userId` (`userId`);

ALTER TABLE `AgentBinary`
  ADD PRIMARY KEY (`agentBinaryId`);

ALTER TABLE `AgentError`
  ADD PRIMARY KEY (`agentErrorId`),
  ADD KEY `agentId` (`agentId`),
  ADD KEY `taskId` (`taskId`);

ALTER TABLE `AgentStat`
  ADD PRIMARY KEY (`agentStatId`),
  ADD KEY `agentId` (`agentId`);

ALTER TABLE `AgentZap`
  ADD PRIMARY KEY (`agentZapId`),
  ADD KEY `agentId` (`agentId`),
  ADD KEY `lastZapId` (`lastZapId`);

ALTER TABLE `ApiKey`
  ADD PRIMARY KEY (`apiKeyId`);

ALTER TABLE `ApiGroup`
  ADD PRIMARY KEY (`apiGroupId`);

ALTER TABLE `Assignment`
  ADD PRIMARY KEY (`assignmentId`),
  ADD KEY `taskId` (`taskId`),
  ADD KEY `agentId` (`agentId`);

ALTER TABLE `Chunk`
  ADD PRIMARY KEY (`chunkId`),
  ADD KEY `taskId` (`taskId`),
  ADD KEY `progress` (`progress`),
  ADD KEY `agentId` (`agentId`),
  ADD KEY `idx_task_progress_length` (`taskId`, `progress`, `length`);

ALTER TABLE `Config`
  ADD PRIMARY KEY (`configId`),
  ADD KEY `configSectionId` (`configSectionId`);

ALTER TABLE `ConfigSection`
  ADD PRIMARY KEY (`configSectionId`);

ALTER TABLE `CrackerBinary`
  ADD PRIMARY KEY (`crackerBinaryId`),
  ADD KEY `crackerBinaryTypeId` (`crackerBinaryTypeId`);

ALTER TABLE `CrackerBinaryType`
  ADD PRIMARY KEY (`crackerBinaryTypeId`);

ALTER TABLE `File`
  ADD PRIMARY KEY (`fileId`);

ALTER TABLE `FileDownload`
  ADD PRIMARY KEY (`fileDownloadId`);

ALTER TABLE `FileDelete`
  ADD PRIMARY KEY (`fileDeleteId`);

ALTER TABLE `FilePretask`
  ADD PRIMARY KEY (`filePretaskId`),
  ADD KEY `fileId` (`fileId`),
  ADD KEY `pretaskId` (`pretaskId`);

ALTER TABLE `FileTask`
  ADD PRIMARY KEY (`fileTaskId`),
  ADD KEY `fileId` (`fileId`),
  ADD KEY `taskId` (`taskId`);

ALTER TABLE `Hash`
  ADD PRIMARY KEY (`hashId`),
  ADD KEY `hashlistId` (`hashlistId`),
  ADD KEY `chunkId` (`chunkId`),
  ADD KEY `isCracked` (`isCracked`),
  ADD KEY `hash` (`hash`(500));

ALTER TABLE `HashBinary`
  ADD PRIMARY KEY (`hashBinaryId`),
  ADD KEY `hashlistId` (`hashlistId`),
  ADD KEY `chunkId` (`chunkId`);

ALTER TABLE `Hashlist`
  ADD PRIMARY KEY (`hashlistId`),
  ADD KEY `hashTypeId` (`hashTypeId`);

ALTER TABLE `HashlistHashlist`
  ADD PRIMARY KEY (`hashlistHashlistId`),
  ADD KEY `parentHashlistId` (`parentHashlistId`),
  ADD KEY `hashlistId` (`hashlistId`);

ALTER TABLE `HashType`
  ADD PRIMARY KEY (`hashTypeId`);

ALTER TABLE `HealthCheck` 
  ADD PRIMARY KEY (`healthCheckId`);

ALTER TABLE `HealthCheckAgent` 
  ADD PRIMARY KEY (`healthCheckAgentId`);

ALTER TABLE `LogEntry`
  ADD PRIMARY KEY (`logEntryId`);

ALTER TABLE `NotificationSetting`
  ADD PRIMARY KEY (`notificationSettingId`),
  ADD KEY `userId` (`userId`);

ALTER TABLE `Pretask`
  ADD PRIMARY KEY (`pretaskId`);

ALTER TABLE `RegVoucher`
  ADD PRIMARY KEY (`regVoucherId`);

ALTER TABLE `RightGroup`
  ADD PRIMARY KEY (`rightGroupId`);

ALTER TABLE `Session`
  ADD PRIMARY KEY (`sessionId`),
  ADD KEY `userId` (`userId`);

ALTER TABLE `Speed`
  ADD PRIMARY KEY (`speedId`),
  ADD KEY `agentId` (`agentId`),
  ADD KEY `taskId` (`taskId`);

ALTER TABLE `StoredValue`
  ADD PRIMARY KEY (`storedValueId`);

ALTER TABLE `Supertask`
  ADD PRIMARY KEY (`supertaskId`);

ALTER TABLE `SupertaskPretask`
  ADD PRIMARY KEY (`supertaskPretaskId`),
  ADD KEY `supertaskId` (`supertaskId`),
  ADD KEY `pretaskId` (`pretaskId`);

ALTER TABLE `Task`
  ADD PRIMARY KEY (`taskId`),
  ADD KEY `crackerBinaryId` (`crackerBinaryId`);

ALTER TABLE `TaskDebugOutput`
  ADD PRIMARY KEY (`taskDebugOutputId`);

ALTER TABLE `TaskWrapper`
  ADD PRIMARY KEY (`taskWrapperId`),
  ADD KEY `hashlistId` (`hashlistId`),
  ADD KEY `priority` (`priority`),
  ADD KEY `isArchived` (`isArchived`),
  ADD KEY `accessGroupId` (`accessGroupId`);

ALTER TABLE `User`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `rightGroupId` (`rightGroupId`);

ALTER TABLE `Zap`
  ADD PRIMARY KEY (`zapId`),
  ADD KEY `agentId` (`agentId`),
  ADD KEY `hashlistId` (`hashlistId`);

ALTER TABLE `Preprocessor`
  ADD PRIMARY KEY (`preprocessorId`);

-- Add AUTO_INCREMENT for tables
ALTER TABLE `AccessGroup`
  MODIFY `accessGroupId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `AccessGroupAgent`
  MODIFY `accessGroupAgentId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `AccessGroupUser`
  MODIFY `accessGroupUserId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Agent`
  MODIFY `agentId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `AgentBinary`
  MODIFY `agentBinaryId` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 2;

ALTER TABLE `AgentError`
  MODIFY `agentErrorId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `AgentStat`
  MODIFY `agentStatId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `AgentZap`
  MODIFY `agentZapId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ApiKey`
  MODIFY `apiKeyId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ApiGroup`
  MODIFY `apiGroupId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Assignment`
  MODIFY `assignmentId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Chunk`
  MODIFY `chunkId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Config`
  MODIFY `configId` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 72;

ALTER TABLE `ConfigSection`
  MODIFY `configSectionId` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 8;

ALTER TABLE `CrackerBinary`
  MODIFY `crackerBinaryId` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 2;

ALTER TABLE `CrackerBinaryType`
  MODIFY `crackerBinaryTypeId` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 2;

ALTER TABLE `File`
  MODIFY `fileId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `FileDownload`
  MODIFY `fileDownloadId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `FileDelete`
  MODIFY `fileDeleteId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `FilePretask`
  MODIFY `filePretaskId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `FileTask`
  MODIFY `fileTaskId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Hash`
  MODIFY `hashId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `HashBinary`
  MODIFY `hashBinaryId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Hashlist`
  MODIFY `hashlistId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `HashlistHashlist`
  MODIFY `hashlistHashlistId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `HealthCheck` 
  MODIFY `healthCheckId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `HealthCheckAgent` 
  MODIFY `healthCheckAgentId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `LogEntry`
  MODIFY `logEntryId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `NotificationSetting`
  MODIFY `notificationSettingId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Pretask`
  MODIFY `pretaskId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `RegVoucher`
  MODIFY `regVoucherId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `RightGroup`
  MODIFY `rightGroupId` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 2;

ALTER TABLE `Session`
  MODIFY `sessionId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Speed`
  MODIFY `speedId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Supertask`
  MODIFY `supertaskId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `SupertaskPretask`
  MODIFY `supertaskPretaskId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Task`
  MODIFY `taskId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `TaskDebugOutput`
  MODIFY `taskDebugOutputId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `TaskWrapper`
  MODIFY `taskWrapperId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `User`
  MODIFY `userId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Zap`
  MODIFY `zapId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Preprocessor`
  MODIFY `preprocessorId` INT(11) NOT NULL AUTO_INCREMENT;

-- Add Constraints
ALTER TABLE `AccessGroupAgent`
  ADD CONSTRAINT `AccessGroupAgent_ibfk_1` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`),
  ADD CONSTRAINT `AccessGroupAgent_ibfk_2` FOREIGN KEY (`agentId`)       REFERENCES `Agent` (`agentId`);

ALTER TABLE `AccessGroupUser`
  ADD CONSTRAINT `AccessGroupUser_ibfk_1` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`),
  ADD CONSTRAINT `AccessGroupUser_ibfk_2` FOREIGN KEY (`userId`)        REFERENCES `User` (`userId`);

ALTER TABLE `Agent`
  ADD CONSTRAINT `Agent_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

ALTER TABLE `AgentError`
  ADD CONSTRAINT `AgentError_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `AgentError_ibfk_2` FOREIGN KEY (`taskId`)  REFERENCES `Task` (`taskId`);

ALTER TABLE `AgentStat`
  ADD CONSTRAINT `AgentStat_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

ALTER TABLE `AgentZap`
  ADD CONSTRAINT `AgentZap_ibfk_1` FOREIGN KEY (`agentId`)   REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `AgentZap_ibfk_2` FOREIGN KEY (`lastZapId`) REFERENCES `Zap` (`zapId`);

ALTER TABLE `ApiKey`
  ADD CONSTRAINT `ApiKey_ibfk_1` FOREIGN KEY (`userId`)     REFERENCES `User` (`userId`),
  ADD CONSTRAINT `ApiKey_ibfk_2` FOREIGN KEY (`apiGroupId`) REFERENCES `ApiGroup` (`apiGroupId`);

ALTER TABLE `Assignment`
  ADD CONSTRAINT `Assignment_ibfk_1` FOREIGN KEY (`taskId`)  REFERENCES `Task` (`taskId`),
  ADD CONSTRAINT `Assignment_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

ALTER TABLE `Chunk`
  ADD CONSTRAINT `Chunk_ibfk_1` FOREIGN KEY (`taskId`)  REFERENCES `Task` (`taskId`),
  ADD CONSTRAINT `Chunk_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

ALTER TABLE `Config`
  ADD CONSTRAINT `Config_ibfk_1` FOREIGN KEY (`configSectionId`) REFERENCES `ConfigSection` (`configSectionId`);

ALTER TABLE `CrackerBinary`
  ADD CONSTRAINT `CrackerBinary_ibfk_1` FOREIGN KEY (`crackerBinaryTypeId`) REFERENCES `CrackerBinaryType` (`crackerBinaryTypeId`);

ALTER TABLE `File`
  ADD CONSTRAINT `File_ibfk_1` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`);

ALTER TABLE `FileDownload`
  ADD CONSTRAINT `FileDownload_ibkf_1` FOREIGN KEY (`fileId`) REFERENCES `File`(`fileId`);

ALTER TABLE `FilePretask`
  ADD CONSTRAINT `FilePretask_ibfk_1` FOREIGN KEY (`fileId`)    REFERENCES `File` (`fileId`),
  ADD CONSTRAINT `FilePretask_ibfk_2` FOREIGN KEY (`pretaskId`) REFERENCES `Pretask` (`pretaskId`);

ALTER TABLE `FileTask`
  ADD CONSTRAINT `FileTask_ibfk_1` FOREIGN KEY (`fileId`) REFERENCES `File` (`fileId`),
  ADD CONSTRAINT `FileTask_ibfk_2` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`);

ALTER TABLE `Hash`
  ADD CONSTRAINT `Hash_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `Hash_ibfk_2` FOREIGN KEY (`chunkId`)    REFERENCES `Chunk` (`chunkId`);

ALTER TABLE `HashBinary`
  ADD CONSTRAINT `HashBinary_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `HashBinary_ibfk_2` FOREIGN KEY (`chunkId`)    REFERENCES `Chunk` (`chunkId`);

ALTER TABLE `Hashlist`
  ADD CONSTRAINT `Hashlist_ibfk_1` FOREIGN KEY (`hashTypeId`)    REFERENCES `HashType` (`hashTypeId`),
  ADD CONSTRAINT `Hashlist_ibfk_2` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`);

ALTER TABLE `HashlistHashlist`
  ADD CONSTRAINT `HashlistHashlist_ibfk_1` FOREIGN KEY (`parentHashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `HashlistHashlist_ibfk_2` FOREIGN KEY (`hashlistId`)       REFERENCES `Hashlist` (`hashlistId`);

ALTER TABLE `HealthCheck`
  ADD CONSTRAINT `HealthCheck_ibfk_1` FOREIGN KEY (`crackerBinaryId`) REFERENCES `CrackerBinary` (`crackerBinaryId`);

ALTER TABLE `HealthCheckAgent`
  ADD CONSTRAINT `HealthCheckAgent_ibfk_1` FOREIGN KEY (`agentId`)       REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `HealthCheckAgent_ibfk_2` FOREIGN KEY (`healthCheckId`) REFERENCES `HealthCheck` (`healthCheckId`);

ALTER TABLE `NotificationSetting`
  ADD CONSTRAINT `NotificationSetting_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

ALTER TABLE `Pretask`
  ADD CONSTRAINT `Pretask_ibfk_1` FOREIGN KEY (`crackerBinaryTypeId`) REFERENCES `CrackerBinaryType` (`crackerBinaryTypeId`);

ALTER TABLE `Session`
  ADD CONSTRAINT `Session_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

ALTER TABLE `Speed`
  ADD CONSTRAINT `Speed_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `Speed_ibfk_2` FOREIGN KEY (`taskId`)  REFERENCES `Task` (`taskId`);

ALTER TABLE `SupertaskPretask`
  ADD CONSTRAINT `SupertaskPretask_ibfk_1` FOREIGN KEY (`supertaskId`) REFERENCES `Supertask` (`supertaskId`),
  ADD CONSTRAINT `SupertaskPretask_ibfk_2` FOREIGN KEY (`pretaskId`)   REFERENCES `Pretask` (`pretaskId`);

ALTER TABLE `Task`
  ADD CONSTRAINT `Task_ibfk_1` FOREIGN KEY (`crackerBinaryId`)     REFERENCES `CrackerBinary` (`crackerBinaryId`),
  ADD CONSTRAINT `Task_ibfk_2` FOREIGN KEY (`crackerBinaryTypeId`) REFERENCES `CrackerBinaryType` (`crackerBinaryTypeId`),
  ADD CONSTRAINT `Task_ibfk_3` FOREIGN KEY (`taskWrapperId`)       REFERENCES `TaskWrapper` (`taskWrapperId`);

ALTER TABLE `TaskDebugOutput`
  ADD CONSTRAINT `TaskDebugOutput_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`);

ALTER TABLE `TaskWrapper`
  ADD CONSTRAINT `TaskWrapper_ibfk_1` FOREIGN KEY (`hashlistId`)    REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `TaskWrapper_ibfk_2` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`);

ALTER TABLE `User`
  ADD CONSTRAINT `User_ibfk_1` FOREIGN KEY (`rightGroupId`) REFERENCES `RightGroup` (`rightGroupId`);

ALTER TABLE `Zap`
  ADD CONSTRAINT `Zap_ibfk_1` FOREIGN KEY (`agentId`)    REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `Zap_ibfk_2` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`);

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
