-- In case of an upgrade from 0.14.8 where with new setups the column was still named 'type', rename it to 'binaryType' before the UPDATE.
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'AgentBinary' AND COLUMN_NAME = 'type');
SET @sql = IF(@col_exists > 0, 'ALTER TABLE AgentBinary RENAME COLUMN `type` TO `binaryType`', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE AgentBinary SET version='0.7.5' WHERE version='0.7.4' AND filename='hashtopolis.zip' AND binaryType='python';
