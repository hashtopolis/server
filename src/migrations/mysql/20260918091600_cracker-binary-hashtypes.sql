-- Association table between cracker binaries and hashtypes: a binary only
-- supports the hashtypes it is linked to.
CREATE TABLE `CrackerBinaryHashtype` (
  `crackerBinaryHashtypeId` int NOT NULL AUTO_INCREMENT,
  `crackerBinaryId` int NOT NULL,
  `hashTypeId` int NOT NULL,
  PRIMARY KEY (`crackerBinaryHashtypeId`),
  KEY `crackerBinaryId` (`crackerBinaryId`),
  KEY `hashTypeId` (`hashTypeId`),
  CONSTRAINT `CrackerBinaryHashtype_ibfk_1` FOREIGN KEY (`crackerBinaryId`) REFERENCES `CrackerBinary` (`crackerBinaryId`),
  CONSTRAINT `CrackerBinaryHashtype_ibfk_2` FOREIGN KEY (`hashTypeId`) REFERENCES `HashType` (`hashTypeId`)
) ENGINE=InnoDB;

-- Transition for existing binaries: hashcat binaries support every hashtype
-- (they follow the hashcat mode numbering), so they are linked to all existing
-- hashtypes. Binaries of other types start without any association, their
-- supported hashtypes have to be associated manually.
INSERT INTO `CrackerBinaryHashtype` (`crackerBinaryId`, `hashTypeId`)
  SELECT b.`crackerBinaryId`, h.`hashTypeId`
  FROM `CrackerBinary` b
  JOIN `CrackerBinaryType` t ON b.`crackerBinaryTypeId` = t.`crackerBinaryTypeId` AND t.`typeName` = 'hashcat'
  CROSS JOIN `HashType` h;
