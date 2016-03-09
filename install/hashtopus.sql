
CREATE TABLE IF NOT EXISTS `agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE latin1_bin NOT NULL COMMENT 'Friendly machine name',
  `uid` varchar(36) COLLATE latin1_bin NOT NULL COMMENT 'HDD serial number',
  `os` tinyint(4) NOT NULL COMMENT '0=Win, 1=Unix',
  `cputype` tinyint(4) NOT NULL COMMENT '32/64',
  `gpubrand` tinyint(4) NOT NULL COMMENT '1=NVidia, 2=AMD',
  `gpudriver` int(11) NOT NULL DEFAULT '0' COMMENT 'GPU driver version',
  `gpus` text COLLATE latin1_bin NOT NULL COMMENT 'List of GPUs',
  `hcversion` varchar(10) COLLATE latin1_bin DEFAULT '' COMMENT 'Version of oclHashcat delivered to agent',
  `cmdpars` varchar(128) COLLATE latin1_bin DEFAULT NULL COMMENT 'Agent specific command line',
  `wait` int(11) NOT NULL DEFAULT '0' COMMENT 'Idle wait before cracking',
  `ignoreerrors` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Don''t pause agent on errors',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Flag if agent is active',
  `trusted` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Is agent trusted for secret data?',
  `token` varchar(10) COLLATE latin1_bin NOT NULL COMMENT 'Generated access token',
  `lastact` varchar(10) COLLATE latin1_bin NOT NULL DEFAULT '' COMMENT 'Last action',
  `lasttime` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Last action time',
  `lastip` varchar(15) COLLATE latin1_bin NOT NULL DEFAULT '' COMMENT 'Last action IP',
  `userId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `assignment_verify` (`token`,`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='List of Hashtopus agents' AUTO_INCREMENT=10 ;

CREATE TABLE IF NOT EXISTS `assignments` (
  `task` int(11) NOT NULL COMMENT 'Task ID',
  `agent` int(11) NOT NULL COMMENT 'Agent ID',
  `benchmark` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Agent''s benchmark for this task',
  `autoadjust` tinyint(4) NOT NULL COMMENT 'Autoadjust override',
  `speed` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Current cracking speed',
  UNIQUE KEY `assigned_all` (`agent`),
  KEY `assigned_active` (`task`,`agent`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='Information about agents assignments';

CREATE TABLE IF NOT EXISTS `chunks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task` int(11) DEFAULT NULL COMMENT 'Task ID',
  `skip` bigint(20) NOT NULL COMMENT 'Keyspace skip',
  `length` bigint(20) NOT NULL COMMENT 'Keyspace length',
  `agent` int(11) DEFAULT NULL COMMENT 'Agent ID',
  `dispatchtime` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Time of dispatching',
  `progress` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Confirmed progress in chunk (0 to length)',
  `rprogress` smallint(20) NOT NULL DEFAULT '0' COMMENT 'Real progress within chunk',
  `state` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Actual state of the chunk',
  `cracked` int(11) NOT NULL DEFAULT '0' COMMENT 'Number of cracked hashes',
  `solvetime` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Time of last activity',
  PRIMARY KEY (`id`),
  KEY `solve_verify` (`id`,`task`,`agent`),
  KEY `chunk_redispatch` (`task`,`agent`,`progress`,`length`,`dispatchtime`,`solvetime`,`skip`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='Dispatched chunks of work' AUTO_INCREMENT=42 ;

CREATE TABLE IF NOT EXISTS `config` (
  `item` varchar(16) COLLATE latin1_bin NOT NULL,
  `value` varchar(64) COLLATE latin1_bin NOT NULL,
  PRIMARY KEY (`item`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='Global configuration values';

INSERT INTO `config` (`item`, `value`) VALUES
('agenttimeout', '30'),
('benchtime', '10'),
('chunktime', '600'),
('chunktimeout', '30'),
('emailaddr', 'email@example.org'),
('emailerror', '0'),
('emailhldone', '0'),
('emailtaskdone', '0'),
('fieldseparator', ':'),
('hashlistAlias', '#HL#'),
('statustimer', '5'),
('timefmt', 'd.m.Y, H:i:s');

CREATE TABLE IF NOT EXISTS `errors` (
  `agent` int(11) NOT NULL COMMENT 'Agent ID',
  `task` int(11) DEFAULT NULL COMMENT 'Task ID',
  `time` bigint(20) NOT NULL COMMENT 'Error time',
  `error` text COLLATE latin1_bin NOT NULL COMMENT 'Error message'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='Error output received from agents';

CREATE TABLE IF NOT EXISTS `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'File id',
  `filename` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'Filename',
  `size` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Size of the file',
  `secret` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is file secret?',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='Files that can be added to tasks' AUTO_INCREMENT=34 ;

CREATE TABLE IF NOT EXISTS `hashcatreleases` (
  `version` varchar(10) COLLATE latin1_bin NOT NULL,
  `time` bigint(20) NOT NULL,
  `url_nvidia` varchar(128) COLLATE latin1_bin NOT NULL,
  `url_amd` varchar(128) COLLATE latin1_bin NOT NULL,
  `common_files` varchar(128) COLLATE latin1_bin NOT NULL,
  `32_nvidia` varchar(128) COLLATE latin1_bin NOT NULL,
  `64_nvidia` varchar(128) COLLATE latin1_bin NOT NULL,
  `32_amd` varchar(128) COLLATE latin1_bin NOT NULL,
  `64_amd` varchar(128) COLLATE latin1_bin NOT NULL,
  `rootdir_nvidia` varchar(32) COLLATE latin1_bin NOT NULL,
  `rootdir_amd` varchar(32) COLLATE latin1_bin NOT NULL,
  `minver_nvidia` int(11) NOT NULL,
  `minver_amd` int(11) NOT NULL,
  PRIMARY KEY (`version`),
  KEY `newest_search` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='oclHashcat releases';

INSERT INTO `hashcatreleases` (`version`, `time`, `url_nvidia`, `url_amd`, `common_files`, `32_nvidia`, `64_nvidia`, `32_amd`, `64_amd`, `rootdir_nvidia`, `rootdir_amd`, `minver_nvidia`, `minver_amd`) VALUES
('2.01', 1457330572, 'http://hashcat.net/files/cudaHashcat-2.01.7z', 'http://hashcat.net/files/oclHashcat-2.01.7z', 'hashcat.hcstat hashcat.keyfile', 'kernels/4318/*32.cubin', 'kernels/4318/*64.cubin', 'kernels/4098/*.llvmir', 'kernels/4098/*.llvmir', 'cudaHashcat-2.01', 'oclHashcat-2.01', 34659, 1409);

CREATE TABLE IF NOT EXISTS `hashes` (
  `hashlist` int(11) NOT NULL COMMENT 'Hashlist ID',
  `hash` varchar(512) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'Hash',
  `salt` varchar(64) COLLATE latin1_bin NOT NULL DEFAULT '' COMMENT 'Optional salt',
  `plaintext` varchar(128) COLLATE latin1_bin DEFAULT NULL COMMENT 'Cracked plaintext',
  `time` bigint(20) DEFAULT NULL COMMENT 'Time of crack',
  `chunk` int(11) DEFAULT NULL COMMENT 'Chunk in which the hash was cracked',
  PRIMARY KEY (`hashlist`,`hash`,`salt`),
  KEY `download` (`hashlist`,`plaintext`),
  KEY `adm_chunk` (`chunk`),
  KEY `download_zaps` (`hashlist`,`time`,`chunk`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='Hashes for specific hashlists';

CREATE TABLE IF NOT EXISTS `hashes_binary` (
  `hashlist` int(11) NOT NULL COMMENT 'hashlist ID',
  `essid` varchar(36) COLLATE latin1_bin NOT NULL DEFAULT '' COMMENT 'AP name',
  `hash` longblob NOT NULL COMMENT 'Raw binary hash',
  `plaintext` varchar(128) COLLATE latin1_bin DEFAULT NULL COMMENT 'Cracked plaintext',
  `time` bigint(20) DEFAULT NULL COMMENT 'Time of crack',
  `chunk` int(11) DEFAULT NULL COMMENT 'Chunk in which the hash was cracked',
  PRIMARY KEY (`hashlist`,`essid`),
  UNIQUE KEY `download` (`hashlist`,`plaintext`),
  KEY `adm_chunk` (`chunk`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='Hashes for specific WPA hashlist';

CREATE TABLE IF NOT EXISTS `hashlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE latin1_bin NOT NULL COMMENT 'Name of the hashlist',
  `format` int(11) NOT NULL DEFAULT '0' COMMENT '0 = text, 1 = wpa, 2 = bin',
  `hashtype` int(11) NOT NULL COMMENT 'Hashtype',
  `hashcount` int(11) NOT NULL DEFAULT '0' COMMENT 'Total count of hashes',
  `cracked` int(11) NOT NULL DEFAULT '0' COMMENT 'Total count of cracked hashes',
  `secret` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is hashlist secret?',
  PRIMARY KEY (`id`,`format`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='List of hashlists' AUTO_INCREMENT=28 ;

CREATE TABLE IF NOT EXISTS `hashlistusers` (
  `hashlist` int(11) NOT NULL COMMENT 'Used hashlist',
  `agent` int(11) NOT NULL COMMENT 'Using agent',
  PRIMARY KEY (`hashlist`,`agent`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='Marks if an agent is using a hashlist';

CREATE TABLE IF NOT EXISTS `hashtypes` (
  `id` int(11) NOT NULL COMMENT 'Hashtype',
  `description` varchar(64) COLLATE latin1_bin NOT NULL COMMENT 'Hash description',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;

INSERT INTO `hashtypes` (`id`, `description`) VALUES
(0, 'MD5'),
(10, 'md5($pass.$salt)'),
(11, 'Joomla < 2.5.18'),
(12, 'PostgreSQL'),
(20, 'md5($salt.$pass)'),
(21, 'osCommerce, xt:Commerce'),
(22, 'Juniper Netscreen/SSG (ScreenOS)'),
(23, 'Skype'),
(30, 'md5(unicode($pass).$salt)'),
(40, 'md5($salt.unicode($pass))'),
(50, 'HMAC-MD5 (key = $pass)'),
(60, 'HMAC-MD5 (key = $salt)'),
(100, 'SHA1'),
(101, 'nsldap, SHA-1(Base64), Netscape LDAP SHA'),
(110, 'sha1($pass.$salt)'),
(111, 'nsldaps, SSHA-1(Base64), Netscape LDAP SSHA'),
(112, 'Oracle 11g/12c'),
(120, 'sha1($salt.$pass)'),
(121, 'SMF > v1.1'),
(122, 'OSX v10.4, v10.5, v10.6'),
(124, 'Django (SHA-1)'),
(130, 'sha1(unicode($pass).$salt)'),
(131, 'MSSQL(2000)'),
(132, 'MSSQL(2005)'),
(133, 'PeopleSoft'),
(140, 'sha1($salt.unicode($pass))'),
(141, 'EPiServer 6.x < v4'),
(150, 'HMAC-SHA1 (key = $pass)'),
(160, 'HMAC-SHA1 (key = $salt)'),
(190, 'sha1(LinkedIn)'),
(200, 'MySQL323'),
(300, 'MySQL4.1/MySQL5'),
(400, 'phpass, MD5(Wordpress), MD5(phpBB3), MD5(Joomla)'),
(500, 'md5crypt, MD5(Unix), FreeBSD MD5, Cisco-IOS MD5'),
(501, 'Juniper IVE'),
(900, 'MD4'),
(1000, 'NTLM'),
(1100, 'Domain Cached Credentials, mscash'),
(1400, 'SHA256'),
(1410, 'sha256($pass.$salt)'),
(1420, 'sha256($salt.$pass)'),
(1421, 'hMailServer'),
(1430, 'sha256(unicode($pass).$salt)'),
(1440, 'sha256($salt.unicode($pass))'),
(1441, 'EPiServer 6.x > v4'),
(1450, 'HMAC-SHA256 (key = $pass)'),
(1460, 'HMAC-SHA256 (key = $salt)'),
(1500, 'descrypt, DES(Unix), Traditional DES'),
(1600, 'md5apr1, MD5(APR), Apache MD5'),
(1700, 'SHA512'),
(1710, 'sha512($pass.$salt)'),
(1711, 'SSHA-512(Base64), LDAP {SSHA512}'),
(1720, 'sha512($salt.$pass)'),
(1722, 'OSX v10.7'),
(1730, 'sha512(unicode($pass).$salt)'),
(1731, 'MSSQL(2012), MSSQL(2014)'),
(1740, 'sha512($salt.unicode($pass))'),
(1750, 'HMAC-SHA512 (key = $pass)'),
(1760, 'HMAC-SHA512 (key = $salt)'),
(1800, 'sha512crypt, SHA512(Unix)'),
(2100, 'Domain Cached Credentials2, mscash2'),
(2400, 'Cisco-PIX MD5'),
(2410, 'Cisco-ASA MD5'),
(2500, 'WPA/WPA2'),
(2600, 'Double MD5'),
(2611, 'vBulletin < v3.8.5'),
(2612, 'PHPS'),
(2711, 'vBulletin > v3.8.5'),
(2811, 'IPB2+, MyBB1.2+'),
(3000, 'LM'),
(3100, 'Oracle 7-10g, DES(Oracle)'),
(3200, 'bcrypt, Blowfish(OpenBSD)'),
(3710, 'md5($salt.md5($pass))'),
(3711, 'Mediawiki B type'),
(3800, 'md5($pass.$salt.$pass)'),
(4300, 'md5(strtoupper(md5($pass)))'),
(4400, 'md5(sha1($pass))'),
(4500, 'Double SHA1'),
(4700, 'sha1(md5($pass))'),
(4800, 'MD5(Chap), iSCSI CHAP authentication'),
(4900, 'sha1($salt.$pass.$salt)'),
(5000, 'SHA-3(Keccak)'),
(5100, 'Half MD5'),
(5200, 'Password Safe v3'),
(5300, 'IKE-PSK MD5'),
(5400, 'IKE-PSK SHA1'),
(5500, 'NetNTLMv1-VANILLA / NetNTLMv1+ESS'),
(5600, 'NetNTLMv2'),
(5700, 'Cisco-IOS SHA256'),
(5800, 'Android PIN'),
(6000, 'RipeMD160'),
(6100, 'Whirlpool'),
(6300, 'AIX {smd5}'),
(6400, 'AIX {ssha256}'),
(6500, 'AIX {ssha512}'),
(6600, '1Password, agilekeychain'),
(6700, 'AIX {ssha1}'),
(6800, 'Lastpass'),
(6900, 'GOST R 34.11-94'),
(7100, 'OSX v10.8+'),
(7200, 'GRUB 2'),
(7300, 'IPMI2 RAKP HMAC-SHA1'),
(7400, 'sha256crypt, SHA256(Unix)'),
(7500, 'Kerberos 5 AS-REQ Pre-Auth etype 23'),
(7600, 'Redmine Project Management Web App'),
(7700, 'SAP CODVN B (BCODE)'),
(7800, 'SAP CODVN F/G (PASSCODE)'),
(7900, 'Drupal7'),
(8000, 'Sybase ASE'),
(8100, 'Citrix Netscaler'),
(8200, '1Password, cloudkeychain'),
(8300, 'DNSSEC (NSEC3)'),
(8400, 'WBB3, Woltlab Burning Board 3'),
(8500, 'RACF'),
(8600, 'Lotus Notes/Domino 5'),
(8700, 'Lotus Notes/Domino 6'),
(8800, 'Android FDE <= 4.3'),
(8900, 'scrypt'),
(9000, 'Password Safe v2'),
(9100, 'Lotus Notes/Domino 8'),
(9200, 'Cisco $8$'),
(9300, 'Cisco $9$'),
(9400, 'Office 2007'),
(9500, 'Office 2010'),
(9600, 'Office 2013'),
(9700, 'MS Office <= 2003 MD5 + RC4, oldoffice$0, oldoffice$1'),
(9710, 'MS Office <= 2003 MD5 + RC4, collider-mode #1'),
(9720, 'MS Office <= 2003 MD5 + RC4, collider-mode #2'),
(9800, 'MS Office <= 2003 SHA1 + RC4, oldoffice$3, oldoffice$4'),
(9810, 'MS Office <= 2003 SHA1 + RC4, collider-mode #1'),
(9820, 'MS Office <= 2003 SHA1 + RC4, collider-mode #2'),
(9900, 'Radmin2'),
(10000, 'Django (PBKDF2-SHA256)'),
(10100, 'SipHash'),
(10200, 'Cram MD5'),
(10300, 'SAP CODVN H (PWDSALTEDHASH) iSSHA-1'),
(10400, 'PDF 1.1 - 1.3 (Acrobat 2 - 4)'),
(10410, 'PDF 1.1 - 1.3 (Acrobat 2 - 4) + collider-mode #1'),
(10420, 'PDF 1.1 - 1.3 (Acrobat 2 - 4) + collider-mode #2'),
(10500, 'PDF 1.4 - 1.6 (Acrobat 5 - 8)'),
(10600, 'PDF 1.7 Level 3 (Acrobat 9)'),
(10700, 'PDF 1.7 Level 8 (Acrobat 10 - 11)'),
(10800, 'SHA384'),
(10900, 'PBKDF2-HMAC-SHA256'),
(11000, 'PrestaShop'),
(11100, 'PostgreSQL Challenge-Response Authentication (MD5)'),
(11200, 'MySQL Challenge-Response Authentication (SHA1)'),
(11300, 'Bitcoin/Litecoin wallet.dat'),
(11400, 'SIP digest authentication (MD5)'),
(11500, 'CRC32'),
(11600, '7-Zip');

CREATE TABLE IF NOT EXISTS `regvouchers` (
  `voucher` varchar(10) COLLATE latin1_bin NOT NULL COMMENT 'Registration vouchers',
  `time` bigint(20) NOT NULL COMMENT 'Timestamp of creation',
  PRIMARY KEY (`voucher`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='Tokens allowing agent registration';

CREATE TABLE IF NOT EXISTS `RightGroup` (
  `rightGroupId` int(11) NOT NULL AUTO_INCREMENT,
  `groupName` varchar(30) COLLATE utf8_bin NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`rightGroupId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=6 ;

INSERT INTO `RightGroup` (`rightGroupId`, `groupName`, `level`) VALUES
(1, 'View User', 1),
(2, 'Read Only User', 5),
(3, 'Normal User', 20),
(4, 'Superuser', 30),
(5, 'Administrator', 50);

CREATE TABLE IF NOT EXISTS `Session` (
  `sessionId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `sessionStartDate` int(11) NOT NULL,
  `lastActionDate` int(11) NOT NULL,
  `isOpen` tinyint(4) NOT NULL,
  `sessionLifetime` int(11) NOT NULL,
  `sessionKey` varchar(500) NOT NULL,
  PRIMARY KEY (`sessionId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `superhashlists` (
  `id` int(11) NOT NULL,
  `hashlist` int(11) NOT NULL COMMENT 'Included hashlist',
  KEY `hashlists` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;

CREATE TABLE IF NOT EXISTS `taskfiles` (
  `task` int(11) NOT NULL COMMENT 'Task ID',
  `file` int(11) NOT NULL COMMENT 'Attached file ID',
  KEY `task` (`task`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='Files associated to tasks (wordlist, rulesets, etc)';

CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE latin1_bin NOT NULL COMMENT 'Task name',
  `attackcmd` varchar(256) COLLATE latin1_bin NOT NULL COMMENT 'Hashcat command line',
  `hashlist` int(11) DEFAULT NULL COMMENT 'Hashlist ID',
  `chunktime` int(11) NOT NULL COMMENT 'Chunk size in seconds',
  `statustimer` int(11) NOT NULL COMMENT 'Interval for sending status',
  `autoadjust` tinyint(4) NOT NULL COMMENT 'Indicator if agents benchmarks are automaticaly adjusted',
  `keyspace` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Keyspace size (calculated by Hashcat)',
  `progress` bigint(20) NOT NULL DEFAULT '0' COMMENT 'How far have chunks been dispatched',
  `priority` int(11) NOT NULL DEFAULT '0' COMMENT 'Assignment priority',
  `color` varchar(6) COLLATE latin1_bin DEFAULT NULL COMMENT 'Color of task shown in admin',
  PRIMARY KEY (`id`),
  KEY `adm_usage` (`hashlist`),
  KEY `autoassign` (`progress`,`keyspace`,`priority`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='List of tasks' AUTO_INCREMENT=48 ;

CREATE TABLE IF NOT EXISTS `User` (
  `userId` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_bin NOT NULL,
  `passwordHash` varchar(512) COLLATE utf8_bin NOT NULL,
  `email` varchar(512) COLLATE utf8_bin NOT NULL,
  `passwordSalt` varchar(512) COLLATE utf8_bin NOT NULL,
  `isValid` tinyint(11) NOT NULL,
  `isComputedPassword` tinyint(11) NOT NULL,
  `lastLoginDate` int(11) NOT NULL,
  `registeredSince` int(11) NOT NULL,
  `sessionLifetime` int(11) NOT NULL DEFAULT '600',
  `rightGroupId` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`userId`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `zapqueue` (
  `hashlist` int(11) NOT NULL COMMENT 'Hashlist to zap',
  `agent` int(11) NOT NULL COMMENT 'For which agent',
  `time` bigint(20) NOT NULL COMMENT 'When were the hashes cracked',
  `chunk` int(11) NOT NULL COMMENT 'Chunk where the hashes were cracked',
  PRIMARY KEY (`hashlist`,`agent`,`time`,`chunk`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='Contains zapping instruction for all involved agents';
