-- Session lifetimes now bound the refresh token and may not be shorter than an access token (2 hours)
UPDATE `htp_User` SET `sessionLifetime` = 7200 WHERE `sessionLifetime` < 7200;
