-- Benchmark caching (issue #879): cache an agent's benchmark result keyed by the
-- factors that determine cracking speed, so agents with identical hardware reuse
-- a benchmark instead of re-running it on every task pickup.
CREATE TABLE benchmark (
    benchmarkid SERIAL PRIMARY KEY,
    crackerbinaryid integer NOT NULL REFERENCES crackerbinary (crackerbinaryid),
    hashmode integer NOT NULL,
    attackparameters varchar(64) NOT NULL,
    devicesignature varchar(64) NOT NULL,
    benchmarktype varchar(10) NOT NULL,
    benchmarkvalue varchar(50) NOT NULL,
    createtime bigint NOT NULL,
    expiretime bigint NOT NULL
);
CREATE INDEX benchmark_lookup ON benchmark (crackerbinaryid, hashmode, attackparameters, devicesignature, benchmarktype);
CREATE INDEX benchmark_expiretime ON benchmark (expiretime);

-- Seed the benchmark cache TTL (in seconds, 0 disables caching) for existing installs.
INSERT INTO config (configsectionid, item, value)
SELECT 1, 'benchmarkCacheTtl', '0'
WHERE NOT EXISTS (SELECT 1 FROM config WHERE item = 'benchmarkCacheTtl');
