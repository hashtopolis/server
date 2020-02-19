<?php

namespace DBA;

class AccessGroup extends AbstractModel {
  private $accessGroupId;
  private $groupName;
  
  function __construct($accessGroupId, $groupName) {
    $this->accessGroupId = $accessGroupId;
    $this->groupName = $groupName;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['accessGroupId'] = $this->accessGroupId;
    $dict['groupName'] = $this->groupName;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "accessGroupId";
  }
  
  function getPrimaryKeyValue() {
    return $this->accessGroupId;
  }
  
  function getId() {
    return $this->accessGroupId;
  }
  
  function setId($id) {
    $this->accessGroupId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getGroupName() {
    return $this->groupName;
  }
  
  function setGroupName($groupName) {
    $this->groupName = $groupName;
  }
  
  const ACCESS_GROUP_ID = "accessGroupId";
  const GROUP_NAME = "groupName";
}
