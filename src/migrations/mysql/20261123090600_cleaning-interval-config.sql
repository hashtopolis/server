-- Interval in minutes of the periodic cleaning background job, which replaced the cleanup
-- that used to run inline on every agent progress report.
INSERT INTO `Config` (`configSectionId`, `item`, `value`)
SELECT 5, 'cleaningInterval', '10'
WHERE NOT EXISTS (SELECT 1 FROM `Config` WHERE `item` = 'cleaningInterval');
