<?php

namespace DBA;

class User extends AbstractModel {
  private $userId;
  private $username;
  private $email;
  private $passwordHash;
  private $passwordSalt;
  private $isValid;
  private $isComputedPassword;
  private $lastLoginDate;
  private $registeredSince;
  private $sessionLifetime;
  private $rightGroupId;
  private $yubikey;
  private $otp1;
  private $otp2;
  private $otp3;
  private $otp4;
  
  function __construct($userId, $username, $email, $passwordHash, $passwordSalt, $isValid, $isComputedPassword, $lastLoginDate, $registeredSince, $sessionLifetime, $rightGroupId, $yubikey, $otp1, $otp2, $otp3, $otp4) {
    $this->userId = $userId;
    $this->username = $username;
    $this->email = $email;
    $this->passwordHash = $passwordHash;
    $this->passwordSalt = $passwordSalt;
    $this->isValid = $isValid;
    $this->isComputedPassword = $isComputedPassword;
    $this->lastLoginDate = $lastLoginDate;
    $this->registeredSince = $registeredSince;
    $this->sessionLifetime = $sessionLifetime;
    $this->rightGroupId = $rightGroupId;
    $this->yubikey = $yubikey;
    $this->otp1 = $otp1;
    $this->otp2 = $otp2;
    $this->otp3 = $otp3;
    $this->otp4 = $otp4;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['userId'] = $this->userId;
    $dict['username'] = $this->username;
    $dict['email'] = $this->email;
    $dict['passwordHash'] = $this->passwordHash;
    $dict['passwordSalt'] = $this->passwordSalt;
    $dict['isValid'] = $this->isValid;
    $dict['isComputedPassword'] = $this->isComputedPassword;
    $dict['lastLoginDate'] = $this->lastLoginDate;
    $dict['registeredSince'] = $this->registeredSince;
    $dict['sessionLifetime'] = $this->sessionLifetime;
    $dict['rightGroupId'] = $this->rightGroupId;
    $dict['yubikey'] = $this->yubikey;
    $dict['otp1'] = $this->otp1;
    $dict['otp2'] = $this->otp2;
    $dict['otp3'] = $this->otp3;
    $dict['otp4'] = $this->otp4;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['userId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "id", "public" => True];
    $dict['username'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "name", "public" => True];
    $dict['email'] = ['read_only' => False, "type" => "str(150)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "email", "public" => False];
    $dict['passwordHash'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => True, "alias" => "passwordHash", "public" => False];
    $dict['passwordSalt'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => True, "alias" => "passwordSalt", "public" => False];
    $dict['isValid'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "isValid", "public" => False];
    $dict['isComputedPassword'] = ['read_only' => True, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "isComputedPassword", "public" => False];
    $dict['lastLoginDate'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lastLoginDate", "public" => False];
    $dict['registeredSince'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "registeredSince", "public" => False];
    $dict['sessionLifetime'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "sessionLifetime", "public" => False];
    $dict['rightGroupId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "globalPermissionGroupId", "public" => False];
    $dict['yubikey'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "yubikey", "public" => False];
    $dict['otp1'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "otp1", "public" => False];
    $dict['otp2'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "otp2", "public" => False];
    $dict['otp3'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "otp3", "public" => False];
    $dict['otp4'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "otp4", "public" => False];

    return $dict;
  }

  function getPrimaryKey() {
    return "userId";
  }
  
  function getPrimaryKeyValue() {
    return $this->userId;
  }
  
  function getId() {
    return $this->userId;
  }
  
  function setId($id) {
    $this->userId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getUsername() {
    return $this->username;
  }
  
  function setUsername($username) {
    $this->username = $username;
  }
  
  function getEmail() {
    return $this->email;
  }
  
  function setEmail($email) {
    $this->email = $email;
  }
  
  function getPasswordHash() {
    return $this->passwordHash;
  }
  
  function setPasswordHash($passwordHash) {
    $this->passwordHash = $passwordHash;
  }
  
  function getPasswordSalt() {
    return $this->passwordSalt;
  }
  
  function setPasswordSalt($passwordSalt) {
    $this->passwordSalt = $passwordSalt;
  }
  
  function getIsValid() {
    return $this->isValid;
  }
  
  function setIsValid($isValid) {
    $this->isValid = $isValid;
  }
  
  function getIsComputedPassword() {
    return $this->isComputedPassword;
  }
  
  function setIsComputedPassword($isComputedPassword) {
    $this->isComputedPassword = $isComputedPassword;
  }
  
  function getLastLoginDate() {
    return $this->lastLoginDate;
  }
  
  function setLastLoginDate($lastLoginDate) {
    $this->lastLoginDate = $lastLoginDate;
  }
  
  function getRegisteredSince() {
    return $this->registeredSince;
  }
  
  function setRegisteredSince($registeredSince) {
    $this->registeredSince = $registeredSince;
  }
  
  function getSessionLifetime() {
    return $this->sessionLifetime;
  }
  
  function setSessionLifetime($sessionLifetime) {
    $this->sessionLifetime = $sessionLifetime;
  }
  
  function getRightGroupId() {
    return $this->rightGroupId;
  }
  
  function setRightGroupId($rightGroupId) {
    $this->rightGroupId = $rightGroupId;
  }
  
  function getYubikey() {
    return $this->yubikey;
  }
  
  function setYubikey($yubikey) {
    $this->yubikey = $yubikey;
  }
  
  function getOtp1() {
    return $this->otp1;
  }
  
  function setOtp1($otp1) {
    $this->otp1 = $otp1;
  }
  
  function getOtp2() {
    return $this->otp2;
  }
  
  function setOtp2($otp2) {
    $this->otp2 = $otp2;
  }
  
  function getOtp3() {
    return $this->otp3;
  }
  
  function setOtp3($otp3) {
    $this->otp3 = $otp3;
  }
  
  function getOtp4() {
    return $this->otp4;
  }
  
  function setOtp4($otp4) {
    $this->otp4 = $otp4;
  }
  
  const USER_ID = "userId";
  const USERNAME = "username";
  const EMAIL = "email";
  const PASSWORD_HASH = "passwordHash";
  const PASSWORD_SALT = "passwordSalt";
  const IS_VALID = "isValid";
  const IS_COMPUTED_PASSWORD = "isComputedPassword";
  const LAST_LOGIN_DATE = "lastLoginDate";
  const REGISTERED_SINCE = "registeredSince";
  const SESSION_LIFETIME = "sessionLifetime";
  const RIGHT_GROUP_ID = "rightGroupId";
  const YUBIKEY = "yubikey";
  const OTP1 = "otp1";
  const OTP2 = "otp2";
  const OTP3 = "otp3";
  const OTP4 = "otp4";

  const PERM_CREATE = "permUserCreate";
  const PERM_READ = "permUserRead";
  const PERM_UPDATE = "permUserUpdate";
  const PERM_DELETE = "permUserDelete";
}
