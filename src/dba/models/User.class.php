<?php

namespace DBA;

class User extends AbstractModel {
  private ?int $userId;
  private ?string $username;
  private ?string $email;
  private ?string $passwordHash;
  private ?string $passwordSalt;
  private ?int $isValid;
  private ?int $isComputedPassword;
  private ?int $lastLoginDate;
  private ?int $registeredSince;
  private ?int $sessionLifetime;
  private ?int $rightGroupId;
  private ?string $yubikey;
  private ?string $otp1;
  private ?string $otp2;
  private ?string $otp3;
  private ?string $otp4;
  
  function __construct(?int $userId, ?string $username, ?string $email, ?string $passwordHash, ?string $passwordSalt, ?int $isValid, ?int $isComputedPassword, ?int $lastLoginDate, ?int $registeredSince, ?int $sessionLifetime, ?int $rightGroupId, ?string $yubikey, ?string $otp1, ?string $otp2, ?string $otp3, ?string $otp4) {
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
  
  function getKeyValueDict(): array {
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
  
  static function getFeatures(): array {
    $dict = array();
    $dict['userId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "id", "public" => True, "dba_mapping" => False];
    $dict['username'] = ['read_only' => True, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "name", "public" => True, "dba_mapping" => False];
    $dict['email'] = ['read_only' => False, "type" => "str(150)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "email", "public" => False, "dba_mapping" => False];
    $dict['passwordHash'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => True, "alias" => "passwordHash", "public" => False, "dba_mapping" => False];
    $dict['passwordSalt'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => True, "alias" => "passwordSalt", "public" => False, "dba_mapping" => False];
    $dict['isValid'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "isValid", "public" => False, "dba_mapping" => False];
    $dict['isComputedPassword'] = ['read_only' => True, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "isComputedPassword", "public" => False, "dba_mapping" => False];
    $dict['lastLoginDate'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lastLoginDate", "public" => False, "dba_mapping" => False];
    $dict['registeredSince'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "registeredSince", "public" => False, "dba_mapping" => False];
    $dict['sessionLifetime'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "sessionLifetime", "public" => False, "dba_mapping" => False];
    $dict['rightGroupId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "globalPermissionGroupId", "public" => False, "dba_mapping" => False];
    $dict['yubikey'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "yubikey", "public" => False, "dba_mapping" => False];
    $dict['otp1'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "otp1", "public" => False, "dba_mapping" => False];
    $dict['otp2'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "otp2", "public" => False, "dba_mapping" => False];
    $dict['otp3'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "otp3", "public" => False, "dba_mapping" => False];
    $dict['otp4'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "otp4", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "userId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->userId;
  }
  
  function getId(): ?int {
    return $this->userId;
  }
  
  function setId($id): void {
    $this->userId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getUsername(): ?string {
    return $this->username;
  }
  
  function setUsername(?string $username): void {
    $this->username = $username;
  }
  
  function getEmail(): ?string {
    return $this->email;
  }
  
  function setEmail(?string $email): void {
    $this->email = $email;
  }
  
  function getPasswordHash(): ?string {
    return $this->passwordHash;
  }
  
  function setPasswordHash(?string $passwordHash): void {
    $this->passwordHash = $passwordHash;
  }
  
  function getPasswordSalt(): ?string {
    return $this->passwordSalt;
  }
  
  function setPasswordSalt(?string $passwordSalt): void {
    $this->passwordSalt = $passwordSalt;
  }
  
  function getIsValid(): ?int {
    return $this->isValid;
  }
  
  function setIsValid(?int $isValid): void {
    $this->isValid = $isValid;
  }
  
  function getIsComputedPassword(): ?int {
    return $this->isComputedPassword;
  }
  
  function setIsComputedPassword(?int $isComputedPassword): void {
    $this->isComputedPassword = $isComputedPassword;
  }
  
  function getLastLoginDate(): ?int {
    return $this->lastLoginDate;
  }
  
  function setLastLoginDate(?int $lastLoginDate): void {
    $this->lastLoginDate = $lastLoginDate;
  }
  
  function getRegisteredSince(): ?int {
    return $this->registeredSince;
  }
  
  function setRegisteredSince(?int $registeredSince): void {
    $this->registeredSince = $registeredSince;
  }
  
  function getSessionLifetime(): ?int {
    return $this->sessionLifetime;
  }
  
  function setSessionLifetime(?int $sessionLifetime): void {
    $this->sessionLifetime = $sessionLifetime;
  }
  
  function getRightGroupId(): ?int {
    return $this->rightGroupId;
  }
  
  function setRightGroupId(?int $rightGroupId): void {
    $this->rightGroupId = $rightGroupId;
  }
  
  function getYubikey(): ?string {
    return $this->yubikey;
  }
  
  function setYubikey(?string $yubikey): void {
    $this->yubikey = $yubikey;
  }
  
  function getOtp1(): ?string {
    return $this->otp1;
  }
  
  function setOtp1(?string $otp1): void {
    $this->otp1 = $otp1;
  }
  
  function getOtp2(): ?string {
    return $this->otp2;
  }
  
  function setOtp2(?string $otp2): void {
    $this->otp2 = $otp2;
  }
  
  function getOtp3(): ?string {
    return $this->otp3;
  }
  
  function setOtp3(?string $otp3): void {
    $this->otp3 = $otp3;
  }
  
  function getOtp4(): ?string {
    return $this->otp4;
  }
  
  function setOtp4(?string $otp4): void {
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
