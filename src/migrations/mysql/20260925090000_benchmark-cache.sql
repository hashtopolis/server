-- Benchmark caching (issue #879): cache an agent's benchmark result keyed by the
-- factors that determine cracking speed, so agents with identical hardware reuse
-- a benchmark instead of re-running it on every task pickup.
CREATE TABLE `Benchmark` (
  `benchmarkId` int NOT NULL AUTO_INCREMENT,
  `crackerBinaryId` int NOT NULL,
  `hashMode` int NOT NULL,
  `attackParameters` varchar(64) NOT NULL,
  `deviceSignature` varchar(64) NOT NULL,
  `benchmarkType` varchar(10) NOT NULL,
  `benchmarkValue` varchar(50) NOT NULL,
  `createTime` bigint NOT NULL,
  `expireTime` bigint NOT NULL,
  PRIMARY KEY (`benchmarkId`),
  KEY `crackerBinaryId` (`crackerBinaryId`),
  KEY `Benchmark_lookup` (`crackerBinaryId`,`hashMode`,`attackParameters`,`deviceSignature`,`benchmarkType`),
  KEY `Benchmark_expireTime` (`expireTime`),
  CONSTRAINT `Benchmark_ibfk_1` FOREIGN KEY (`crackerBinaryId`) REFERENCES `CrackerBinary` (`crackerBinaryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Seed the benchmark cache TTL (in seconds, 0 disables caching) for existing installs.
INSERT INTO `Config` (`configSectionId`, `item`, `value`)
SELECT 1, 'benchmarkCacheTtl', '0'
WHERE NOT EXISTS (SELECT 1 FROM `Config` WHERE `item` = 'benchmarkCacheTtl');
