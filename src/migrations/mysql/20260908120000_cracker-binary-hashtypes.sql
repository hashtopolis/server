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

-- Transition for existing binaries: it is not known which hashtypes are
-- supported exactly, so every binary is linked to all existing hashtypes.
-- The user can correct the associations later.
INSERT INTO `CrackerBinaryHashtype` (`crackerBinaryId`, `hashTypeId`)
  SELECT b.`crackerBinaryId`, h.`hashTypeId` FROM `CrackerBinary` b CROSS JOIN `HashType` h;
