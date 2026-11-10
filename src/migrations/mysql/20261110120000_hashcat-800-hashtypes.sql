-- hashcat 8.0.0 introduced 217 new hash-modes which are not part of the seed
-- list of the initial migration. Fresh installs get them from hashtypes.json
-- during the setup, this migration provides them for upgraded installs, so
-- the state does not depend on the scan of the binary completing. The scan
-- creates missing hashtypes as well, the NOT EXISTS keeps this idempotent for
-- installs where the scan already added them.
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 3300, 'MD5(Sun), SunMD5', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 3300);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 4000, 'Hash Recipe, see --hash-recipe', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 4000);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 17050, 'GPG (AES-OCB-128 (SHA-1($pass)))', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 17050);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 24430, 'PKCS#12 (.pfx, .p12) (SHA1 + HMAC-SHA1)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 24430);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 24440, 'PKCS#12 (.pfx, .p12) (SHA256 + HMAC-SHA256)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 24440);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 26150, 'Mozilla key4.db SHA384', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 26150);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 29491, 'VeraCrypt BLAKE2s-256 + XTS 512 bit', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 29491);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 29492, 'VeraCrypt BLAKE2s-256 + XTS 1024 bit', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 29492);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 29493, 'VeraCrypt BLAKE2s-256 + XTS 1536 bit', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 29493);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 34301, 'KeePass AESKDF (KDBX v4)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 34301);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 35300, 'Kerberos 5, etype 23, TGS-REP (NT)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 35300);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 35400, 'Kerberos 5, etype 23, AS-REP (NT)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 35400);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 35500, 'Wordpress bcrypt(hmac-sha384($pass))', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 35500);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 35600, 'gost12512crypt [$gost12512hash$] (Unix)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 35600);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 35800, 'Symfony Legacy SHA256', 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 35800);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 36000, 'BIP39 Passphrase Recovery (ASCII, P2SH/P2PKH/P2WPKH)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 36000);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 36100, 'yescrypt', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 36100);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 36200, 'gost-yescrypt', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 36200);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 36300, 'scryptcrypt, scrypt (Unix)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 36300);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 36400, 'KDE KWallet 4.13+ (PBKDF2-HMAC-SHA512, Blowfish)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 36400);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 36410, 'KDE KWallet &lt; 4.13 (SHA-1, Blowfish)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 36410);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 36600, 'PBKDF2-HMAC-SHA512(utf16le($pass))', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 36600);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 36601, 'MSSQL (2025)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 36601);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 36800, 'OpenSSH Private Keys (bcrypt-pbkdf)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 36800);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 36900, 'iClass Standard (DES + hash0 + iClass Cipher, dual-MAC)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 36900);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 36901, 'iClass Legacy (partial div_key + iClass Cipher, dual-MAC)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 36901);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 37000, 'MIFARE Ultralight C 3DES Key Recovery', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 37000);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 37400, 'RACF DES Passphrase', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 37400);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 37500, 'RACF KDFAES Passphrase', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 37500);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 37600, 'SASL DIGEST-MD5', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 37600);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 37601, 'POP3 APOP', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 37601);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 37700, 'Cardano Eternl Wallet (PBKDF2-HMAC-SHA512, ChaCha20)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 37700);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 38000, 'Mikrotik RouterOS EC-SRP5 (Curve25519)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 38000);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 38200, 'Cisco-IOS $14$ (MD5 (Type 5) + scrypt, Convoluted)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 38200);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 38300, 'Apple DMG (encrcdsa v2, PBKDF2-HMAC-SHA1 + AES-192 key)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 38300);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 38400, 'Kerberos 5, etype 1/2/3, DB', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 38400);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 38500, 'Kerberos 5, etype 3, TGS-REP', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 38500);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 38600, 'Kerberos 5, etype 3, AS-REP', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 38600);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 38700, 'FreeBSD GELI', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 38700);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 38800, 'OpenBSD softraid FDE (PBKDF2-HMAC-SHA1)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 38800);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 38810, 'OpenBSD softraid FDE (bcrypt-pbkdf)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 38810);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 38900, 'Google fscrypt (Argon2id)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 38900);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40000, 'XCA database password', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40000);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40001, 'OSPFv2/RIPv2 MD5 authentication', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40001);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40002, 'Huawei / H3C / HPE Comware $h$6$', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40002);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40004, 'UUID v3 (RFC 4122 name-based, MD5)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40004);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40005, 'UUID v5 (RFC 4122 name-based, SHA-1)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40005);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40006, 'MySQL caching_sha2_password challenge-response (fast auth)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40006);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40007, 'MODX Evolution (uncrypt)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40007);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40009, 'sha224($pass.$salt)', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40009);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40010, 'sha224($salt.$pass)', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40010);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40011, 'Password Safe v3', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40011);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40012, 'Cisco-ISE Hashed Password (SHA256)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40012);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40013, 'HMAC-RIPEMD160 (key = $pass)', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40013);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40014, 'HMAC-RIPEMD160 (key = $salt)', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40014);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40015, 'AIX {ssha256}', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40015);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40016, 'AIX {ssha512}', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40016);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40017, 'AIX {ssha1}', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40017);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40018, 'macOS v10.8+ (PBKDF2-SHA512)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40018);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40019, 'GRUB 2', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40019);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40020, 'Drupal7', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40020);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40021, 'Cisco-IOS $8$ (PBKDF2-SHA256)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40021);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40022, 'Django (PBKDF2-SHA256)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40022);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40023, 'SAP CODVN H (PWDSALTEDHASH) iSSHA-1', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40023);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40024, 'PBKDF2-HMAC-SHA256', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40024);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40025, 'RedHat 389-DS LDAP (PBKDF2-HMAC-SHA256)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40025);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40026, 'PrestaShop', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40026);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40027, 'HMAC-Streebog-256 (key = $pass), big-endian', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40027);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40028, 'HMAC-Streebog-256 (key = $salt), big-endian', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40028);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40029, 'HMAC-Streebog-512 (key = $pass), big-endian', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40029);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40030, 'HMAC-Streebog-512 (key = $salt), big-endian', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40030);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40031, 'PBKDF2-HMAC-MD5', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40031);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40032, 'PBKDF2-HMAC-SHA1', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40032);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40033, 'Atlassian (PBKDF2-HMAC-SHA1)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40033);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40034, 'PBKDF2-HMAC-SHA512', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40034);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40035, 'Apache Shiro 1 SHA-512', 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40035);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40036, 'Oracle T: Type (Oracle 12+)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40036);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40037, 'MS-AzureSync PBKDF2-HMAC-SHA256', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40037);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40038, 'Juniper/NetBSD sha1crypt', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40038);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40039, 'sha1(md5(md5($pass)))', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40039);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40040, 'Blockchain, My Wallet, Second Password (SHA256)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40040);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40041, 'QNX 7 /etc/shadow (SHA512)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40041);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40042, 'Python passlib pbkdf2-sha512', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40042);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40043, 'Python passlib pbkdf2-sha256', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40043);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40044, 'Python passlib pbkdf2-sha1', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40044);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40045, 'Oracle Transportation Management (SHA256)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40045);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40046, 'sha256($salt.sha256($pass))', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40046);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40047, 'sha256(sha256($pass.$salt))', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40047);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40048, 'md5($salt.sha1($salt.$pass))', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40048);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40049, 'sha256($salt.sha256_bin($pass))', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40049);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40050, 'Web2py pbkdf2-sha512', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40050);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40051, 'Simpla CMS - md5($salt.$pass.md5($pass))', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40051);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40052, 'XMPP SCRAM PBKDF2-SHA1', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40052);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40053, 'MongoDB ServerKey SCRAM-SHA-256', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40053);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40054, 'FortiGate256 (FortiOS256)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40054);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40055, 'PostgreSQL SCRAM-SHA-256', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40055);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40056, 'Flask Session Cookie ($salt.$salt.$pass)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40056);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40057, 'NetIQ SSPR (MD5)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40057);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40058, 'NetIQ SSPR (SHA1)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40058);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40059, 'NetIQ SSPR (SHA-1 with Salt)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40059);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40060, 'NetIQ SSPR (SHA-256 with Salt)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40060);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40061, 'Adobe AEM (SSPR, SHA-256 with Salt)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40061);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40062, 'NetIQ SSPR (SHA-512 with Salt)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40062);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40063, 'Adobe AEM (SSPR, SHA-512 with Salt)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40063);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40064, 'NetIQ SSPR (PBKDF2WithHmacSHA1)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40064);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40065, 'NetIQ SSPR (PBKDF2WithHmacSHA256)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40065);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40066, 'NetIQ SSPR (PBKDF2WithHmacSHA512)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40066);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40067, 'sha512(sha512($pass).$salt)', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40067);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40068, 'sha512(sha512_bin($pass).$salt)', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40068);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40069, 'CubeCart (whirlpool($salt.$pass.$salt))', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40069);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40070, 'PBKDF1-SHA1', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40070);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40071, 'md5($salt.md5($pass).$salt)', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40071);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40072, 'HMAC-BLAKE2S (key = $pass)', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40072);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40073, 'HMAC-RIPEMD320 (key = $pass)', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40073);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40074, 'HMAC-RIPEMD320 (key = $salt)', 1, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40074);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40075, 'Citrix NetScaler (PBKDF2-HMAC-SHA256)', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40075);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40076, 'sha224(sha224($pass))', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40076);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40077, 'sha224(sha1($pass))', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40077);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40078, 'SAP CODVN H (PWDSALTEDHASH) isSHA512', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40078);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40079, 'phpass(md5($pass))', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40079);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40080, 'NTLM(NTLM_bin($pass))', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40080);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40081, 'SAP CODVN H (PWDSALTEDHASH) iSSHA-256', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40081);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40082, 'SAP CODVN H (PWDSALTEDHASH) iSSHA-384', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40082);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40083, 'Fernet (key = SHA256($pass))', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40083);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40084, 'MD2', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40084);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40085, 'SHA-0', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40085);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40086, 'RIPEMD-128', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40086);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40087, 'RIPEMD-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40087);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40088, 'GOST R 34.11-94 (CryptoPro)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40088);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40089, 'Tiger', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40089);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40090, 'Tiger-128', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40090);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40091, 'Tiger-160', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40091);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40092, 'Tiger2', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40092);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40093, 'TTH (Tiger Tree Hash, single leaf)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40093);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40094, 'Snefru-128', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40094);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40095, 'Snefru-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40095);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40096, 'Skein-512-224', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40096);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40097, 'Skein-512-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40097);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40098, 'Skein-512-384', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40098);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40099, 'Skein-512-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40099);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40100, 'PANAMA', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40100);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40101, 'MDC-2', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40101);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40102, 'HAVAL-128 (3 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40102);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40103, 'HAVAL-128 (4 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40103);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40104, 'HAVAL-128 (5 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40104);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40105, 'HAVAL-160 (3 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40105);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40106, 'HAVAL-160 (4 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40106);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40107, 'HAVAL-160 (5 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40107);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40108, 'HAVAL-192 (3 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40108);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40109, 'HAVAL-192 (4 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40109);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40110, 'HAVAL-192 (5 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40110);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40111, 'HAVAL-224 (3 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40111);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40112, 'HAVAL-224 (4 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40112);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40113, 'HAVAL-224 (5 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40113);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40114, 'HAVAL-256 (3 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40114);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40115, 'HAVAL-256 (4 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40115);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40116, 'HAVAL-256 (5 passes)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40116);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40117, 'BLAKE-224', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40117);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40118, 'BLAKE-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40118);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40119, 'BLAKE-384', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40119);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40120, 'BLAKE-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40120);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40121, 'BMW-224', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40121);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40122, 'BMW-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40122);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40123, 'BMW-384', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40123);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40124, 'BMW-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40124);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40125, 'CubeHash-224', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40125);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40126, 'CubeHash-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40126);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40127, 'CubeHash-384', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40127);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40128, 'CubeHash-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40128);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40129, 'ECHO-224', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40129);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40130, 'ECHO-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40130);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40131, 'ECHO-384', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40131);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40132, 'ECHO-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40132);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40133, 'Fugue-224', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40133);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40134, 'Fugue-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40134);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40135, 'Fugue-384', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40135);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40136, 'Fugue-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40136);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40137, 'Groestl-224', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40137);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40138, 'Groestl-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40138);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40139, 'Groestl-384', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40139);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40140, 'Groestl-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40140);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40141, 'Hamsi-224', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40141);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40142, 'Hamsi-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40142);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40143, 'Hamsi-384', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40143);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40144, 'Hamsi-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40144);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40145, 'JH-224', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40145);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40146, 'JH-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40146);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40147, 'JH-384', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40147);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40148, 'JH-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40148);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40149, 'Luffa-224', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40149);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40150, 'Luffa-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40150);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40151, 'Luffa-384', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40151);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40152, 'Luffa-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40152);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40153, 'Shabal-224', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40153);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40154, 'Shabal-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40154);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40155, 'Shabal-384', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40155);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40156, 'Shabal-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40156);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40157, 'SHAvite-3-224', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40157);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40158, 'SHAvite-3-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40158);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40159, 'SHAvite-3-384', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40159);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40160, 'SHAvite-3-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40160);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40161, 'SIMD-224', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40161);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40162, 'SIMD-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40162);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40163, 'SIMD-384', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40163);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40164, 'SIMD-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40164);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40165, 'HAS-160', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40165);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40166, 'EDON-R-256', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40166);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40167, 'EDON-R-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40167);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40168, 'MD6-128', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40168);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40170, 'MD6-512', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40170);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40171, 'RadioGatun[32] (first 128 bits)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40171);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40172, 'RadioGatun[64]', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40172);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40173, 'RIPEMD (original)', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40173);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40174, 'Whirlpool-0', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40174);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 40175, 'Whirlpool-T', 0, 0 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 40175);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 75000, 'bcrypt $2*$, Blowfish (Unix) [Bridged: FPGA]', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 75000);
INSERT INTO `HashType` (`hashTypeId`, `description`, `isSalted`, `isSlowHash`)
SELECT 79000, 'bcrypt $2*$, Blowfish (Unix) [Bridged: FPGA fleet sim]', 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `HashType` WHERE `hashTypeId` = 79000);
