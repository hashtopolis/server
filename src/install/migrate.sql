/* Add new column to existing agents table */
ALTER TABLE agents ADD `userId` int(11) NOT NULL DEFAULT '0';
ALTER TABLE hashlists ADD `hexsalt` int(11) NOT NULL DEFAULT '0';

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

INSERT INTO `hashcatreleases` (`version`, `time`, `url_nvidia`, `url_amd`, `common_files`, `32_nvidia`, `64_nvidia`, `32_amd`, `64_amd`, `rootdir_nvidia`, `rootdir_amd`, `minver_nvidia`, `minver_amd`) VALUES
('3.10', 1457330572, 'https://hashcat.net/files/hashcat-3.10.7z', 'https://hashcat.net/files/hashcat-3.10.7z', 'hashcat.hcstat hashcat.hctune', 'OpenCL/*', 'OpenCL/*', 'OpenCL/*', 'OpenCL/*', 'hashcat-3.10', 'hashcat-3.10', 34659, 1409);

CREATE TABLE `Supertask` (
  `supertaskId` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `Supertask`
  ADD PRIMARY KEY (`supertaskId`);

CREATE TABLE `SupertaskTask` (
  `supertaskTaskId` int(11) NOT NULL,
  `supertaskId` int(11) NOT NULL,
  `taskId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `SupertaskTask`
  ADD PRIMARY KEY (`supertaskTaskId`);