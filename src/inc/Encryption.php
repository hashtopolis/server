<?php

namespace Hashtopolis\inc;

use Hashtopolis\inc\StartupConfig;
use Hashtopolis\inc\Util;

/**
 * Bundle of static functions to generate password hashes, session keys, random strings
 * and other crypt functions
 */
class Encryption {
  /**
   * Generates a session key out of the given parameters.
   *
   * @param int $id sessionID
   * @param int $startTime time of the session start
   * @param string $username username of the user the session belongs to
   * @return string base64 encoded hash
   */
  public static function sessionHash($id, $startTime, $username) {
    $KEY = pack('H*', hash("sha256", $startTime));
    $cycles = Encryption::getCount($username . $startTime, 500, 1000);
    $CIPHER = $username . $startTime;
    $CIPHER = openssl_encrypt($CIPHER, 'blowfish', $KEY, 0, substr(StartupConfig::getInstance()->getPepper(0), 0, 8));
    for ($x = 0; $x < $cycles; $x++) {
      $KEY = pack('H*', hash("sha256", $CIPHER . $id . StartupConfig::getInstance()->getPepper(0) . $KEY));
    }
    return Util::strToHex($KEY);
  }
  
  /**
   * Detect if a given passwords is complex enough to be accepted as password.
   *
   * @param string $string password to check
   * @return boolean true if password is complex enough, false if not
   */
  public static function validPassword($string) {
    if (strlen($string) < 8) {
      return false;
    }
    $number = false;
    $special = false;
    $upper = false;
    $lower = false;
    for ($x = 0; $x < strlen($string); $x++) {
      if (ctype_upper($string[$x])) {
        $upper = true;
      }
      else if (ctype_lower($string[$x])) {
        $lower = true;
      }
      else if (ctype_digit($string[$x])) {
        $number = true;
      }
      else {
        $special = true;
      }
    }
    return ($number && $special && $upper && $lower);
  }
  
  /**
   * Generates a password hash out of the given parameters.
   *
   * @param string $password plain password
   * @param string $salt salt which belongs to the password
   * @return string hash
   */
  public static function passwordHash($password, $salt) {
    $CIPHER = StartupConfig::getInstance()->getPepper(1) . $password . $salt;
    $options = array('cost' => 12);
    $CIPHER = password_hash($CIPHER, PASSWORD_BCRYPT, $options);
    return $CIPHER;
  }
  
  public static function passwordVerify($password, $salt, $hash) {
    $CIPHER = StartupConfig::getInstance()->getPepper(1) . $password . $salt;
    if (!password_verify($CIPHER, $hash)) {
      return false;
    }
    return true;
  }
  
  /**
   * Get the number of cycles for a given string
   *
   * @param string $string
   * @param int $mincycles
   * @param int $maxcycles
   * @return int num cycles
   */
  private static function getCount($string, $mincycles = 3000, $maxcycles = 5000) {
    $count = 0;
    for ($x = 0; $x < strlen($string); $x++) {
      $count += $x * ord($string[$x]) * pow($x, 15);
      $count = $count % 10000;
    }
    return $count % $maxcycles + $mincycles;
  }
  
  /**
   * Generates a hash for the validation of a user email
   *
   * @param int $id userID to validate
   * @param string $username username to validate
   * @return string base64 encoded hash
   */
  public static function validationHash($id, $username) {
    $KEY = pack('H*', hash("sha256", $id));
    $cycles = Encryption::getCount($username . StartupConfig::getInstance()->getPepper(2), 500, 1000);
    $CIPHER = $id . $username;
    $CIPHER = openssl_encrypt($CIPHER, 'blowfish', $KEY, 0, substr(StartupConfig::getInstance()->getPepper(2), 0, 8));
    for ($x = 0; $x < $cycles; $x++) {
      $KEY = pack('H*', hash("sha256", $CIPHER . $id . StartupConfig::getInstance()->getPepper(2) . $username . $KEY));
    }
    return Util::strToHex($KEY);
  }
}



