<?php

namespace DBA;

class RightGroup extends AbstractModel {
  private $rightGroupId;
  private $groupName;
  private $permissions;
  
  function __construct($rightGroupId, $groupName, $permissions) {
    $this->rightGroupId = $rightGroupId;
    $this->groupName = $groupName;
    $this->permissions = $permissions;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['rightGroupId'] = $this->rightGroupId;
    $dict['groupName'] = $this->groupName;
    $dict['permissions'] = $this->permissions;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['rightGroupId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "id"];
    $dict['groupName'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "name"];
    $dict['permissions'] = ['read_only' => False, "type" => "dict", "subtype" => "bool", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "permissions"];

    return $dict;
  }

  function getPrimaryKey() {
    return "rightGroupId";
  }
  
  function getPrimaryKeyValue() {
    return $this->rightGroupId;
  }
  
  function getId() {
    return $this->rightGroupId;
  }
  
  function setId($id) {
    $this->rightGroupId = $id;
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
  
  function getPermissions() {
    return $this->permissions;
  }
  
  function setPermissions($permissions) {
    $this->permissions = $permissions;
  }
  
  const RIGHT_GROUP_ID = "rightGroupId";
  const GROUP_NAME = "groupName";
  const PERMISSIONS = "permissions";

  const PERM_CREATE = "permRightGroupCreate";
  const PERM_READ = "permRightGroupRead";
  const PERM_UPDATE = "permRightGroupUpdate";
  const PERM_DELETE = "permRightGroupDelete";
}
