-- Add tokenName column to JwtApiKey to allow naming individual API tokens.
ALTER TABLE `JwtApiKey` ADD COLUMN `tokenName` VARCHAR(100) NOT NULL DEFAULT '';
