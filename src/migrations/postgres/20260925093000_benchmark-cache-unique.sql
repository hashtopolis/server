-- Benchmark caching (issue #879): make the lookup key unique so a store() race
-- cannot leave two rows for one key, and replace the old non-unique index.
-- Collapse any pre-existing duplicates to the newest row before adding the key.
DELETE FROM benchmark a USING benchmark b
  WHERE a.crackerbinaryid=b.crackerbinaryid AND a.hashmode=b.hashmode
  AND a.attackparameters=b.attackparameters AND a.devicesignature=b.devicesignature
  AND a.benchmarktype=b.benchmarktype AND a.benchmarkid<b.benchmarkid;
DROP INDEX benchmark_lookup;
CREATE UNIQUE INDEX benchmark_lookup ON benchmark
  (crackerbinaryid, hashmode, attackparameters, devicesignature, benchmarktype);
