<?php

namespace DBA;

class ApiKey extends AbstractModel {
  private $apiKeyId;
  private $startValid;
  private $endValid;
  private $accessKey;
  private $accessCount;
  private $userId;
  private $apiGroupId;
  
  function __construct($apiKeyId, $startValid, $endValid, $accessKey, $accessCount, $userId, $apiGroupId) {
    $this->apiKeyId = $apiKeyId;
    $this->startValid = $startValid;
    $this->endValid = $endValid;
    $this->accessKey = $accessKey;
    $this->accessCount = $accessCount;
    $this->userId = $userId;
    $this->apiGroupId = $apiGroupId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['apiKeyId'] = $this->apiKeyId;
    $dict['startValid'] = $this->startValid;
    $dict['endValid'] = $this->endValid;
    $dict['accessKey'] = $this->accessKey;
    $dict['accessCount'] = $this->accessCount;
    $dict['userId'] = $this->userId;
    $dict['apiGroupId'] = $this->apiGroupId;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['apiKeyId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "apiKeyId"];
    $dict['startValid'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "startValid"];
    $dict['endValid'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "endValid"];
    $dict['accessKey'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "accessKey"];
    $dict['accessCount'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "accessCount"];
    $dict['userId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "userId"];
    $dict['apiGroupId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "apiGroupId"];

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
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getStartValid() {
    return $this->startValid;
  }
  
  function setStartValid($startValid) {
    $this->startValid = $startValid;
  }
  
  function getEndValid() {
    return $this->endValid;
  }
  
  function setEndValid($endValid) {
    $this->endValid = $endValid;
  }
  
  function getAccessKey() {
    return $this->accessKey;
  }
  
  function setAccessKey($accessKey) {
    $this->accessKey = $accessKey;
  }
  
  function getAccessCount() {
    return $this->accessCount;
  }
  
  function setAccessCount($accessCount) {
    $this->accessCount = $accessCount;
  }
  
  function getUserId() {
    return $this->userId;
  }
  
  function setUserId($userId) {
    $this->userId = $userId;
  }
  
  function getApiGroupId() {
    return $this->apiGroupId;
  }
  
  function setApiGroupId($apiGroupId) {
    $this->apiGroupId = $apiGroupId;
  }
  
  const API_KEY_ID = "apiKeyId";
  const START_VALID = "startValid";
  const END_VALID = "endValid";
  const ACCESS_KEY = "accessKey";
  const ACCESS_COUNT = "accessCount";
  const USER_ID = "userId";
  const API_GROUP_ID = "apiGroupId";
}
