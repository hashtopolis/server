
CREATE TABLE `Agent` (
  `agentId` int(11) NOT NULL,
  `agentName` varchar(200) COLLATE utf8_bin NOT NULL,
  `uid` varchar(200) COLLATE utf8_bin NOT NULL,
  `os` int(11) NOT NULL,
  `gpuDriver` int(11) NOT NULL,
  `gpus` text COLLATE utf8_bin NOT NULL,
  `hcVersion` varchar(20) COLLATE utf8_bin NOT NULL,
  `cmdPars` varchar(200) COLLATE utf8_bin NOT NULL,
  `wait` int(11) NOT NULL COMMENT 'idle wait before cracking',
  `ignoreErrors` int(11) NOT NULL,
  `isActive` int(11) NOT NULL,
  `isTrusted` int(11) NOT NULL,
  `token` varchar(50) COLLATE utf8_bin NOT NULL,
  `lastAct` int(11) NOT NULL,
  `lastTime` int(11) NOT NULL,
  `lastIp` varchar(50) COLLATE utf8_bin NOT NULL,
  `userId` int(11) NOT NULL,
  `cpuOnly` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `Assignment` (
  `assignmentId` int(11) NOT NULL,
  `taskId` int(11) NOT NULL,
  `agentId` int(11) NOT NULL,
  `benchmark` bigint(20) NOT NULL,
  `autoAdjust` int(11) NOT NULL,
  `speed` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `Chunk` (
  `chunkId` int(11) NOT NULL,
  `taskId` int(11) NOT NULL,
  `skip` bigint(20) NOT NULL,
  `length` bigint(20) NOT NULL,
  `agentId` int(11) NOT NULL,
  `dispatchTime` int(11) NOT NULL,
  `progress` bigint(20) NOT NULL,
  `rprogress` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  `cracked` int(11) NOT NULL,
  `solveTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `Config` (
  `configId` int(11) NOT NULL,
  `item` varchar(100) COLLATE utf8_bin NOT NULL,
  `value` varchar(200) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `Config` (`configId`, `item`, `value`) VALUES
(1, 'agenttimeout', '30'),
(2, 'benchtime', '30'),
(3, 'chunktime', '600'),
(4, 'chunktimeout', '30'),
(5, 'emailaddr', 'email@example.org'),
(6, 'emailerror', '0'),
(7, 'emailhldone', '0'),
(8, 'emailtaskdone', '0'),
(9, 'fieldseparator', ':'),
(10, 'hashlistAlias', '#HL#'),
(11, 'statustimer', '5'),
(12, 'timefmt', 'd.m.Y, H:i:s');

CREATE TABLE `AgentError` (
  `agentErrorId` int(11) NOT NULL,
  `agentId` int(11) NOT NULL,
  `taskId` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `error` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `File` (
  `fileId` int(11) NOT NULL,
  `filename` varchar(200) COLLATE utf8_bin NOT NULL,
  `size` bigint(20) NOT NULL,
  `secret` int(11) NOT NULL,
  `fileType` int(11) NOT NULL COMMENT '0 -> dict, 1 -> rule'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `Hash` (
  `hashId` int(11) NOT NULL,
  `hashlistId` int(11) NOT NULL,
  `hash` varchar(512) COLLATE utf8_bin NOT NULL,
  `salt` varchar(200) COLLATE utf8_bin NOT NULL,
  `plaintext` varchar(200) COLLATE utf8_bin NOT NULL,
  `time` int(11) NOT NULL,
  `chunkId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `HashBinary` (
  `hashBinaryId` int(11) NOT NULL,
  `hashlistId` int(11) NOT NULL,
  `essid` varchar(100) COLLATE utf8_bin NOT NULL,
  `hash` longblob NOT NULL,
  `plaintext` varchar(200) COLLATE utf8_bin NOT NULL,
  `time` int(11) NOT NULL,
  `chunkId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `HashcatRelease` (
  `hashcatReleaseId` int(11) NOT NULL,
  `version` varchar(50) COLLATE utf8_bin NOT NULL,
  `time` int(11) NOT NULL,
  `url` varchar(200) COLLATE utf8_bin NOT NULL,
  `commonFiles` varchar(200) COLLATE utf8_bin NOT NULL,
  `binary32` varchar(200) COLLATE utf8_bin NOT NULL,
  `binary64` varchar(200) COLLATE utf8_bin NOT NULL,
  `rootdir` varchar(200) COLLATE utf8_bin NOT NULL,
  `minver` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `Hashlist` (
  `hashlistId` int(11) NOT NULL,
  `hashlistName` varchar(200) COLLATE utf8_bin NOT NULL,
  `format` int(11) NOT NULL COMMENT '0 -> text, 1 -> wpa, 2 -> bin',
  `hashTypeId` int(11) NOT NULL,
  `hashCount` int(11) NOT NULL,
  `cracked` int(11) NOT NULL,
  `secret` int(11) NOT NULL,
  `hexSalt` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `HashlistAgent` (
  `hashlistAgentId` int(11) NOT NULL,
  `hashlistId` int(11) NOT NULL,
  `agentId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `HashType` (
  `hashTypeId` int(11) NOT NULL,
  `description` varchar(200) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `HashType` (`hashTypeId`, `description`) VALUES
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
(3800, 'md5($salt.$pass.$salt)'),
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

CREATE TABLE `RegVoucher` (
  `regVoucherId` int(11) NOT NULL,
  `voucher` varchar(50) COLLATE utf8_bin NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `RightGroup` (
  `rightGroupId` int(11) NOT NULL,
  `groupName` varchar(30) COLLATE utf8_bin NOT NULL,
  `level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `RightGroup` (`rightGroupId`, `groupName`, `level`) VALUES
(1, 'View User', 1),
(2, 'Read Only User', 5),
(3, 'Normal User', 20),
(4, 'Superuser', 30),
(5, 'Administrator', 50);

CREATE TABLE `Session` (
  `sessionId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `sessionStartDate` int(11) NOT NULL,
  `lastActionDate` int(11) NOT NULL,
  `isOpen` tinyint(4) NOT NULL,
  `sessionLifetime` int(11) NOT NULL,
  `sessionKey` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `SuperHashlist` (
  `superHashlistId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `SuperHashlistHashlist` (
  `superHashlistHashlistId` int(11) NOT NULL,
  `superHashlistId` int(11) NOT NULL,
  `hashlistId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `Supertask` (
  `supertaskId` int(11) NOT NULL,
  `supertaskName` varchar(100) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `SupertaskTask` (
  `supertaskTaskId` int(11) NOT NULL,
  `supertaskId` int(11) NOT NULL,
  `taskId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `Task` (
  `taskId` int(11) NOT NULL,
  `taskName` varchar(200) COLLATE utf8_bin NOT NULL,
  `attackCmd` varchar(512) COLLATE utf8_bin NOT NULL,
  `hashlistId` int(11) NOT NULL,
  `chunkTime` int(11) NOT NULL,
  `statusTimer` int(11) NOT NULL,
  `autoAdjust` int(11) NOT NULL,
  `keyspace` bigint(20) NOT NULL,
  `progress` bigint(20) NOT NULL,
  `priority` int(11) NOT NULL,
  `color` varchar(10) COLLATE utf8_bin NOT NULL,
  `isSmall` int(11) NOT NULL,
  `isCpuTask` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `TaskFile` (
  `taskFileId` int(11) NOT NULL,
  `taskId` int(11) NOT NULL,
  `fileId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `User` (
  `userId` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_bin NOT NULL,
  `passwordHash` varchar(512) COLLATE utf8_bin NOT NULL,
  `email` varchar(512) COLLATE utf8_bin NOT NULL,
  `passwordSalt` varchar(512) COLLATE utf8_bin NOT NULL,
  `isValid` tinyint(11) NOT NULL,
  `isComputedPassword` tinyint(11) NOT NULL,
  `lastLoginDate` int(11) NOT NULL,
  `registeredSince` int(11) NOT NULL,
  `sessionLifetime` int(11) NOT NULL DEFAULT '600',
  `rightGroupId` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `Agent`
  ADD PRIMARY KEY (`agentId`),
  ADD KEY `userId` (`userId`);

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

ALTER TABLE `SuperHashlist`
  ADD PRIMARY KEY (`superHashlistId`);

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
  MODIFY `agentId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Assignment`
  MODIFY `assignmentId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Chunk`
  MODIFY `chunkId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Config`
  MODIFY `configId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

ALTER TABLE `AgentError`
  MODIFY `agentErrorId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `File`
  MODIFY `fileId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Hash`
  MODIFY `hashId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `HashBinary`
  MODIFY `hashBinaryId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `HashcatRelease`
  MODIFY `hashcatReleaseId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Hashlist`
  MODIFY `hashlistId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `HashlistAgent`
  MODIFY `hashlistAgentId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `HashType`
  MODIFY `hashTypeId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11601;

ALTER TABLE `RegVoucher`
  MODIFY `regVoucherId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `RightGroup`
  MODIFY `rightGroupId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `Session`
  MODIFY `sessionId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=685;

ALTER TABLE `SuperHashlist`
  MODIFY `superHashlistId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `SuperHashlistHashlist`
  MODIFY `superHashlistHashlistId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Supertask`
  MODIFY `supertaskId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `SupertaskTask`
  MODIFY `supertaskTaskId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Task`
  MODIFY `taskId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `TaskFile`
  MODIFY `taskFileId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `User`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

ALTER TABLE `Agent`
  ADD CONSTRAINT `Agent_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

ALTER TABLE `Assignment`
  ADD CONSTRAINT `Assignment_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`),
  ADD CONSTRAINT `Assignment_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `Assignment_ibfk_3` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

ALTER TABLE `Chunk`
  ADD CONSTRAINT `Chunk_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`),
  ADD CONSTRAINT `Chunk_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `Chunk_ibfk_3` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`),
  ADD CONSTRAINT `Chunk_ibfk_4` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

ALTER TABLE `AgentError`
  ADD CONSTRAINT `Error_ibfk_1` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `Error_ibfk_2` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`),
  ADD CONSTRAINT `Error_ibfk_3` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `Error_ibfk_4` FOREIGN KEY (`taskId`) REFERENCES `Task` (`taskId`);

ALTER TABLE `Hash`
  ADD CONSTRAINT `Hash_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `Hash_ibfk_2` FOREIGN KEY (`chunkId`) REFERENCES `Chunk` (`chunkId`),
  ADD CONSTRAINT `Hash_ibfk_3` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `Hash_ibfk_4` FOREIGN KEY (`chunkId`) REFERENCES `Chunk` (`chunkId`);

ALTER TABLE `HashBinary`
  ADD CONSTRAINT `HashBinary_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `HashBinary_ibfk_2` FOREIGN KEY (`chunkId`) REFERENCES `Chunk` (`chunkId`),
  ADD CONSTRAINT `HashBinary_ibfk_3` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `HashBinary_ibfk_4` FOREIGN KEY (`chunkId`) REFERENCES `Chunk` (`chunkId`);

ALTER TABLE `Hashlist`
  ADD CONSTRAINT `Hashlist_ibfk_1` FOREIGN KEY (`hashTypeId`) REFERENCES `HashType` (`hashTypeId`),
  ADD CONSTRAINT `Hashlist_ibfk_2` FOREIGN KEY (`hashTypeId`) REFERENCES `HashType` (`hashTypeId`);

ALTER TABLE `HashlistAgent`
  ADD CONSTRAINT `HashlistAgent_ibfk_1` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `HashlistAgent_ibfk_2` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`),
  ADD CONSTRAINT `HashlistAgent_ibfk_3` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `HashlistAgent_ibfk_4` FOREIGN KEY (`agentId`) REFERENCES `Agent` (`agentId`);

ALTER TABLE `Session`
  ADD CONSTRAINT `Session_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`),
  ADD CONSTRAINT `Session_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

ALTER TABLE `SuperHashlistHashlist`
  ADD CONSTRAINT `SuperHashlistHashlist_ibfk_1` FOREIGN KEY (`superHashlistId`) REFERENCES `SuperHashlist` (`superHashlistId`),
  ADD CONSTRAINT `SuperHashlistHashlist_ibfk_2` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`),
  ADD CONSTRAINT `SuperHashlistHashlist_ibfk_3` FOREIGN KEY (`superHashlistId`) REFERENCES `SuperHashlist` (`superHashlistId`),
  ADD CONSTRAINT `SuperHashlistHashlist_ibfk_4` FOREIGN KEY (`hashlistId`) REFERENCES `Hashlist` (`hashlistId`);

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
