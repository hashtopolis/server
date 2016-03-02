<?php

class User extends AbstractModel {
	private $modelName = "User";
	
	// Modelvariables
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
	
	
	function __construct($userId, $username, $email, $passwordHash, $passwordSalt, $isValid, $isComputedPassword, $lastLoginDate, $registeredSince, $sessionLifetime, $rightGroupId) {
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

	}
	
	function getKeyValueDict() {
		$dict = array ();
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

	function getUsername(){
		return $this->username;
	}

	function setUsername($username){
		$this->username = $username;
	}

	function getEmail(){
		return $this->email;
	}

	function setEmail($email){
		$this->email = $email;
	}

	function getPasswordHash(){
		return $this->passwordHash;
	}

	function setPasswordHash($passwordHash){
		$this->passwordHash = $passwordHash;
	}

	function getPasswordSalt(){
		return $this->passwordSalt;
	}

	function setPasswordSalt($passwordSalt){
		$this->passwordSalt = $passwordSalt;
	}

	function getIsValid(){
		return $this->isValid;
	}

	function setIsValid($isValid){
		$this->isValid = $isValid;
	}

	function getIsComputedPassword(){
		return $this->isComputedPassword;
	}

	function setIsComputedPassword($isComputedPassword){
		$this->isComputedPassword = $isComputedPassword;
	}

	function getLastLoginDate(){
		return $this->lastLoginDate;
	}

	function setLastLoginDate($lastLoginDate){
		$this->lastLoginDate = $lastLoginDate;
	}

	function getRegisteredSince(){
		return $this->registeredSince;
	}

	function setRegisteredSince($registeredSince){
		$this->registeredSince = $registeredSince;
	}

	function getSessionLifetime(){
		return $this->sessionLifetime;
	}

	function setSessionLifetime($sessionLifetime){
		$this->sessionLifetime = $sessionLifetime;
	}

	function getRightGroupId(){
		return $this->rightGroupId;
	}

	function setRightGroupId($rightGroupId){
		$this->rightGroupId = $rightGroupId;
	}
}
