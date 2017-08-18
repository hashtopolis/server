<?php

namespace DBA;

class Group extends AbstractModel {
  private $groupId;
  private $groupName;
  
  function __construct($groupId, $groupName) {
    $this->groupId = $groupId;
    $this->groupName = $groupName;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['groupId'] = $this->groupId;
    $dict['groupName'] = $this->groupName;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "groupId";
  }
  
  function getPrimaryKeyValue() {
    return $this->groupId;
  }
  
  function getId() {
    return $this->groupId;
  }
  
  function setId($id) {
    $this->groupId = $id;
  }
  
  function getGroupName(){
    return $this->groupName;
  }
  
  function setGroupName($groupName){
    $this->groupName = $groupName;
  }

  const GROUP_ID = "groupId";
  const GROUP_NAME = "groupName";
}
