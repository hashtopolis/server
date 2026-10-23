-- Association table between cracker binaries and hashtypes: a binary only
-- supports the hashtypes it is linked to. Each pair is only linked once,
-- the unique key also protects against concurrent requests creating the
-- same association twice.
CREATE TABLE `CrackerBinaryHashtype` (
  `crackerBinaryHashtypeId` int NOT NULL AUTO_INCREMENT,
  `crackerBinaryId` int NOT NULL,
  `hashTypeId` int NOT NULL,
  PRIMARY KEY (`crackerBinaryHashtypeId`),
  UNIQUE KEY `crackerBinaryId_hashTypeId` (`crackerBinaryId`, `hashTypeId`),
  KEY `hashTypeId` (`hashTypeId`),
  CONSTRAINT `CrackerBinaryHashtype_ibfk_1` FOREIGN KEY (`crackerBinaryId`) REFERENCES `CrackerBinary` (`crackerBinaryId`),
  CONSTRAINT `CrackerBinaryHashtype_ibfk_2` FOREIGN KEY (`hashTypeId`) REFERENCES `HashType` (`hashTypeId`)
) ENGINE=InnoDB;

-- Transition for existing binaries: hashcat binaries get their hashtype
-- associations populated by a scan of the binary, which reads the supported
-- hash-modes from the unpacked archive. A pending scan job is enqueued for
-- each of them here, the next run of the background job runner executes the
-- scans. Binaries of other types start without any association, their
-- supported hashtypes have to be associated manually.
INSERT INTO `BackgroundJob` (`jobType`, `payload`, `status`, `userId`, `createdAt`)
  SELECT 'scan_cracker',
         JSON_OBJECT('crackerBinaryId', b.`crackerBinaryId`),
         0, NULL, UNIX_TIMESTAMP()
  FROM `CrackerBinary` b
  JOIN `CrackerBinaryType` t ON b.`crackerBinaryTypeId` = t.`crackerBinaryTypeId` AND t.`typeName` = 'hashcat';
