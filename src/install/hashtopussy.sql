CREATE TABLE `Agent` (
  `agentId`      INT(11)               NOT NULL,
  `agentName`    VARCHAR(200)
                 COLLATE utf8_bin      NOT NULL,
  `uid`          VARCHAR(200)
                 COLLATE utf8_bin      NOT NULL,
  `os`           INT(11)               NOT NULL,
  `gpus`         TEXT COLLATE utf8_bin NOT NULL,
  `hcVersion`    VARCHAR(20)
                 COLLATE utf8_bin      NOT NULL,
  `cmdPars`      VARCHAR(200)
                 COLLATE utf8_bin      NOT NULL,
  `ignoreErrors` INT(11)               NOT NULL,
  `isActive`     INT(11)               NOT NULL,
  `isTrusted`    INT(11)               NOT NULL,
  `token`        VARCHAR(50)
                 COLLATE utf8_bin      NOT NULL,
  `lastAct`      VARCHAR(20)           NOT NULL,
  `lastTime`     INT(11)               NOT NULL,
  `lastIp`       VARCHAR(50)
                 COLLATE utf8_bin      NOT NULL,
  `userId`       INT(11)               NULL,
  `cpuOnly`      INT(11)               NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `Zap` (
  `zapId`      INT(11) AUTO_INCREMENT PRIMARY KEY NOT NULL,
  `hash`       VARCHAR(512)                       NOT NULL,
  `solveTime`  INT(11)                            NOT NULL,
  `agentId`    INT(11)                            NULL,
  `hashlistId` INT(11)                            NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `AgentZap` (
  `agentId`      INT(11) AUTO_INCREMENT PRIMARY KEY NOT NULL,
  `lastZapId`       INT(11)                       NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `StoredValue` (
  `storedValueId` VARCHAR(127) PRIMARY KEY NOT NULL,
  `val`           VARCHAR(127)             NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `LogEntry` (
  `logEntryId` INT(11) AUTO_INCREMENT PRIMARY KEY NOT NULL,
  `issuer` VARCHAR(20) NOT NULL,
  `issuerId` VARCHAR(30) NOT NULL,
  `level` VARCHAR(20) NOT NULL,
  `message` TEXT NOT NULL,
  `time` INT(11) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `Assignment` (
  `assignmentId` INT(11)    NOT NULL,
  `taskId`       INT(11)    NOT NULL,
  `agentId`      INT(11)    NOT NULL,
  `benchmark`    VARCHAR(40) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `Chunk` (
  `chunkId`      INT(11)    NOT NULL,
  `taskId`       INT(11)    NOT NULL,
  `skip`         BIGINT(20) NOT NULL,
  `length`       BIGINT(20) NOT NULL,
  `agentId`      INT(11)    NULL,
  `dispatchTime` INT(11)    NOT NULL,
  `progress`     BIGINT(20) NOT NULL,
  `rprogress`    INT(11)    NOT NULL,
  `state`        INT(11)    NOT NULL,
  `cracked`      INT(11)    NOT NULL,
  `solveTime`    INT(11)    NOT NULL,
  `speed`        BIGINT(20)    NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `Config` (
  `configId` INT(11)          NOT NULL,
  `item`     VARCHAR(100)
             COLLATE utf8_bin NOT NULL,
  `value`    VARCHAR(200)
             COLLATE utf8_bin NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

INSERT INTO `Config` (`configId`, `item`, `value`) VALUES
  (1, 'agenttimeout', '30'),
  (2, 'benchtime', '30'),
  (3, 'chunktime', '600'),
  (4, 'chunktimeout', '30'),
  (9, 'fieldseparator', ':'),
  (10, 'hashlistAlias', '#HL#'),
  (11, 'statustimer', '5'),
  (12, 'timefmt', 'd.m.Y, H:i:s'),
  (13, 'blacklistChars', '&|`"\''),
  (14, 'numLogEntries', '5000'),
  (15, 'disptolerance', '20'),
  (16, 'batchSize', '10000');

CREATE TABLE `AgentError` (
  `agentErrorId` INT(11)               NOT NULL,
  `agentId`      INT(11)               NOT NULL,
  `taskId`       INT(11)               NOT NULL,
  `time`         INT(11)               NOT NULL,
  `error`        TEXT COLLATE utf8_bin NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `File` (
  `fileId`   INT(11)          NOT NULL,
  `filename` VARCHAR(200)
             COLLATE utf8_bin NOT NULL,
  `size`     BIGINT(20)       NOT NULL,
  `secret`   INT(11)          NOT NULL,
  `fileType` INT(11)          NOT NULL
  COMMENT '0 -> dict, 1 -> rule'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `Hash` (
  `hashId`     INT(11)          NOT NULL,
  `hashlistId` INT(11)          NOT NULL,
  `hash`       VARCHAR(512)
               COLLATE utf8_bin NOT NULL,
  `salt`       VARCHAR(200)
               COLLATE utf8_bin NOT NULL,
  `plaintext`  VARCHAR(200)
               COLLATE utf8_bin NOT NULL,
  `time`       INT(11)          NOT NULL,
  `chunkId`    INT(11)    DEFAULT NULL,
  `isCracked`  TINYINT(1) DEFAULT 0
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `HashBinary` (
  `hashBinaryId` INT(11)          NOT NULL,
  `hashlistId`   INT(11)          NOT NULL,
  `essid`        VARCHAR(100)
                 COLLATE utf8_bin NOT NULL,
  `hash`         LONGBLOB         NOT NULL,
  `plaintext`    VARCHAR(200)
                 COLLATE utf8_bin DEFAULT NULL,
  `time`         INT(11)          NOT NULL,
  `chunkId`      INT(11)    DEFAULT NULL,
  `isCracked`    TINYINT(1) DEFAULT 0
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `HashcatRelease` (
  `hashcatReleaseId` INT(11)          NOT NULL,
  `version`          VARCHAR(50)
                     COLLATE utf8_bin NOT NULL,
  `time`             INT(11)          NOT NULL,
  `url`              VARCHAR(200)
                     COLLATE utf8_bin NOT NULL,
  `rootdir`          VARCHAR(200)
                     COLLATE utf8_bin NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `Hashlist` (
  `hashlistId`    INT(11)          NOT NULL,
  `hashlistName`  VARCHAR(200)
                  COLLATE utf8_bin NOT NULL,
  `format`        INT(11)          NOT NULL
  COMMENT '0 -> text, 1 -> wpa, 2 -> bin',
  `hashTypeId`    INT(11)          NOT NULL,
  `hashCount`     INT(11)          NOT NULL,
  `saltSeparator` VARCHAR(10)      NOT NULL,
  `cracked`       INT(11)          NOT NULL,
  `secret`        INT(11)          NOT NULL,
  `hexSalt`       INT(11)          NOT NULL,
  `isSalted`      INT(11)          NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `AgentBinary` (
  `agentBinaryId`    INT(11)      NOT NULL,
  `type`         VARCHAR(30)  NOT NULL,
  `version`          VARCHAR(20) NOT NULL,
  `operatingSystems` VARCHAR(30)  NOT NULL,
  `filename`         VARCHAR(200) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `HashlistAgent` (
  `hashlistAgentId` INT(11) NOT NULL,
  `hashlistId`      INT(11) NOT NULL,
  `agentId`         INT(11) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `HashType` (
  `hashTypeId`  INT(11)          NOT NULL,
  `description` VARCHAR(200)
                COLLATE utf8_bin NOT NULL,
  `isSalted`    TINYINT NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`) VALUES
  (0,'MD5',0),
  (10,'md5($pass.$salt)',1),
  (20,'md5($salt.$pass)',1),
  (30,'md5(unicode($pass).$salt)',1),
  (40,'md5($salt.unicode($pass))',1),
  (50,'HMAC-MD5 (key = $pass)',1),
  (60,'HMAC-MD5 (key = $salt)',1),
  (100,'SHA1',0),
  (110,'sha1($pass.$salt)',1),
  (120,'sha1($salt.$pass)',1),
  (130,'sha1(unicode($pass).$salt)',1),
  (140,'sha1($salt.unicode($pass))',1),
  (150,'HMAC-SHA1 (key = $pass)',1),
  (160,'HMAC-SHA1 (key = $salt)',1),
  (190,'sha1(LinkedIn)',0),
  (200,'MySQL323',0),
  (300,'MySQL4.1/MySQL5+',0),
  (400,'phpass, MD5(Wordpress), MD5(Joomla), MD5(phpBB3)',0),
  (500,'md5crypt, MD5(Unix), FreeBSD MD5, Cisco-IOS MD5 2',0),
  (501,'Juniper IVE',0),
  (900,'MD4',0),
  (1000,'NTLM',0),
  (1100,'Domain Cached Credentials (DCC), MS Cache',1),
  (1300,'SHA-224',0),
  (1400,'SHA256',0),
  (1410,'sha256($pass.$salt)',1),
  (1420,'sha256($salt.$pass)',1),
  (1430,'sha256(unicode($pass).$salt)',1),
  (1431,'base64(sha256(unicode($pass)))',0),
  (1440,'sha256($salt.unicode($pass))',1),
  (1450,'HMAC-SHA256 (key = $pass)',1),
  (1460,'HMAC-SHA256 (key = $salt)',1),
  (1500,'descrypt, DES(Unix), Traditional DES',0),
  (1600,'md5apr1, MD5(APR), Apache MD5',0),
  (1700,'SHA512',0),
  (1710,'sha512($pass.$salt)',0),
  (1720,'sha512($salt.$pass)',1),
  (1730,'sha512(unicode($pass).$salt)',1),
  (1740,'sha512($salt.unicode($pass))',1),
  (1750,'HMAC-SHA512 (key = $pass)',1),
  (1760,'HMAC-SHA512 (key = $salt)',1),
  (1800,'sha512crypt, SHA512(Unix)',0),
  (2100,'Domain Cached Credentials 2 (DCC2), MS Cache',0),
  (2400,'Cisco-PIX MD5',0),
  (2410,'Cisco-ASA MD5',1),
  (2500,'WPA/WPA2',0),
  (2600,'md5(md5($pass))',0),
  (3000,'LM',0),
  (3100,'Oracle H: Type (Oracle 7+), DES(Oracle)',1),
  (3200,'bcrypt, Blowfish(OpenBSD)',0),
  (3300,'MD5(Sun)',0),
  (3500,'md5(md5(md5($pass)))',0),
  (3610,'md5(md5($salt).$pass)',1),
  (3710,'md5($salt.md5($pass))',1),
  (3720,'md5($pass.md5($salt))',1),
  (3800,'md5($salt.$pass.$salt)',1),
  (3910,'md5(md5($pass).md5($salt))',1),
  (4010,'md5($salt.md5($salt.$pass))',1),
  (4110,'md5($salt.md5($pass.$salt))',1),
  (4210,'md5($username.0.$pass)',1),
  (4300,'md5(strtoupper(md5($pass)))',0),
  (4400,'md5(sha1($pass))',0),
  (4500,'sha1(sha1($pass))',0),
  (4520,'sha1($salt.sha1($pass))',1),
  (4600,'sha1(sha1(sha1($pass)))',0),
  (4700,'sha1(md5($pass))',0),
  (4800,'MD5(Chap), iSCSI CHAP authentication',1),
  (4900,'sha1($salt.$pass.$salt)',1),
  (5000,'SHA-3(Keccak)',0),
  (5100,'Half MD5',0),
  (5200,'Password Safe v3',0),
  (5300,'IKE-PSK MD5',0),
  (5400,'IKE-PSK SHA1',0),
  (5500,'NetNTLMv1-VANILLA / NetNTLMv1+ESS',0),
  (5600,'NetNTLMv2',0),
  (5700,'Cisco-IOS SHA256',0),
  (5800,'Samsung Android Password/PIN',1),
  (6000,'RipeMD160',0),
  (6100,'Whirlpool',0),
  (6211,'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES/Serpent/Twofish',0),
  (6212,'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish/Serpent-AES/Twofish-Serpent',0),
  (6213,'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish-Serpent/Serpent-Twofish-AES',0),
  (6221,'TrueCrypt 5.0+ SHA512 + AES/Serpent/Twofish',0),
  (6222,'TrueCrypt 5.0+ SHA512 + AES-Twofish/Serpent-AES/Twofish-Serpent',0),
  (6223,'TrueCrypt 5.0+ SHA512 + AES-Twofish-Serpent/Serpent-Twofish-AES',0),
  (6231,'TrueCrypt 5.0+ Whirlpool + AES/Serpent/Twofish',0),
  (6232,'TrueCrypt 5.0+ Whirlpool + AES-Twofish/Serpent-AES/Twofish-Serpent',0),
  (6233,'TrueCrypt 5.0+ Whirlpool + AES-Twofish-Serpent/Serpent-Twofish-AES',0),
  (6241,'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES/Serpent/Twofish + boot',0),
  (6242,'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish/Serpent-AES/Twofish-Serpent + boot',0),
  (6243,'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish-Serpent/Serpent-Twofish-AES + boot',0),
  (6300,'AIX {smd5}',0),
  (6400,'AIX {ssha256}',0),
  (6500,'AIX {ssha512}',0),
  (6600,'1Password, Agile Keychain',0),
  (6700,'AIX {ssha1}',0),
  (6800,'Lastpass',1),
  (6900,'GOST R 34.11-94',0),
  (7000,'Fortigate (FortiOS)',0),
  (7100,'OS X v10.8 / v10.9',0),
  (7200,'GRUB 2',0),
  (7300,'IPMI2 RAKP HMAC-SHA1',1),
  (7400,'sha256crypt, SHA256(Unix)',0),
  (7500,'Kerberos 5 AS-REQ Pre-Auth',0),
  (7700,'SAP CODVN B (BCODE)',0),
  (7800,'SAP CODVN F/G (PASSCODE)',0),
  (7900,'Drupal7',0),
  (8000,'Sybase ASE',0),
  (8100,'Citrix Netscaler',0),
  (8200,'1Password, Cloud Keychain',0),
  (8300,'DNSSEC (NSEC3)',0),
  (8400,'WBB3, Woltlab Burning Board 3',0),
  (8500,'RACF',0),
  (8600,'Lotus Notes/Domino 5',0),
  (8700,'Lotus Notes/Domino 6',0),
  (8800,'Android FDE <= 4.3',0),
  (8900,'scrypt',0),
  (9000,'Password Safe v2',0),
  (9100,'Lotus Notes/Domino',0),
  (9200,'Cisco $8$',0),
  (9300,'Cisco $9$',0),
  (9400,'Office 2007',0),
  (9500,'Office 2010',0),
  (9600,'Office 2013',0),
  (9700,'MS Office ⇐ 2003 MD5 + RC4, oldoffice$0, oldoffice$1',0),
  (9800,'MS Office ⇐ 2003 SHA1 + RC4, oldoffice$3, oldoffice$4',0),
  (9900,'Radmin2',0),
  (10000,'Django (PBKDF2-SHA256)',0),
  (10100,'SipHash',0),
  (10200,'Cram MD5',0),
  (10300,'SAP CODVN H (PWDSALTEDHASH) iSSHA-1',0),
  (10400,'PDF 1.1 - 1.3 (Acrobat 2 - 4)',0),
  (10500,'PDF 1.4 - 1.6 (Acrobat 5 - 8)',0),
  (10600,'PDF 1.7 Level 3 (Acrobat 9)',0),
  (10700,'PDF 1.7 Level 8 (Acrobat 10 - 11)',0),
  (10800,'SHA384',0),
  (10900,'PBKDF2-HMAC-SHA256',0),
  (11000,'PrestaShop',0),
  (11100,'PostgreSQL Challenge-Response Authentication (MD5)',0),
  (11200,'MySQL Challenge-Response Authentication (SHA1)',0),
  (11300,'Bitcoin/Litecoin wallet.dat',0),
  (11400,'SIP digest authentication (MD5)',0),
  (11500,'CRC32',1),
  (11600,'7-Zip',0),
  (11700,'GOST R 34.11-2012 (Streebog) 256-bit',0),
  (11800,'GOST R 34.11-2012 (Streebog) 512-bit',0),
  (11900,'PBKDF2-HMAC-MD5',0),
  (12000,'PBKDF2-HMAC-SHA1',0),
  (12100,'PBKDF2-HMAC-SHA512',0),
  (12200,'eCryptfs',0),
  (12300,'Oracle T: Type (Oracle 12+)',0),
  (12400,'BSDiCrypt, Extended DES',0),
  (12500,'RAR3-hp',0),
  (12600,'ColdFusion 10+',0),
  (12700,'Blockchain, My Wallet',0),
  (12800,'MS-AzureSync PBKDF2-HMAC-SHA256',0),
  (12900,'Android FDE (Samsung DEK)',0),
  (13000,'RAR5',0),
  (13100,'Kerberos 5 TGS-REP etype 23',0),
  (13200,'AxCrypt',0),
  (13300,'AxCrypt in memory SHA1',0),
  (13400,'Keepass 1/2 AES/Twofish with/without keyfile',0),
  (13500,'PeopleSoft PS_TOKEN',0),
  (13600,'WinZip',0),
  (13800,'Windows 8+ phone PIN/Password',0),
  (13900,'OpenCart',1),
  (14000,'DES (PT = $salt, key = $pass)',1),
  (14100,'3DES (PT = $salt, key = $pass)',1),
  (14400,'sha1(CX)',1),
  (14600,'LUKS 10',0),
  (14700,'iTunes Backup < 10.0 11',0),
  (14800,'iTunes Backup >= 10.0 11',0),
  (14900,'Skip32 12',1),
  (99999,'Plaintext',0),
  (11,'Joomla < 2.5.18',1),
  (12,'PostgreSQL',1),
  (21,'osCommerce, xt:Commerce',1),
  (22,'Juniper Netscreen/SSG (ScreenOS)',1),
  (23,'Skype',1),
  (101,'nsldap, SHA-1(Base64), Netscape LDAP SHA',0),
  (111,'nsldaps, SSHA-1(Base64), Netscape LDAP SSHA',0),
  (112,'Oracle S: Type (Oracle 11+)',1),
  (121,'SMF >= v1.1',1),
  (122,'OS X v10.4, v10.5, v10.6',0),
  (123,'EPi',0),
  (124,'Django (SHA-1)',0),
  (125,'ArubaOS',0),
  (131,'MSSQL(2000)',0),
  (132,'MSSQL(2005)',0),
  (133,'PeopleSoft',0),
  (141,'EPiServer 6.x < v4',0),
  (1411,'SSHA-256(Base64), LDAP {SSHA256}',0),
  (1421,'hMailServer',0),
  (1441,'EPiServer 6.x >= v4',0),
  (1711,'SSHA-512(Base64), LDAP {SSHA512}',0),
  (1722,'OS X v10.7',0),
  (1731,'MSSQL(2012), MSSQL(2014)',0),
  (2611,'vBulletin < v3.8.5',1),
  (2612,'PHPS',0),
  (2711,'vBulletin >= v3.8.5',1),
  (2811,'IPB2+, MyBB1.2+',1),
  (3711,'Mediawiki B type',0),
  (3721,'WebEdition CMS',1),
  (4521,'Redmine Project Management Web App',0),
  (4522,'PunBB',0);

CREATE TABLE `RegVoucher` (
  `regVoucherId` INT(11)          NOT NULL,
  `voucher`      VARCHAR(50)
                 COLLATE utf8_bin NOT NULL,
  `time`         INT(11)          NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `RightGroup` (
  `rightGroupId` INT(11)          NOT NULL,
  `groupName`    VARCHAR(30)
                 COLLATE utf8_bin NOT NULL,
  `level`        INT(11)          NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

INSERT INTO `RightGroup` (`rightGroupId`, `groupName`, `level`) VALUES
  (1, 'View User', 1),
  (2, 'Read Only User', 5),
  (3, 'Normal User', 20),
  (4, 'Superuser', 30),
  (5, 'Administrator', 50);

INSERT INTO `AgentBinary` (`agentBinaryId`, `type`, `operatingSystems`, `filename`, `version`)
VALUES (1, 'csharp', 'Windows', 'hashtopussy.exe', '0.43.13');

CREATE TABLE `Session` (
  `sessionId`        INT(11)      NOT NULL,
  `userId`           INT(11)      NOT NULL,
  `sessionStartDate` INT(11)      NOT NULL,
  `lastActionDate`   INT(11)      NOT NULL,
  `isOpen`           TINYINT(4)   NOT NULL,
  `sessionLifetime`  INT(11)      NOT NULL,
  `sessionKey`       VARCHAR(500) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

CREATE TABLE `SuperHashlistHashlist` (
  `superHashlistHashlistId` INT(11) NOT NULL,
  `superHashlistId`         INT(11) NOT NULL,
  `hashlistId`              INT(11) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `Supertask` (
  `supertaskId`   INT(11)          NOT NULL,
  `supertaskName` VARCHAR(100)
                  COLLATE utf8_bin NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `SupertaskTask` (
  `supertaskTaskId` INT(11) NOT NULL,
  `supertaskId`     INT(11) NOT NULL,
  `taskId`          INT(11) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `Task` (
  `taskId`      INT(11)          NOT NULL,
  `taskName`    VARCHAR(200)
                COLLATE utf8_bin NOT NULL,
  `attackCmd`   VARCHAR(512)
                COLLATE utf8_bin NOT NULL,
  `hashlistId`  INT(11)          NULL,
  `chunkTime`   INT(11)          NOT NULL,
  `statusTimer` INT(11)          NOT NULL,
  `keyspace`    BIGINT(20)       NOT NULL,
  `progress`    BIGINT(20)       NOT NULL,
  `priority`    INT(11)          NOT NULL,
  `color`       VARCHAR(10)
                COLLATE utf8_bin NULL,
  `isSmall`     INT(11)          NOT NULL,
  `isCpuTask`   INT(11)          NOT NULL,
  `useNewBench` INT(11)          NOT NULL,
  `skipKeyspace` BIGINT(20)      NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `TaskFile` (
  `taskFileId` INT(11) NOT NULL,
  `taskId`     INT(11) NOT NULL,
  `fileId`     INT(11) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `User` (
  `userId`             INT(11)          NOT NULL,
  `username`           VARCHAR(50)
                       COLLATE utf8_bin NOT NULL,
  `passwordHash`       VARCHAR(512)
                       COLLATE utf8_bin NOT NULL,
  `email`              VARCHAR(512)
                       COLLATE utf8_bin NOT NULL,
  `passwordSalt`       VARCHAR(512)
                       COLLATE utf8_bin NOT NULL,
  `isValid`            TINYINT(11)      NOT NULL,
  `isComputedPassword` TINYINT(11)      NOT NULL,
  `lastLoginDate`      INT(11)          NOT NULL,
  `registeredSince`    INT(11)          NOT NULL,
  `sessionLifetime`    INT(11)          NOT NULL DEFAULT '600',
  `rightGroupId`       INT(11)          NOT NULL DEFAULT '1'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin;

CREATE TABLE `NotificationSetting` (
  `notificationSettingId` int(11) NOT NULL,
  `action` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `objectId` int(11) NULL,
  `notification` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `userId` int(11) NOT NULL,
  `receiver` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `isActive` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `NotificationSetting`
  ADD PRIMARY KEY (`notificationSettingId`),
  ADD KEY `NotificationSetting_ibfk_1` (`userId`);

ALTER TABLE `NotificationSetting`
  MODIFY `notificationSettingId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Agent`
  ADD PRIMARY KEY (`agentId`),
  ADD KEY `userId` (`userId`);

ALTER TABLE `AgentBinary`
  ADD PRIMARY KEY (`agentBinaryId`);

ALTER TABLE `Assignment`
  ADD PRIMARY KEY (`assignmentId`),
  ADD KEY `taskId` (`taskId`),
  ADD KEY `agentId` (`agentId`);

ALTER TABLE `Chunk`
  ADD PRIMARY KEY (`chunkId`),
  ADD KEY `taskId` (`taskId`),
  ADD KEY `agentId` (`agentId`);

ALTER TABLE `Config`
  ADD PRIMARY KEY (`configId`);

ALTER TABLE `AgentError`
  ADD PRIMARY KEY (`agentErrorId`),
  ADD KEY `agentId` (`agentId`),
  ADD KEY `taskId` (`taskId`);

ALTER TABLE `File`
  ADD PRIMARY KEY (`fileId`);

ALTER TABLE `Hash`
  ADD PRIMARY KEY (`hashId`),
  ADD KEY `hashlistId` (`hashlistId`),
  ADD KEY `chunkId` (`chunkId`);

ALTER TABLE `HashBinary`
  ADD PRIMARY KEY (`hashBinaryId`),
  ADD KEY `hashlistId` (`hashlistId`),
  ADD KEY `chunkId` (`chunkId`);

ALTER TABLE `HashcatRelease`
  ADD PRIMARY KEY (`hashcatReleaseId`);

ALTER TABLE `Hashlist`
  ADD PRIMARY KEY (`hashlistId`),
  ADD KEY `hashTypeId` (`hashTypeId`);

ALTER TABLE `HashlistAgent`
  ADD PRIMARY KEY (`hashlistAgentId`),
  ADD KEY `hashlistId` (`hashlistId`),
  ADD KEY `agentId` (`agentId`);

ALTER TABLE `HashType`
  ADD PRIMARY KEY (`hashTypeId`);

ALTER TABLE `RegVoucher`
  ADD PRIMARY KEY (`regVoucherId`);

ALTER TABLE `RightGroup`
  ADD PRIMARY KEY (`rightGroupId`);

ALTER TABLE `Session`
  ADD PRIMARY KEY (`sessionId`),
  ADD KEY `userId` (`userId`);

ALTER TABLE `SuperHashlistHashlist`
  ADD PRIMARY KEY (`superHashlistHashlistId`),
  ADD KEY `superHashlistId` (`superHashlistId`),
  ADD KEY `hashlistId` (`hashlistId`);

ALTER TABLE `Supertask`
  ADD PRIMARY KEY (`supertaskId`);

ALTER TABLE `SupertaskTask`
  ADD PRIMARY KEY (`supertaskTaskId`),
  ADD KEY `supertaskId` (`supertaskId`),
  ADD KEY `taskId` (`taskId`);

ALTER TABLE `Task`
  ADD PRIMARY KEY (`taskId`),
  ADD KEY `hashlistId` (`hashlistId`);

ALTER TABLE `TaskFile`
  ADD PRIMARY KEY (`taskFileId`),
  ADD KEY `taskId` (`taskId`),
  ADD KEY `fileId` (`fileId`);

ALTER TABLE `User`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `rightGroupId` (`rightGroupId`);

ALTER TABLE `Agent`
  MODIFY `agentId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `AgentBinary`
  MODIFY `agentBinaryId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Assignment`
  MODIFY `assignmentId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Chunk`
  MODIFY `chunkId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Config`
  MODIFY `configId` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 13;

ALTER TABLE `AgentError`
  MODIFY `agentErrorId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `File`
  MODIFY `fileId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Hash`
  MODIFY `hashId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `HashBinary`
  MODIFY `hashBinaryId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `HashcatRelease`
  MODIFY `hashcatReleaseId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Hashlist`
  MODIFY `hashlistId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `HashlistAgent`
  MODIFY `hashlistAgentId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `RegVoucher`
  MODIFY `regVoucherId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `RightGroup`
  MODIFY `rightGroupId` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 6;

ALTER TABLE `Session`
  MODIFY `sessionId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `SuperHashlistHashlist`
  MODIFY `superHashlistHashlistId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Supertask`
  MODIFY `supertaskId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `SupertaskTask`
  MODIFY `supertaskTaskId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Task`
  MODIFY `taskId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `TaskFile`
  MODIFY `taskFileId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `User`
  MODIFY `userId` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Agent`
  ADD CONSTRAINT `Agent_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

ALTER TABLE `Assignment`
  ADD CONSTRAINT `Assignment_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`),
  ADD CONSTRAINT `Assignment_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

ALTER TABLE `Chunk`
  ADD CONSTRAINT `Chunk_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`),
  ADD CONSTRAINT `Chunk_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

ALTER TABLE `AgentError`
  ADD CONSTRAINT `Error_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `Error_ibfk_2` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`);

ALTER TABLE `Hash`
  ADD CONSTRAINT `Hash_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `Hash_ibfk_2` FOREIGN KEY (`chunkId`) REFERENCES `Chunk` (`chunkId`);

ALTER TABLE `HashBinary`
  ADD CONSTRAINT `HashBinary_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `HashBinary_ibfk_2` FOREIGN KEY (`chunkId`) REFERENCES `Chunk` (`chunkId`);

ALTER TABLE `Hashlist`
  ADD CONSTRAINT `Hashlist_ibfk_1` FOREIGN KEY (`hashTypeId`) REFERENCES `HashType` (`hashTypeId`);

ALTER TABLE `NotificationSetting`
  ADD CONSTRAINT `NotificationSetting_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

ALTER TABLE `HashlistAgent`
  ADD CONSTRAINT `HashlistAgent_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `HashlistAgent_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `HashlistAgent_ibfk_3` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `HashlistAgent_ibfk_4` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

ALTER TABLE `Session`
  ADD CONSTRAINT `Session_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

ALTER TABLE `SuperHashlistHashlist`
  ADD CONSTRAINT `SuperHashlistHashlist_ibfk_1` FOREIGN KEY (`superHashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `SuperHashlistHashlist_ibfk_2` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`);

ALTER TABLE `SupertaskTask`
  ADD CONSTRAINT `SupertaskTask_ibfk_1` FOREIGN KEY (`supertaskId`) REFERENCES `Supertask` (`supertaskId`),
  ADD CONSTRAINT `SupertaskTask_ibfk_2` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`);

ALTER TABLE `Task`
  ADD CONSTRAINT `Task_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`);

ALTER TABLE `TaskFile`
  ADD CONSTRAINT `TaskFile_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`),
  ADD CONSTRAINT `TaskFile_ibfk_2` FOREIGN KEY (`fileId`) REFERENCES `File` (`fileId`);

ALTER TABLE `User`
  ADD CONSTRAINT `User_ibfk_1` FOREIGN KEY (`rightGroupId`) REFERENCES `RightGroup` (`rightGroupId`);
