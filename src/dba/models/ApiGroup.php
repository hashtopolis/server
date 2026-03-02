<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class ApiGroup extends AbstractModel {
  private ?int $apiGroupId;
  private ?string $permissions;
  private ?string $name;
  
  function __construct(?int $apiGroupId, ?string $permissions, ?string $name) {
    $this->apiGroupId = $apiGroupId;
    $this->permissions = $permissions;
    $this->name = $name;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['apiGroupId'] = $this->apiGroupId;
    $dict['permissions'] = $this->permissions;
    $dict['name'] = $this->name;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['apiGroupId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "apiGroupId", "public" => False, "dba_mapping" => False];
    $dict['permissions'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "permissions", "public" => False, "dba_mapping" => False];
    $dict['name'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "name", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "apiGroupId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->apiGroupId;
  }
  
  function getId(): ?int {
    return $this->apiGroupId;
  }
  
  function setId($id): void {
    $this->apiGroupId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getPermissions(): ?string {
    return $this->permissions;
  }
  
  function setPermissions(?string $permissions): void {
    $this->permissions = $permissions;
  }
  
  function getName(): ?string {
    return $this->name;
  }
  
  function setName(?string $name): void {
    $this->name = $name;
  }
  
  const API_GROUP_ID = "apiGroupId";
  const PERMISSIONS = "permissions";
  const NAME = "name";

  const PERM_CREATE = "permApiGroupCreate";
  const PERM_READ = "permApiGroupRead";
  const PERM_UPDATE = "permApiGroupUpdate";
  const PERM_DELETE = "permApiGroupDelete";
}
