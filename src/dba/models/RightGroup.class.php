<?php

namespace DBA;

class RightGroup extends AbstractModel {
  private ?int $rightGroupId;
  private ?string $groupName;
  private ?string $permissions;
  
  function __construct(?int $rightGroupId, ?string $groupName, ?string $permissions) {
    $this->rightGroupId = $rightGroupId;
    $this->groupName = $groupName;
    $this->permissions = $permissions;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['rightGroupId'] = $this->rightGroupId;
    $dict['groupName'] = $this->groupName;
    $dict['permissions'] = $this->permissions;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['rightGroupId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "id", "public" => False];
    $dict['groupName'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "name", "public" => False];
    $dict['permissions'] = ['read_only' => False, "type" => "dict", "subtype" => "bool", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "permissions", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "rightGroupId";
  }
  
  function getPrimaryKeyValue(): int {
    return $this->rightGroupId;
  }
  
  function getId(): int {
    return $this->rightGroupId;
  }
  
  function setId($id): void {
    $this->rightGroupId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getGroupName(): ?string {
    return $this->groupName;
  }
  
  function setGroupName(?string $groupName): void {
    $this->groupName = $groupName;
  }
  
  function getPermissions(): ?string {
    return $this->permissions;
  }
  
  function setPermissions(?string $permissions): void {
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
