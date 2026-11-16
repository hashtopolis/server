-- Interval in minutes of the periodic cleaning background job, which replaced the cleanup
-- that used to run inline on every agent progress report.
INSERT INTO config (configsectionid, item, value)
SELECT 5, 'cleaningInterval', '10'
WHERE NOT EXISTS (SELECT 1 FROM config WHERE item = 'cleaningInterval');
