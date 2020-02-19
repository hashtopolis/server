<?php

namespace DBA;

class ApiGroup extends AbstractModel {
  private $apiGroupId;
  private $permissions;
  private $name;
  
  function __construct($apiGroupId, $permissions, $name) {
    $this->apiGroupId = $apiGroupId;
    $this->permissions = $permissions;
    $this->name = $name;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['apiGroupId'] = $this->apiGroupId;
    $dict['permissions'] = $this->permissions;
    $dict['name'] = $this->name;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "apiGroupId";
  }
  
  function getPrimaryKeyValue() {
    return $this->apiGroupId;
  }
  
  function getId() {
    return $this->apiGroupId;
  }
  
  function setId($id) {
    $this->apiGroupId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getPermissions() {
    return $this->permissions;
  }
  
  function setPermissions($permissions) {
    $this->permissions = $permissions;
  }
  
  function getName() {
    return $this->name;
  }
  
  function setName($name) {
    $this->name = $name;
  }
  
  const API_GROUP_ID = "apiGroupId";
  const PERMISSIONS = "permissions";
  const NAME = "name";
}
