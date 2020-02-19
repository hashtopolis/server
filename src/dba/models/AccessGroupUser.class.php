<?php

namespace DBA;

class AccessGroupUser extends AbstractModel {
  private $accessGroupUserId;
  private $accessGroupId;
  private $userId;
  
  function __construct($accessGroupUserId, $accessGroupId, $userId) {
    $this->accessGroupUserId = $accessGroupUserId;
    $this->accessGroupId = $accessGroupId;
    $this->userId = $userId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['accessGroupUserId'] = $this->accessGroupUserId;
    $dict['accessGroupId'] = $this->accessGroupId;
    $dict['userId'] = $this->userId;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "accessGroupUserId";
  }
  
  function getPrimaryKeyValue() {
    return $this->accessGroupUserId;
  }
  
  function getId() {
    return $this->accessGroupUserId;
  }
  
  function setId($id) {
    $this->accessGroupUserId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getAccessGroupId() {
    return $this->accessGroupId;
  }
  
  function setAccessGroupId($accessGroupId) {
    $this->accessGroupId = $accessGroupId;
  }
  
  function getUserId() {
    return $this->userId;
  }
  
  function setUserId($userId) {
    $this->userId = $userId;
  }
  
  const ACCESS_GROUP_USER_ID = "accessGroupUserId";
  const ACCESS_GROUP_ID = "accessGroupId";
  const USER_ID = "userId";
}
