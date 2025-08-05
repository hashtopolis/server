<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Factory;
use DBA\HashType;

if (!isset($PRESENT["v0.14.x_update_agent_binary"])) {
  if (Util::databaseColumnExists("AgentBinary", "type")) {
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `AgentBinary` RENAME COLUMN `type` to `binaryType`;");
    $EXECUTED["v0.14.x_update_agent_binary"] = true;
  }
}
if (!isset($PRESENT["v0.14.x_update_hashtypes"])){
  $hashTypes = [
    new HashType( 2630 ,  "md5(md5($pass.$salt))" , 0, 0),
    new HashType( 3610 ,  "md5(md5(md5($pass)).$salt)" , 0, 0),
    new HashType( 3730 ,  "md5($salt1.strtoupper(md5($salt2.$pass)))" , 0, 0),
    new HashType( 4420 ,  "md5(sha1($pass.$salt))" , 0, 0),
    new HashType( 4430 ,  "md5(sha1($salt.$pass))" , 0, 0),
    new HashType( 6050 ,  "HMAC-RIPEMD160 (key = $pass)" , 0, 0),
    new HashType( 6060 ,  "HMAC-RIPEMD160 (key = $salt)" , 0, 0),
    new HashType( 7350 ,  "IPMI2 RAKP HMAC-MD5" , 0, 0),
    new HashType( 10510 ,  "PDF 1.3 - 1.6 (Acrobat 4 - 8) w/ RC4-40" , 0, 0),
    new HashType( 12150 ,  "Apache Shiro 1 SHA-512" , 0, 0),
    new HashType( 14200 ,  "RACF KDFAES" , 0, 0),
    new HashType( 16501 ,  "Perl Mojolicious session cookie (HMAC-SHA256, >= v9.19)" , 0, 0),
    new HashType( 17020 ,  "GPG (AES-128/AES-256 (SHA-512($pass)))" , 0, 0),
    new HashType( 17030 ,  "GPG (AES-128/AES-256 (SHA-256($pass)))" , 0, 0),
    new HashType( 17040 ,  "GPG (CAST5 (SHA-1($pass)))" , 0, 0),
    new HashType( 19210 ,  "QNX 7 /etc/shadow (SHA512)" , 0, 0),
    new HashType( 20712 ,  "RSA Security Analytics / NetWitness (sha256)" , 0, 0),
    new HashType( 20730 ,  "sha256(sha256($pass.$salt))" , 0, 0),
    new HashType( 21310 ,  "md5($salt1.sha1($salt2.$pass))" , 0, 0),
    new HashType( 21900 ,  "md5(md5(md5($pass.$salt1)).$salt2)" , 0, 0),
    new HashType( 22800 ,  "Simpla CMS - md5($salt.$pass.md5($pass))" , 0, 0),
    new HashType( 24000 ,  "BestCrypt v4 Volume Encryption" , 0, 0),
    new HashType( 26610 ,  "MetaMask Wallet (short hash, plaintext check)" , 0, 0),
    new HashType( 29800 ,  "Bisq .wallet (scrypt)" , 0, 0),
    new HashType( 29910 ,  "ENCsecurity Datavault (PBKDF2/no keychain)" , 0, 0),
    new HashType( 29920 ,  "ENCsecurity Datavault (PBKDF2/keychain)" , 0, 0),
    new HashType( 29930 ,  "ENCsecurity Datavault (MD5/no keychain)" , 0, 0),
    new HashType( 29940 ,  "ENCsecurity Datavault (MD5/keychain)" , 0, 0),
    new HashType( 30420 ,  "DANE RFC7929/RFC8162 SHA2-256" , 0, 0),
    new HashType( 30500 ,  "md5(md5($salt).md5(md5($pass)))" , 0, 0),
    new HashType( 30600 ,  "bcrypt(sha256($pass)) / bcryptsha256" , 0, 0),
    new HashType( 30601 ,  "bcrypt-sha256 v2 bcrypt(HMAC-SHA256($pass))" , 0, 0),
    new HashType( 30700 ,  "Anope IRC Services (enc_sha256)" , 0, 0),
    new HashType( 30901 ,  "Bitcoin raw private key (P2PKH), compressed" , 0, 0),
    new HashType( 30902 ,  "Bitcoin raw private key (P2PKH), uncompressed" , 0, 0),
    new HashType( 30903 ,  "Bitcoin raw private key (P2WPKH, Bech32), compressed" , 0, 0),
    new HashType( 30904 ,  "Bitcoin raw private key (P2WPKH, Bech32), uncompressed" , 0, 0),
    new HashType( 30905 ,  "Bitcoin raw private key (P2SH(P2WPKH)), compressed" , 0, 0),
    new HashType( 30906 ,  "Bitcoin raw private key (P2SH(P2WPKH)), uncompressed" , 0, 0),
    new HashType( 31100 ,  "ShangMi 3 (SM3)" , 0, 0),
    new HashType( 34211 ,  "MurmurHash64A truncated (zero seed)" , 0, 0),
    new HashType( 31000 ,  "BLAKE2s-256" , 0, 0),
    new HashType( 31100 ,  "ShangMi 3 (SM3)" , 0, 0),
    new HashType( 31200 ,  "Veeam VBK" , 0, 0),
    new HashType( 31300 ,  "MS SNTP" , 0, 0),
    new HashType( 31400 ,  "SecureCRT MasterPassphrase v2" , 0, 0),
    new HashType( 31500 ,  "Domain Cached Credentials (DCC), MS Cache (NT)" , 0, 0),
    new HashType( 31600 ,  "Domain Cached Credentials 2 (DCC2), MS Cache 2, (NT)" , 0, 0),
    new HashType( 31700 ,  "md5(md5(md5($pass).$salt1).$salt2)" , 0, 0),
    new HashType( 31800 ,  "1Password, mobilekeychain (1Password 8)" , 0, 0),
    new HashType( 31900 ,  "MetaMask Mobile Wallet" , 0, 0),
    new HashType( 32000 ,  "NetIQ SSPR (MD5)" , 0, 0),
    new HashType( 32010 ,  "NetIQ SSPR (SHA1)" , 0, 0),
    new HashType( 32020 ,  "NetIQ SSPR (SHA-1 with Salt)" , 0, 0),
    new HashType( 32030 ,  "NetIQ SSPR (SHA-256 with Salt)" , 0, 0),
    new HashType( 32031 ,  "Adobe AEM (SSPR, SHA-256 with Salt)" , 0, 0),
    new HashType( 32040 ,  "NetIQ SSPR (SHA-512 with Salt)" , 0, 0),
    new HashType( 32041 ,  "Adobe AEM (SSPR, SHA-512 with Salt)" , 0, 0),
    new HashType( 32050 ,  "NetIQ SSPR (PBKDF2WithHmacSHA1)" , 0, 0),
    new HashType( 32060 ,  "NetIQ SSPR (PBKDF2WithHmacSHA256)" , 0, 0),
    new HashType( 32070 ,  "NetIQ SSPR (PBKDF2WithHmacSHA512)" , 0, 0),
    new HashType( 32100 ,  "Kerberos 5, etype 17, AS-REP" , 0, 0),
    new HashType( 32200 ,  "Kerberos 5, etype 18, AS-REP" , 0, 0),
    new HashType( 32300 ,  "Empire CMS (Admin password)" , 0, 0),
    new HashType( 32410 ,  "sha512(sha512($pass).$salt)" , 0, 0),
    new HashType( 32420 ,  "sha512(sha512_bin($pass).$salt)" , 0, 0),
    new HashType( 32500 ,  "Dogechain.info Wallet" , 0, 0),
    new HashType( 32600 ,  "CubeCart (whirlpool($salt.$pass.$salt))" , 0, 0),
    new HashType( 32700 ,  "Kremlin Encrypt 3.0 w/NewDES" , 0, 0),
    new HashType( 32800 ,  "md5(sha1(md5($pass)))" , 0, 0),
    new HashType( 32900 ,  "PBKDF1-SHA1" , 0, 0),
    new HashType( 33000 ,  "md5($salt1.$pass.$salt2)" , 0, 0),
    new HashType( 33100 ,  "md5($salt.md5($pass).$salt)" , 0, 0),
    new HashType( 33300 ,  "HMAC-BLAKE2S (key = $pass)" , 0, 0),
    new HashType( 33400 ,  "mega.nz password-protected link (PBKDF2-HMAC-SHA512)" , 0, 0),
    new HashType( 33500 ,  "RC4 40-bit DropN" , 0, 0),
    new HashType( 33501 ,  "RC4 72-bit DropN" , 0, 0),
    new HashType( 33502 ,  "RC4 104-bit DropN" , 0, 0),
    new HashType( 33600 ,  "RIPEMD-320" , 0, 0),
    new HashType( 33650 ,  "HMAC-RIPEMD320 (key = $pass)" , 0, 0),
    new HashType( 33660 ,  "HMAC-RIPEMD320 (key = $salt)" , 0, 0),
    new HashType( 33700 ,  "Microsoft Online Account (PBKDF2-HMAC-SHA256 + AES256)" , 0, 0),
    new HashType( 33800 ,  "WBB4 (Woltlab Burning Board) Plugin [bcrypt(bcrypt($pass))]" , 0, 0),
    new HashType( 33900 ,  "Citrix NetScaler (PBKDF2-HMAC-SHA256)" , 0, 0),
    new HashType( 34000 ,  "Argon2" , 0, 0),
    new HashType( 34100 ,  "LUKS v2 argon2id + SHA-256 + AES" , 0, 0),
    new HashType( 34200 ,  "MurmurHash64A" , 0, 0),
    new HashType( 34201 ,  "MurmurHash64A (zero seed)" , 0, 0),
    new HashType( 34211 ,  "MurmurHash64A truncated (zero seed)" , 0, 0),
    new HashType( 70000 ,  "argon2id [Bridged: reference implementation + tunings]" , 0, 0),
    new HashType( 70100 ,  "scrypt [Bridged: Scrypt-Jane ROMix]" , 0, 0),
    new HashType( 70200 ,  "scrypt [Bridged: Scrypt-Yescrypt]" , 0, 0),
    new HashType( 72000 ,  "Generic Hash [Bridged: Python Interpreter free-threading]" , 0, 0),
    new HashType( 73000 ,  "Generic Hash [Bridged: Python Interpreter with GIL]" , 0, 0),
  ];

  foreach ($hashtypes as $hashtype) {
    $check = Factory::getHashTypeFactory()->get($hashtype->getId());
    if ($check === null) {
      Factory::getHashTypeFactory()->save($hashtype);
    }
  }
  $EXECUTED["v0.14.x_update_hashtypes"] = true;
}
?>