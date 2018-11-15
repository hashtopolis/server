SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `AccessGroup` (
  `accessGroupId` INT(11)     NOT NULL,
  `groupName`     VARCHAR(50) NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `AccessGroupAgent` (
  `accessGroupAgentId` INT(11) NOT NULL,
  `accessGroupId`      INT(11) NOT NULL,
  `agentId`            INT(11) NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `AccessGroupUser` (
  `accessGroupUserId` INT(11) NOT NULL,
  `accessGroupId`     INT(11) NOT NULL,
  `userId`            INT(11) NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `Agent` (
  `agentId`         INT(11)                      NOT NULL,
  `agentName`       VARCHAR(100)
                    COLLATE utf8_unicode_ci      NOT NULL,
  `uid`             VARCHAR(100)
                    COLLATE utf8_unicode_ci      NOT NULL,
  `os`              INT(11)                      NOT NULL,
  `devices`         TEXT COLLATE utf8_unicode_ci NOT NULL,
  `cmdPars`         VARCHAR(256)
                    COLLATE utf8_unicode_ci      NOT NULL,
  `ignoreErrors`    TINYINT(4)                   NOT NULL,
  `isActive`        TINYINT(4)                   NOT NULL,
  `isTrusted`       TINYINT(4)                   NOT NULL,
  `token`           VARCHAR(30)
                    COLLATE utf8_unicode_ci      NOT NULL,
  `lastAct`         VARCHAR(50)
                    COLLATE utf8_unicode_ci      NOT NULL,
  `lastTime`        INT(11)                      NOT NULL,
  `lastIp`          VARCHAR(50)
                    COLLATE utf8_unicode_ci      NOT NULL,
  `userId`          INT(11) DEFAULT NULL,
  `cpuOnly`         TINYINT(4)                   NOT NULL,
  `clientSignature` VARCHAR(50)                  NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `AgentBinary` (
  `agentBinaryId`    INT(11)                 NOT NULL,
  `type`             VARCHAR(20)
                     COLLATE utf8_unicode_ci NOT NULL,
  `version`          VARCHAR(20)
                     COLLATE utf8_unicode_ci NOT NULL,
  `operatingSystems` VARCHAR(50)
                     COLLATE utf8_unicode_ci NOT NULL,
  `filename`         VARCHAR(50)
                     COLLATE utf8_unicode_ci NOT NULL
)
  ENGINE = InnoDB;

INSERT INTO `AgentBinary` (`agentBinaryId`, `type`, `version`, `operatingSystems`, `filename`) VALUES
  (1, 'csharp', '0.52.2', 'Windows, Linux(mono), OS X(mono)', 'hashtopolis.exe'),
  (2, 'python', '0.2.0', 'Windows, Linux, OS X', 'hashtopolis.zip');

CREATE TABLE `AgentError` (
  `agentErrorId` INT(11)                      NOT NULL,
  `agentId`      INT(11)                      NOT NULL,
  `taskId`       INT(11) DEFAULT NULL,
  `time`         INT(11)                      NOT NULL,
  `error`        TEXT COLLATE utf8_unicode_ci NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `AgentStat` (
  `agentStatId` INT(11) NOT NULL,
  `agentId`     INT(11) NOT NULL,
  `statType`    INT(11) NOT NULL,
  `time`        BIGINT NOT NULL,
  `value`       VARCHAR(64) NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `AgentZap` (
  `agentZapId` INT(11) NOT NULL,
  `agentId`    INT(11) NOT NULL,
  `lastZapId`  INT(11) NULL
)
  ENGINE = InnoDB;

CREATE TABLE `Assignment` (
  `assignmentId` INT(11)                 NOT NULL,
  `taskId`       INT(11)                 NOT NULL,
  `agentId`      INT(11)                 NOT NULL,
  `benchmark`    VARCHAR(50)
                 COLLATE utf8_unicode_ci NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `Chunk` (
  `chunkId`      INT(11)    NOT NULL,
  `taskId`       INT(11)    NOT NULL,
  `skip`         BIGINT(20) NOT NULL,
  `length`       BIGINT(20) NOT NULL,
  `agentId`      INT(11)    NULL,
  `dispatchTime` INT(11)    NOT NULL,
  `solveTime`    INT(11)    NOT NULL,
  `checkpoint`   BIGINT(20) NOT NULL,
  `progress`     INT(11)    NOT NULL,
  `state`        INT(11)    NOT NULL,
  `cracked`      INT(11)    NOT NULL,
  `speed`        BIGINT(20) NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `Config` (
  `configId`        INT(11)                      NOT NULL,
  `configSectionId` INT(11)                      NOT NULL,
  `item`            VARCHAR(80)
                    COLLATE utf8_unicode_ci      NOT NULL,
  `value`           TEXT COLLATE utf8_unicode_ci NOT NULL
)
  ENGINE = InnoDB;

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
  (20, 2, 'yubikey_url', 'http://api.yubico.com/wsapi/2.0/verify'),
  (21, 4, 'donateOff', '0'),
  (22, 3, 'pagingSize', '5000'),
  (23, 3, 'plainTextMaxLength', '200'),
  (24, 3, 'hashMaxLength', '1024'),
  (25, 5, 'emailSender', 'hashtopolis@example.org'),
  (26, 5, 'emailSenderName', 'Hashtopolis'),
  (27, 5, 'baseHost', ''),
  (28, 3, 'maxHashlistSize', '5000000'),
  (29, 4, 'hideImportMasks', '1'),
  (30, 5, 'telegramBotToken', ''),
  (31, 5, 'contactEmail', ''),
  (32, 5, 'voucherDeletion', '0'),
  (33, 4, 'hashesPerPage', '1000'),
  (34, 4, 'hideIpInfo', '0'),
  (35, 1, 'defaultBenchmark', '1'),
  (36, 4, 'showTaskPerformance', '0'),
  (37, 1, 'ruleSplitSmallTasks', '0'),
  (38, 1, 'ruleSplitAlways', '0'),
  (39, 1, 'ruleSplitDisable', '0'),
  (40, 1, 'princeLink', 'https://github.com/hashcat/princeprocessor/releases/download/v0.22/princeprocessor-0.22.7z'),
  (41, 4, 'agentStatLimit', '100'),
  (42, 1, 'agentDataLifetime', '3600'),
  (43, 4, 'agentStatTension', '0'),
  (44, 6, 'multicastEnable', '0'),
  (45, 6, 'multicastDevice', 'eth0'),
  (46, 6, 'multicastTransferRateEnable', '0'),
  (47, 6, 'multicastTranserRate', '500000'),
  (48, 5, 'baseUrl', '');

CREATE TABLE `ConfigSection` (
  `configSectionId` INT(11)                 NOT NULL,
  `sectionName`     VARCHAR(100)
                    COLLATE utf8_unicode_ci NOT NULL
)
  ENGINE = InnoDB;

INSERT INTO `ConfigSection` (`configSectionId`, `sectionName`) VALUES
  (1, 'Cracking/Tasks'),
  (2, 'Yubikey'),
  (3, 'Finetuning'),
  (4, 'UI'),
  (5, 'Server');

CREATE TABLE `CrackerBinary` (
  `crackerBinaryId`     INT(11)                 NOT NULL,
  `crackerBinaryTypeId` INT(11)                 NOT NULL,
  `version`             VARCHAR(20)
                        COLLATE utf8_unicode_ci NOT NULL,
  `downloadUrl`         VARCHAR(150)
                        COLLATE utf8_unicode_ci NOT NULL,
  `binaryName`          VARCHAR(50)
                        COLLATE utf8_unicode_ci NOT NULL
)
  ENGINE = InnoDB;

INSERT INTO `CrackerBinary` (`crackerBinaryId`, `crackerBinaryTypeId`, `version`, `downloadUrl`, `binaryName`) VALUES
  (1, 1, '4.2.1', 'https://hashcat.net/files/hashcat-4.2.1.7z', 'hashcat');

CREATE TABLE `CrackerBinaryType` (
  `crackerBinaryTypeId` INT(11)                 NOT NULL,
  `typeName`            VARCHAR(30)
                        COLLATE utf8_unicode_ci NOT NULL,
  `isChunkingAvailable` INT(11)                 NOT NULL
)
  ENGINE = InnoDB;

INSERT INTO `CrackerBinaryType` (`crackerBinaryTypeId`, `typeName`, `isChunkingAvailable`) VALUES
  (1, 'hashcat', 1);

CREATE TABLE `File` (
  `fileId`   INT(11)                 NOT NULL,
  `filename` VARCHAR(100)
             COLLATE utf8_unicode_ci NOT NULL,
  `size`     BIGINT(20)              NOT NULL,
  `isSecret` INT(11)                 NOT NULL,
  `fileType` INT(11)                 NOT NULL,
  `accessGroupId` INT(11)            NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `FilePretask` (
  `filePretaskId` INT(11) NOT NULL,
  `fileId`        INT(11) NOT NULL,
  `pretaskId`     INT(11) NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `FileTask` (
  `fileTaskId` INT(11) NOT NULL,
  `fileId`     INT(11) NOT NULL,
  `taskId`     INT(11) NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `FileDelete` (
  `fileDeleteId` int(11) NOT NULL,
  `filename` varchar(256) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `Hash` (
  `hashId`      INT(11)                 NOT NULL,
  `hashlistId`  INT(11)                 NOT NULL,
  `hash`        VARCHAR(1024)
                COLLATE utf8_unicode_ci NOT NULL,
  `salt`        VARCHAR(256)
                COLLATE utf8_unicode_ci DEFAULT NULL,
  `plaintext`   VARCHAR(256)
                COLLATE utf8_unicode_ci DEFAULT NULL,
  `timeCracked` INT(11)                 DEFAULT NULL,
  `chunkId`     INT(11)                 DEFAULT NULL,
  `isCracked`   TINYINT(4)              NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `HashBinary` (
  `hashBinaryId` INT(11)                            NOT NULL,
  `hashlistId`   INT(11)                            NOT NULL,
  `essid`        VARCHAR(100)
                 COLLATE utf8_unicode_ci            NOT NULL,
  `hash`         MEDIUMTEXT COLLATE utf8_unicode_ci NOT NULL,
  `plaintext`    VARCHAR(1024)
                 COLLATE utf8_unicode_ci DEFAULT NULL,
  `timeCracked`  INT(11)                 DEFAULT NULL,
  `chunkId`      INT(11)                 DEFAULT NULL,
  `isCracked`    TINYINT(4)                         NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `Hashlist` (
  `hashlistId`    INT(11)                 NOT NULL,
  `hashlistName`  VARCHAR(100)
                  COLLATE utf8_unicode_ci NOT NULL,
  `format`        INT(11)                 NOT NULL,
  `hashTypeId`    INT(11)                 NOT NULL,
  `hashCount`     INT(11)                 NOT NULL,
  `saltSeparator` VARCHAR(10)
                  COLLATE utf8_unicode_ci DEFAULT NULL,
  `cracked`       INT(11)                 NOT NULL,
  `isSecret`      INT(11)                 NOT NULL,
  `hexSalt`       INT(11)                 NOT NULL,
  `isSalted`      TINYINT(4)              NOT NULL,
  `accessGroupId` INT(11)                 NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `HashlistHashlist` (
  `hashlistHashlistId` INT(11) NOT NULL,
  `parentHashlistId`   INT(11) NOT NULL,
  `hashlistId`         INT(11) NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `HashType` (
  `hashTypeId`  INT(11)                 NOT NULL,
  `description` VARCHAR(256)
                COLLATE utf8_unicode_ci NOT NULL,
  `isSalted`    TINYINT(4)              NOT NULL
)
  ENGINE = InnoDB;

INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`) VALUES
  (0, 'MD5', 0),
  (10, 'md5($pass.$salt)', 1),
  (11, 'Joomla < 2.5.18', 1),
  (12, 'PostgreSQL', 1),
  (20, 'md5($salt.$pass)', 1),
  (21, 'osCommerce, xt:Commerce', 1),
  (22, 'Juniper Netscreen/SSG (ScreenOS)', 1),
  (23, 'Skype', 1),
  (30, 'md5(unicode($pass).$salt)', 1),
  (40, 'md5($salt.unicode($pass))', 1),
  (50, 'HMAC-MD5 (key = $pass)', 1),
  (60, 'HMAC-MD5 (key = $salt)', 1),
  (100, 'SHA1', 0),
  (101, 'nsldap, SHA-1(Base64), Netscape LDAP SHA', 0),
  (110, 'sha1($pass.$salt)', 1),
  (111, 'nsldaps, SSHA-1(Base64), Netscape LDAP SSHA', 0),
  (112, 'Oracle S: Type (Oracle 11+)', 1),
  (120, 'sha1($salt.$pass)', 1),
  (121, 'SMF >= v1.1', 1),
  (122, 'OS X v10.4, v10.5, v10.6', 0),
  (123, 'EPi', 0),
  (124, 'Django (SHA-1)', 0),
  (125, 'ArubaOS', 0),
  (130, 'sha1(unicode($pass).$salt)', 1),
  (131, 'MSSQL(2000)', 0),
  (132, 'MSSQL(2005)', 0),
  (133, 'PeopleSoft', 0),
  (140, 'sha1($salt.unicode($pass))', 1),
  (141, 'EPiServer 6.x < v4', 0),
  (150, 'HMAC-SHA1 (key = $pass)', 1),
  (160, 'HMAC-SHA1 (key = $salt)', 1),
  (200, 'MySQL323', 0),
  (300, 'MySQL4.1/MySQL5+', 0),
  (400, 'phpass, MD5(Wordpress), MD5(Joomla), MD5(phpBB3)', 0),
  (500, 'md5crypt, MD5(Unix), FreeBSD MD5, Cisco-IOS MD5 2', 0),
  (501, 'Juniper IVE', 0),
  (600, 'BLAKE2b-512', 0),
  (900, 'MD4', 0),
  (1000, 'NTLM', 0),
  (1100, 'Domain Cached Credentials (DCC), MS Cache', 1),
  (1300, 'SHA-224', 0),
  (1400, 'SHA256', 0),
  (1410, 'sha256($pass.$salt)', 1),
  (1411, 'SSHA-256(Base64), LDAP {SSHA256}', 0),
  (1420, 'sha256($salt.$pass)', 1),
  (1421, 'hMailServer', 0),
  (1430, 'sha256(unicode($pass).$salt)', 1),
  (1440, 'sha256($salt.unicode($pass))', 1),
  (1441, 'EPiServer 6.x >= v4', 0),
  (1450, 'HMAC-SHA256 (key = $pass)', 1),
  (1460, 'HMAC-SHA256 (key = $salt)', 1),
  (1500, 'descrypt, DES(Unix), Traditional DES', 0),
  (1600, 'md5apr1, MD5(APR), Apache MD5', 0),
  (1700, 'SHA512', 0),
  (1710, 'sha512($pass.$salt)', 1),
  (1711, 'SSHA-512(Base64), LDAP {SSHA512}', 0),
  (1720, 'sha512($salt.$pass)', 1),
  (1722, 'OS X v10.7', 0),
  (1730, 'sha512(unicode($pass).$salt)', 1),
  (1731, 'MSSQL(2012), MSSQL(2014)', 0),
  (1740, 'sha512($salt.unicode($pass))', 1),
  (1750, 'HMAC-SHA512 (key = $pass)', 1),
  (1760, 'HMAC-SHA512 (key = $salt)', 1),
  (1800, 'sha512crypt, SHA512(Unix)', 0),
  (2100, 'Domain Cached Credentials 2 (DCC2), MS Cache', 0),
  (2400, 'Cisco-PIX MD5', 0),
  (2410, 'Cisco-ASA MD5', 1),
  (2500, 'WPA/WPA2', 0),
  (2600, 'md5(md5($pass))', 0),
  (2611, 'vBulletin < v3.8.5', 1),
  (2612, 'PHPS', 0),
  (2711, 'vBulletin >= v3.8.5', 1),
  (2811, 'IPB2+, MyBB1.2+', 1),
  (3000, 'LM', 0),
  (3100, 'Oracle H: Type (Oracle 7+), DES(Oracle)', 1),
  (3200, 'bcrypt, Blowfish(OpenBSD)', 0),
  (3710, 'md5($salt.md5($pass))', 1),
  (3711, 'Mediawiki B type', 0),
  (3800, 'md5($salt.$pass.$salt)', 1),
  (3910, 'md5(md5($pass).md5($salt))', 1),
  (4010, 'md5($salt.md5($salt.$pass))', 1),
  (4110, 'md5($salt.md5($pass.$salt))', 1),
  (4300, 'md5(strtoupper(md5($pass)))', 0),
  (4400, 'md5(sha1($pass))', 0),
  (4500, 'sha1(sha1($pass))', 0),
  (4520, 'sha1($salt.sha1($pass))', 1),
  (4521, 'Redmine Project Management Web App', 0),
  (4522, 'PunBB', 0),
  (4700, 'sha1(md5($pass))', 0),
  (4800, 'MD5(Chap), iSCSI CHAP authentication', 1),
  (4900, 'sha1($salt.$pass.$salt)', 1),
  (5000, 'SHA-3(Keccak)', 0),
  (5100, 'Half MD5', 0),
  (5200, 'Password Safe v3', 0),
  (5300, 'IKE-PSK MD5', 0),
  (5400, 'IKE-PSK SHA1', 0),
  (5500, 'NetNTLMv1-VANILLA / NetNTLMv1+ESS', 0),
  (5600, 'NetNTLMv2', 0),
  (5700, 'Cisco-IOS SHA256', 0),
  (5800, 'Samsung Android Password/PIN', 1),
  (6000, 'RipeMD160', 0),
  (6100, 'Whirlpool', 0),
  (6211, 'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES/Serpent/Twofish', 0),
  (6212, 'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish/Serpent-AES/Twofish-Serpent', 0),
  (6213, 'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish-Serpent/Serpent-Twofish-AES', 0),
  (6221, 'TrueCrypt 5.0+ SHA512 + AES/Serpent/Twofish', 0),
  (6222, 'TrueCrypt 5.0+ SHA512 + AES-Twofish/Serpent-AES/Twofish-Serpent', 0),
  (6223, 'TrueCrypt 5.0+ SHA512 + AES-Twofish-Serpent/Serpent-Twofish-AES', 0),
  (6231, 'TrueCrypt 5.0+ Whirlpool + AES/Serpent/Twofish', 0),
  (6232, 'TrueCrypt 5.0+ Whirlpool + AES-Twofish/Serpent-AES/Twofish-Serpent', 0),
  (6233, 'TrueCrypt 5.0+ Whirlpool + AES-Twofish-Serpent/Serpent-Twofish-AES', 0),
  (6241, 'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES/Serpent/Twofish + boot', 0),
  (6242, 'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish/Serpent-AES/Twofish-Serpent + boot', 0),
  (6243, 'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish-Serpent/Serpent-Twofish-AES + boot', 0),
  (6300, 'AIX {smd5}', 0),
  (6400, 'AIX {ssha256}', 0),
  (6500, 'AIX {ssha512}', 0),
  (6600, '1Password, Agile Keychain', 0),
  (6700, 'AIX {ssha1}', 0),
  (6800, 'Lastpass', 1),
  (6900, 'GOST R 34.11-94', 0),
  (7000, 'Fortigate (FortiOS)', 0),
  (7100, 'OS X v10.8 / v10.9', 0),
  (7200, 'GRUB 2', 0),
  (7300, 'IPMI2 RAKP HMAC-SHA1', 1),
  (7400, 'sha256crypt, SHA256(Unix)', 0),
  (7500, 'Kerberos 5 AS-REQ Pre-Auth', 0),
  (7700, 'SAP CODVN B (BCODE)', 0),
  (7800, 'SAP CODVN F/G (PASSCODE)', 0),
  (7900, 'Drupal7', 0),
  (8000, 'Sybase ASE', 0),
  (8100, 'Citrix Netscaler', 0),
  (8200, '1Password, Cloud Keychain', 0),
  (8300, 'DNSSEC (NSEC3)', 1),
  (8400, 'WBB3, Woltlab Burning Board 3', 1),
  (8500, 'RACF', 0),
  (8600, 'Lotus Notes/Domino 5', 0),
  (8700, 'Lotus Notes/Domino 6', 0),
  (8800, 'Android FDE <= 4.3', 0),
  (8900, 'scrypt', 1),
  (9000, 'Password Safe v2', 0),
  (9100, 'Lotus Notes/Domino', 0),
  (9200, 'Cisco $8$', 0),
  (9300, 'Cisco $9$', 0),
  (9400, 'Office 2007', 0),
  (9500, 'Office 2010', 0),
  (9600, 'Office 2013', 0),
  (9700, 'MS Office ⇐ 2003 MD5 + RC4, oldoffice$0, oldoffice$1', 0),
  (9710, 'MS Office <= 2003 $0/$1, MD5 + RC4, collider #1', 0),
  (9720, 'MS Office <= 2003 $0/$1, MD5 + RC4, collider #2', 0),
  (9800, 'MS Office ⇐ 2003 SHA1 + RC4, oldoffice$3, oldoffice$4', 0),
  (9810, 'MS Office <= 2003 $3, SHA1 + RC4, collider #1', 0),
  (9820, 'MS Office <= 2003 $3, SHA1 + RC4, collider #2', 0),
  (9900, 'Radmin2', 0),
  (10000, 'Django (PBKDF2-SHA256)', 0),
  (10100, 'SipHash', 1),
  (10200, 'Cram MD5', 0),
  (10300, 'SAP CODVN H (PWDSALTEDHASH) iSSHA-1', 0),
  (10400, 'PDF 1.1 - 1.3 (Acrobat 2 - 4)', 0),
  (10410, 'PDF 1.1 - 1.3 (Acrobat 2 - 4), collider #1', 0),
  (10420, 'PDF 1.1 - 1.3 (Acrobat 2 - 4), collider #2', 0),
  (10500, 'PDF 1.4 - 1.6 (Acrobat 5 - 8)', 0),
  (10600, 'PDF 1.7 Level 3 (Acrobat 9)', 0),
  (10700, 'PDF 1.7 Level 8 (Acrobat 10 - 11)', 0),
  (10800, 'SHA384', 0),
  (10900, 'PBKDF2-HMAC-SHA256', 1),
  (11000, 'PrestaShop', 1),
  (11100, 'PostgreSQL Challenge-Response Authentication (MD5)', 0),
  (11200, 'MySQL Challenge-Response Authentication (SHA1)', 0),
  (11300, 'Bitcoin/Litecoin wallet.dat', 0),
  (11400, 'SIP digest authentication (MD5)', 0),
  (11500, 'CRC32', 1),
  (11600, '7-Zip', 0),
  (11700, 'GOST R 34.11-2012 (Streebog) 256-bit', 0),
  (11800, 'GOST R 34.11-2012 (Streebog) 512-bit', 0),
  (11900, 'PBKDF2-HMAC-MD5', 1),
  (12000, 'PBKDF2-HMAC-SHA1', 1),
  (12001, 'Atlassian (PBKDF2-HMAC-SHA1)', 0),
  (12100, 'PBKDF2-HMAC-SHA512', 1),
  (12200, 'eCryptfs', 0),
  (12300, 'Oracle T: Type (Oracle 12+)', 0),
  (12400, 'BSDiCrypt, Extended DES', 0),
  (12500, 'RAR3-hp', 0),
  (12600, 'ColdFusion 10+', 1),
  (12700, 'Blockchain, My Wallet', 0),
  (12800, 'MS-AzureSync PBKDF2-HMAC-SHA256', 0),
  (12900, 'Android FDE (Samsung DEK)', 0),
  (13000, 'RAR5', 0),
  (13100, 'Kerberos 5 TGS-REP etype 23', 0),
  (13200, 'AxCrypt', 0),
  (13300, 'AxCrypt in memory SHA1', 0),
  (13400, 'Keepass 1/2 AES/Twofish with/without keyfile', 0),
  (13500, 'PeopleSoft PS_TOKEN', 1),
  (13600, 'WinZip', 0),
  (13711, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + AES, Serpent, Twofish', 0),
  (13712, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + AES-Twofish, Serpent-AES, Twofish-Serpent', 0),
  (13713, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + Serpent-Twofish-AES', 0),
  (13721, 'VeraCrypt PBKDF2-HMAC-SHA512 + AES, Serpent, Twofish', 0),
  (13722, 'VeraCrypt PBKDF2-HMAC-SHA512 + AES-Twofish, Serpent-AES, Twofish-Serpent', 0),
  (13723, 'VeraCrypt PBKDF2-HMAC-SHA512 + Serpent-Twofish-AES', 0),
  (13731, 'VeraCrypt PBKDF2-HMAC-Whirlpool + AES, Serpent, Twofish', 0),
  (13732, 'VeraCrypt PBKDF2-HMAC-Whirlpool + AES-Twofish, Serpent-AES, Twofish-Serpent', 0),
  (13733, 'VeraCrypt PBKDF2-HMAC-Whirlpool + Serpent-Twofish-AES', 0),
  (13751, 'VeraCrypt PBKDF2-HMAC-SHA256 + AES, Serpent, Twofish', 0),
  (13752, 'VeraCrypt PBKDF2-HMAC-SHA256 + AES-Twofish, Serpent-AES, Twofish-Serpent', 0),
  (13753, 'VeraCrypt PBKDF2-HMAC-SHA256 + Serpent-Twofish-AES', 0),
  (13800, 'Windows 8+ phone PIN/Password', 1),
  (13900, 'OpenCart', 1),
  (14000, 'DES (PT = $salt, key = $pass)', 1),
  (14100, '3DES (PT = $salt, key = $pass)', 1),
  (14400, 'sha1(CX)', 1),
  (14600, 'LUKS 10', 0),
  (14700, 'iTunes Backup < 10.0 11', 0),
  (14800, 'iTunes Backup >= 10.0 11', 0),
  (14900, 'Skip32 12', 1),
  (15000, 'FileZilla Server >= 0.9.55', 1),
  (15100, 'Juniper/NetBSD sha1crypt', 0),
  (15200, 'Blockchain, My Wallet, V2', 0),
  (15300, 'DPAPI masterkey file v1 and v2', 0),
  (15400, 'ChaCha20', 0),
  (15500, 'JKS Java Key Store Private Keys (SHA1)', 0),
  (15600, 'Ethereum Wallet, PBKDF2-HMAC-SHA256', 0),
  (15700, 'Ethereum Wallet, SCRYPT', 0),
  (15900, 'DPAPI master key file version 2 + Active Directory domain context', 0),
  (16000, 'Tripcode', 0),
  (16100, 'TACACS+', 0),
  (16200, 'Apple Secure Notes', 0),
  (16300, 'Ethereum Pre-Sale Wallet, PBKDF2-HMAC-SHA256', 0),
  (16400, 'CRAM-MD5 Dovecot', 0),
  (16500, 'JWT (JSON Web Token)', 0),
  (16600, 'Electrum Wallet (Salt-Type 1-3)', 0),
  (16700, 'FileVault 2', 0),
  (16800, 'WPA-PMKID-PBKDF2', 0),
  (16801, 'WPA-PMKID-PMK', 0),
  (16900, 'Ansible Vault', 0),
  (99999, 'Plaintext', 0);

CREATE TABLE `LogEntry` (
  `logEntryId` INT(11)                      NOT NULL,
  `issuer`     VARCHAR(50)
               COLLATE utf8_unicode_ci      NOT NULL,
  `issuerId`   VARCHAR(50)
               COLLATE utf8_unicode_ci      NOT NULL,
  `level`      VARCHAR(50)
               COLLATE utf8_unicode_ci      NOT NULL,
  `message`    TEXT COLLATE utf8_unicode_ci NOT NULL,
  `time`       INT(11)                      NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `NotificationSetting` (
  `notificationSettingId` INT(11)                 NOT NULL,
  `action`                VARCHAR(50)
                          COLLATE utf8_unicode_ci NOT NULL,
  `objectId`              INT(11)                 NULL,
  `notification`          VARCHAR(50)
                          COLLATE utf8_unicode_ci NOT NULL,
  `userId`                INT(11)                 NOT NULL,
  `receiver`              VARCHAR(256)
                          COLLATE utf8_unicode_ci NOT NULL,
  `isActive`              TINYINT(4)              NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `Pretask` (
  `pretaskId`           INT(11)                 NOT NULL,
  `taskName`            VARCHAR(100)
                        COLLATE utf8_unicode_ci NOT NULL,
  `attackCmd`           VARCHAR(256)
                        COLLATE utf8_unicode_ci NOT NULL,
  `chunkTime`           INT(11)                 NOT NULL,
  `statusTimer`         INT(11)                 NOT NULL,
  `color`               VARCHAR(20)
                        COLLATE utf8_unicode_ci NULL,
  `isSmall`             INT(11)                 NOT NULL,
  `isCpuTask`           INT(11)                 NOT NULL,
  `useNewBench`         INT(11)                 NOT NULL,
  `priority`            INT(11)                 NOT NULL,
  `isMaskImport`        INT(11)                 NOT NULL,
  `crackerBinaryTypeId` INT(11)                 NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `RegVoucher` (
  `regVoucherId` INT(11)                 NOT NULL,
  `voucher`      VARCHAR(100)
                 COLLATE utf8_unicode_ci NOT NULL,
  `time`         INT(11)                 NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `RightGroup` (
  `rightGroupId` INT(11)                 NOT NULL,
  `groupName`    VARCHAR(50)
                 COLLATE utf8_unicode_ci NOT NULL,
  `permissions`  TEXT                    NOT NULL
)
  ENGINE = InnoDB;

INSERT INTO `RightGroup` (`rightGroupId`, `groupName`, `permissions`) VALUES
  (1, 'Administrator', 'ALL');

CREATE TABLE `Session` (
  `sessionId`        INT(11)                 NOT NULL,
  `userId`           INT(11)                 NOT NULL,
  `sessionStartDate` INT(11)                 NOT NULL,
  `lastActionDate`   INT(11)                 NOT NULL,
  `isOpen`           INT(11)                 NOT NULL,
  `sessionLifetime`  INT(11)                 NOT NULL,
  `sessionKey`       VARCHAR(256)
                     COLLATE utf8_unicode_ci NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `StoredValue` (
  `storedValueId` VARCHAR(50)
                  COLLATE utf8_unicode_ci NOT NULL,
  `val`           VARCHAR(256)
                  COLLATE utf8_unicode_ci NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `Supertask` (
  `supertaskId`   INT(11)                 NOT NULL,
  `supertaskName` VARCHAR(50)
                  COLLATE utf8_unicode_ci NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `SupertaskPretask` (
  `supertaskPretaskId` INT(11) NOT NULL,
  `supertaskId`        INT(11) NOT NULL,
  `pretaskId`          INT(11) NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `Task` (
  `taskId`              INT(11)                 NOT NULL,
  `taskName`            VARCHAR(256)
                        COLLATE utf8_unicode_ci NOT NULL,
  `attackCmd`           VARCHAR(256)
                        COLLATE utf8_unicode_ci NOT NULL,
  `chunkTime`           INT(11)                 NOT NULL,
  `statusTimer`         INT(11)                 NOT NULL,
  `keyspace`            BIGINT(20)              NOT NULL,
  `keyspaceProgress`    BIGINT(20)              NOT NULL,
  `priority`            INT(11)                 NOT NULL,
  `color`               VARCHAR(20)
                        COLLATE utf8_unicode_ci NULL,
  `isSmall`             INT(11)                 NOT NULL,
  `isCpuTask`           INT(11)                 NOT NULL,
  `useNewBench`         INT(11)                 NOT NULL,
  `skipKeyspace`        BIGINT(20)              NOT NULL,
  `crackerBinaryId`     INT(11) DEFAULT NULL,
  `crackerBinaryTypeId` INT(11)                 NULL,
  `taskWrapperId`       INT(11)                 NOT NULL,
  `isArchived`          INT(11)                 NOT NULL,
  `isPrince`            INT(11)                 NOT NULL,
  `notes`               TEXT                    NOT NULL,
  `staticChunks`        INT(11)                 NOT NULL,
  `chunkSize`           BIGINT(20)              NOT NULL,
  `forcePipe`           INT(11)                 NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `TaskDebugOutput` (
  `taskDebugOutputId` int(11) NOT NULL,
  `taskId` int(11) NOT NULL,
  `output` varchar(256) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `TaskWrapper` (
  `taskWrapperId`   INT(11)      NOT NULL,
  `priority`        INT(11)      NOT NULL,
  `taskType`        INT(11)      NOT NULL,
  `hashlistId`      INT(11)      NOT NULL,
  `accessGroupId`   INT(11) DEFAULT NULL,
  `taskWrapperName` VARCHAR(100) NOT NULL,
  `isArchived`      INT(11)      NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `User` (
  `userId`             INT(11)                 NOT NULL,
  `username`           VARCHAR(100)
                       COLLATE utf8_unicode_ci NOT NULL,
  `email`              VARCHAR(150)
                       COLLATE utf8_unicode_ci NOT NULL,
  `passwordHash`       VARCHAR(256)
                       COLLATE utf8_unicode_ci NOT NULL,
  `passwordSalt`       VARCHAR(256)
                       COLLATE utf8_unicode_ci NOT NULL,
  `isValid`            INT(11)                 NOT NULL,
  `isComputedPassword` INT(11)                 NOT NULL,
  `lastLoginDate`      INT(11)                 NOT NULL,
  `registeredSince`    INT(11)                 NOT NULL,
  `sessionLifetime`    INT(11)                 NOT NULL,
  `rightGroupId`       INT(11)                 NOT NULL,
  `yubikey`            VARCHAR(256)
                       COLLATE utf8_unicode_ci DEFAULT NULL,
  `otp1`               VARCHAR(256)
                       COLLATE utf8_unicode_ci DEFAULT NULL,
  `otp2`               VARCHAR(256)
                       COLLATE utf8_unicode_ci DEFAULT NULL,
  `otp3`               VARCHAR(256)
                       COLLATE utf8_unicode_ci DEFAULT NULL,
  `otp4`               VARCHAR(256)
                       COLLATE utf8_unicode_ci DEFAULT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `Zap` (
  `zapId`      INT(11)                 NOT NULL,
  `hash`       VARCHAR(1024)
               COLLATE utf8_unicode_ci NOT NULL,
  `solveTime`  INT(11)                 NOT NULL,
  `agentId`    INT(11)                 NULL,
  `hashlistId` INT(11)                 NOT NULL
)
  ENGINE = InnoDB;

CREATE TABLE `ApiKey` (
  `apiKeyId` int(11) NOT NULL,
  `startValid` bigint(20) NOT NULL,
  `endValid` bigint(20) NOT NULL,
  `accessKey` varchar(256) NOT NULL,
  `accessCount` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `apiGroupId` int(11) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `ApiGroup` (
  `apiGroupId` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `permissions` text NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `FileDownload` (
  `fileDownloadId` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `fileId` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB;

INSERT INTO `ApiGroup` ( `apiGroupId`, `name`, `permissions`) VALUES (1, 'Administrators', 'ALL');


ALTER TABLE `ApiKey`
  ADD PRIMARY KEY (`apiKeyId`);

ALTER TABLE `ApiGroup`
  ADD PRIMARY KEY (`apiGroupId`);

ALTER TABLE `FileDownload`
  ADD PRIMARY KEY (`fileDownloadId`);


ALTER TABLE `FileDelete`
  ADD PRIMARY KEY (`fileDeleteId`);

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

ALTER TABLE `Assignment`
  ADD PRIMARY KEY (`assignmentId`),
  ADD KEY `taskId` (`taskId`),
  ADD KEY `agentId` (`agentId`);

ALTER TABLE `Chunk`
  ADD PRIMARY KEY (`chunkId`),
  ADD KEY `taskId` (`taskId`),
  ADD KEY `agentId` (`agentId`);

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
  ADD KEY `hash` (`hash`);

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
  ADD KEY `accessGroupId` (`accessGroupId`);

ALTER TABLE `User`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `rightGroupId` (`rightGroupId`);

ALTER TABLE `Zap`
  ADD PRIMARY KEY (`zapId`),
  ADD KEY `agentId` (`agentId`),
  ADD KEY `hashlistId` (`hashlistId`);


ALTER TABLE `ApiKey`
  MODIFY `apiKeyId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ApiGroup`
  MODIFY `apiGroupId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `FileDownload`
  MODIFY `fileDownloadId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `FileDelete`
  MODIFY `fileDeleteId` int(11) NOT NULL AUTO_INCREMENT;

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
  AUTO_INCREMENT = 3;

ALTER TABLE `AgentError`
  MODIFY `agentErrorId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `AgentStat`
  MODIFY `agentStatId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `AgentZap`
  MODIFY `agentZapId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Assignment`
  MODIFY `assignmentId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Chunk`
  MODIFY `chunkId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Config`
  MODIFY `configId` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 22;

ALTER TABLE `ConfigSection`
  MODIFY `configSectionId` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 5;

ALTER TABLE `CrackerBinary`
  MODIFY `crackerBinaryId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `CrackerBinaryType`
  MODIFY `crackerBinaryTypeId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `File`
  MODIFY `fileId` INT(11) NOT NULL AUTO_INCREMENT;

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
  AUTO_INCREMENT = 6;

ALTER TABLE `Session`
  MODIFY `sessionId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Supertask`
  MODIFY `supertaskId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `SupertaskPretask`
  MODIFY `supertaskPretaskId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Task`
  MODIFY `taskId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `TaskDebugOutput`
  MODIFY `taskDebugOutputId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `TaskWrapper`
  MODIFY `taskWrapperId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `User`
  MODIFY `userId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Zap`
  MODIFY `zapId` INT(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `AccessGroupAgent`
  ADD CONSTRAINT `AccessGroupAgent_ibfk_1` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`),
  ADD CONSTRAINT `AccessGroupAgent_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

ALTER TABLE `AccessGroupUser`
  ADD CONSTRAINT `AccessGroupUser_ibfk_1` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`),
  ADD CONSTRAINT `AccessGroupUser_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

ALTER TABLE `Agent`
  ADD CONSTRAINT `Agent_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

ALTER TABLE `AgentError`
  ADD CONSTRAINT `AgentError_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `AgentError_ibfk_2` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`);

ALTER TABLE `AgentStat`
  ADD CONSTRAINT `AgentStat_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

ALTER TABLE `AgentZap`
  ADD CONSTRAINT `AgentZap_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `AgentZap_ibfk_2` FOREIGN KEY (`lastZapId`) REFERENCES `Zap` (`zapId`);

ALTER TABLE `Assignment`
  ADD CONSTRAINT `Assignment_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`),
  ADD CONSTRAINT `Assignment_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

ALTER TABLE `Chunk`
  ADD CONSTRAINT `Chunk_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`),
  ADD CONSTRAINT `Chunk_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

ALTER TABLE `Config`
  ADD CONSTRAINT `Config_ibfk_1` FOREIGN KEY (`configSectionId`) REFERENCES `ConfigSection` (`configSectionId`);

ALTER TABLE `CrackerBinary`
  ADD CONSTRAINT `CrackerBinary_ibfk_1` FOREIGN KEY (`crackerBinaryTypeId`) REFERENCES `CrackerBinaryType` (`crackerBinaryTypeId`);

ALTER TABLE `FilePretask`
  ADD CONSTRAINT `FilePretask_ibfk_1` FOREIGN KEY (`fileId`) REFERENCES `File` (`fileId`),
  ADD CONSTRAINT `FilePretask_ibfk_2` FOREIGN KEY (`pretaskId`) REFERENCES `Pretask` (`pretaskId`);

ALTER TABLE `FileTask`
  ADD CONSTRAINT `FileTask_ibfk_1` FOREIGN KEY (`fileId`) REFERENCES `File` (`fileId`),
  ADD CONSTRAINT `FileTask_ibfk_2` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`);

ALTER TABLE `Hash`
  ADD CONSTRAINT `Hash_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `Hash_ibfk_2` FOREIGN KEY (`chunkId`) REFERENCES `Chunk` (`chunkId`);

ALTER TABLE `HashBinary`
  ADD CONSTRAINT `HashBinary_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `HashBinary_ibfk_2` FOREIGN KEY (`chunkId`) REFERENCES `Chunk` (`chunkId`);

ALTER TABLE `Hashlist`
  ADD CONSTRAINT `Hashlist_ibfk_1` FOREIGN KEY (`hashTypeId`) REFERENCES `HashType` (`hashTypeId`);

ALTER TABLE `HashlistHashlist`
  ADD CONSTRAINT `HashlistHashlist_ibfk_1` FOREIGN KEY (`parentHashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `HashlistHashlist_ibfk_2` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`);

ALTER TABLE `NotificationSetting`
  ADD CONSTRAINT `NotificationSetting_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

ALTER TABLE `Session`
  ADD CONSTRAINT `Session_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

ALTER TABLE `SupertaskPretask`
  ADD CONSTRAINT `SupertaskPretask_ibfk_1` FOREIGN KEY (`supertaskId`) REFERENCES `Supertask` (`supertaskId`),
  ADD CONSTRAINT `SupertaskPretask_ibfk_2` FOREIGN KEY (`pretaskId`) REFERENCES `Pretask` (`pretaskId`);

ALTER TABLE `Task`
  ADD CONSTRAINT `Task_ibfk_1` FOREIGN KEY (`crackerBinaryId`) REFERENCES `CrackerBinary` (`crackerBinaryId`);

ALTER TABLE `TaskWrapper`
  ADD CONSTRAINT `TaskWrapper_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `TaskWrapper_ibfk_2` FOREIGN KEY (`accessGroupId`) REFERENCES `AccessGroup` (`accessGroupId`);

ALTER TABLE `User`
  ADD CONSTRAINT `User_ibfk_1` FOREIGN KEY (`rightGroupId`) REFERENCES `RightGroup` (`rightGroupId`);

ALTER TABLE `Zap`
  ADD CONSTRAINT `Zap_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `Zap_ibfk_2` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`);

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;