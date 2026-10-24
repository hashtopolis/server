-- Store the BLAKE3 checksum of a file's content, computed asynchronously by the background job
-- runner. NULL means not computed yet; it is cleared whenever the stored bytes change so a stale
-- digest can never be served.
ALTER TABLE `File` ADD COLUMN `checksum` VARCHAR(64) DEFAULT NULL;
