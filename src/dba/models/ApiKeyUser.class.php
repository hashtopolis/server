<?php

namespace DBA;

class ApiKeyUser extends AbstractModel {
  private $apiKeyUserId;
  private $apiKeyId;
  private $userId;
  
  function __construct($apiKeyUserId, $apiKeyId, $userId) {
    $this->apiKeyUserId = $apiKeyUserId;
    $this->apiKeyId = $apiKeyId;
    $this->userId = $userId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['apiKeyUserId'] = $this->apiKeyUserId;
    $dict['apiKeyId'] = $this->apiKeyId;
    $dict['userId'] = $this->userId;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "apiKeyUserId";
  }
  
  function getPrimaryKeyValue() {
    return $this->apiKeyUserId;
  }
  
  function getId() {
    return $this->apiKeyUserId;
  }
  
  function setId($id) {
    $this->apiKeyUserId = $id;
  }
  
  function getApiKeyId(){
    return $this->apiKeyId;
  }
  
  function setApiKeyId($apiKeyId){
    $this->apiKeyId = $apiKeyId;
  }
  
  function getUserId(){
    return $this->userId;
  }
  
  function setUserId($userId){
    $this->userId = $userId;
  }

  const API_KEY_USER_ID = "apiKeyUserId";
  const API_KEY_ID = "apiKeyId";
  const USER_ID = "userId";
}
