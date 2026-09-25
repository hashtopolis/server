-- Benchmark caching (issue #879): make the lookup key unique so a store() race
-- cannot leave two rows for one key, and replace the old non-unique index.
-- Collapse any pre-existing duplicates to the newest row before adding the key.
DELETE b1 FROM `Benchmark` b1 JOIN `Benchmark` b2
  ON b1.crackerBinaryId=b2.crackerBinaryId AND b1.hashMode=b2.hashMode
  AND b1.attackParameters=b2.attackParameters
  AND b1.deviceSignature=b2.deviceSignature
  AND b1.benchmarkType=b2.benchmarkType AND b1.benchmarkId<b2.benchmarkId;
ALTER TABLE `Benchmark` DROP INDEX `Benchmark_lookup`;
ALTER TABLE `Benchmark` ADD UNIQUE KEY `Benchmark_lookup`
  (`crackerBinaryId`,`hashMode`,`attackParameters`,`deviceSignature`,`benchmarkType`);
