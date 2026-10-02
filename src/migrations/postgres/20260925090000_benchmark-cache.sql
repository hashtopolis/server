-- Benchmark caching (issue #879): cache an agent's benchmark result keyed by the
-- factors that determine cracking speed, so agents with identical hardware reuse
-- a benchmark instead of re-running it on every task pickup. The lookup key is
-- UNIQUE so a concurrent store cannot leave two rows for one key.
CREATE TABLE benchmark (
    benchmarkid SERIAL PRIMARY KEY,
    crackerbinaryid integer NOT NULL REFERENCES crackerbinary (crackerbinaryid),
    hashtypeid integer NOT NULL REFERENCES hashtype (hashtypeid),
    attackparameters varchar(64) NOT NULL,
    devicesignature varchar(64) NOT NULL,
    benchmarktype varchar(10) NOT NULL,
    benchmarkvalue varchar(50) NOT NULL,
    createtime bigint NOT NULL,
    expiretime bigint NOT NULL
);
CREATE UNIQUE INDEX benchmark_lookup ON benchmark (crackerbinaryid, hashtypeid, attackparameters, devicesignature, benchmarktype);
CREATE INDEX benchmark_expiretime ON benchmark (expiretime);

-- Seed the benchmark cache TTL to 30 days (in seconds; 0 disables caching).
INSERT INTO config (configsectionid, item, value)
SELECT 1, 'benchmarkCacheTtl', '2592000'
WHERE NOT EXISTS (SELECT 1 FROM config WHERE item = 'benchmarkCacheTtl');
