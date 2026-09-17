-- Remove Yubikey OTP two-factor authentication: user columns, config entries and config section
ALTER TABLE `htp_User`
    DROP COLUMN `yubikey`,
    DROP COLUMN `otp1`,
    DROP COLUMN `otp2`,
    DROP COLUMN `otp3`,
    DROP COLUMN `otp4`;

DELETE FROM `Config` WHERE `item` IN ('yubikey_id', 'yubikey_key', 'yubikey_url');

DELETE FROM `ConfigSection` WHERE `sectionName` = 'Yubikey';
