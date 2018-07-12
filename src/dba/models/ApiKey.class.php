<?php

namespace DBA;

class ApiKey extends AbstractModel {
  private $apiKeyId;
  private $startValid;
  private $endValid;
  private $accessKey;
  private $accessCount;
  private $permissions;
  private $userId;
  
  function __construct($apiKeyId, $startValid, $endValid, $accessKey, $accessCount, $permissions, $userId) {
    $this->apiKeyId = $apiKeyId;
    $this->startValid = $startValid;
    $this->endValid = $endValid;
    $this->accessKey = $accessKey;
    $this->accessCount = $accessCount;
    $this->permissions = $permissions;
    $this->userId = $userId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['apiKeyId'] = $this->apiKeyId;
    $dict['startValid'] = $this->startValid;
    $dict['endValid'] = $this->endValid;
    $dict['accessKey'] = $this->accessKey;
    $dict['accessCount'] = $this->accessCount;
    $dict['permissions'] = $this->permissions;
    $dict['userId'] = $this->userId;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "apiKeyId";
  }
  
  function getPrimaryKeyValue() {
    return $this->apiKeyId;
  }
  
  function getId() {
    return $this->apiKeyId;
  }
  
  function setId($id) {
    $this->apiKeyId = $id;
  }
  
  function getStartValid(){
    return $this->startValid;
  }
  
  function setStartValid($startValid){
    $this->startValid = $startValid;
  }
  
  function getEndValid(){
    return $this->endValid;
  }
  
  function setEndValid($endValid){
    $this->endValid = $endValid;
  }
  
  function getAccessKey(){
    return $this->accessKey;
  }
  
  function setAccessKey($accessKey){
    $this->accessKey = $accessKey;
  }
  
  function getAccessCount(){
    return $this->accessCount;
  }
  
  function setAccessCount($accessCount){
    $this->accessCount = $accessCount;
  }
  
  function getPermissions(){
    return $this->permissions;
  }
  
  function setPermissions($permissions){
    $this->permissions = $permissions;
  }
  
  function getUserId(){
    return $this->userId;
  }
  
  function setUserId($userId){
    $this->userId = $userId;
  }

  const API_KEY_ID = "apiKeyId";
  const START_VALID = "startValid";
  const END_VALID = "endValid";
  const ACCESS_KEY = "accessKey";
  const ACCESS_COUNT = "accessCount";
  const PERMISSIONS = "permissions";
  const USER_ID = "userId";
}
