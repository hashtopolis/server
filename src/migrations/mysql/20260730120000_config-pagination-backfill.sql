-- Backfill config defaults added in PR #1173 (pagination page size) for installs
-- that started on v0.14.4-v0.14.8 and were never backfilled, as the legacy upgrade
-- script update_v0.14.3_v0.14.x.php only ran for installed versions <= 0.14.3.
INSERT INTO `Config` (`configSectionId`, `item`, `value`)
SELECT 3, 'defaultPageSize', '10000'
WHERE NOT EXISTS (SELECT 1 FROM `Config` WHERE `item` = 'defaultPageSize');

INSERT INTO `Config` (`configSectionId`, `item`, `value`)
SELECT 3, 'maxPageSize', '50000'
WHERE NOT EXISTS (SELECT 1 FROM `Config` WHERE `item` = 'maxPageSize');
