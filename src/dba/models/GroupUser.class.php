<?php

namespace DBA;

class GroupUser extends AbstractModel {
  private $groupUserId;
  private $groupId;
  private $userId;
  
  function __construct($groupUserId, $groupId, $userId) {
    $this->groupUserId = $groupUserId;
    $this->groupId = $groupId;
    $this->userId = $userId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['groupUserId'] = $this->groupUserId;
    $dict['groupId'] = $this->groupId;
    $dict['userId'] = $this->userId;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "groupUserId";
  }
  
  function getPrimaryKeyValue() {
    return $this->groupUserId;
  }
  
  function getId() {
    return $this->groupUserId;
  }
  
  function setId($id) {
    $this->groupUserId = $id;
  }
  
  function getGroupId(){
    return $this->groupId;
  }
  
  function setGroupId($groupId){
    $this->groupId = $groupId;
  }
  
  function getUserId(){
    return $this->userId;
  }
  
  function setUserId($userId){
    $this->userId = $userId;
  }

  const GROUP_USER_ID = "groupUserId";
  const GROUP_ID = "groupId";
  const USER_ID = "userId";
}
