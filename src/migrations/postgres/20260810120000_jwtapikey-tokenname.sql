-- Add tokenName column to jwtapikey to allow naming individual API tokens.
ALTER TABLE jwtapikey ADD COLUMN tokenname VARCHAR(100) NOT NULL DEFAULT '';
