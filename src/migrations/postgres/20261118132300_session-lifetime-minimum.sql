-- Session lifetimes now bound the refresh token and may not be shorter than an access token (2 hours)
UPDATE htp_user SET sessionlifetime = 7200 WHERE sessionlifetime < 7200;
