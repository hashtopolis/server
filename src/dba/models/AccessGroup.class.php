<?php

namespace DBA;

class AccessGroup extends AbstractModel {
  private ?int $accessGroupId;
  private ?string $groupName;
  
  function __construct(?int $accessGroupId, ?string $groupName) {
    $this->accessGroupId = $accessGroupId;
    $this->groupName = $groupName;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['accessGroupId'] = $this->accessGroupId;
    $dict['groupName'] = $this->groupName;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['accessGroupId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "accessGroupId", "public" => False];
    $dict['groupName'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "groupName", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "accessGroupId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->accessGroupId;
  }
  
  function getId(): ?int {
    return $this->accessGroupId;
  }
  
  function setId($id): void {
    $this->accessGroupId = $id;
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
  
  const ACCESS_GROUP_ID = "accessGroupId";
  const GROUP_NAME = "groupName";

  const PERM_CREATE = "permAccessGroupCreate";
  const PERM_READ = "permAccessGroupRead";
  const PERM_UPDATE = "permAccessGroupUpdate";
  const PERM_DELETE = "permAccessGroupDelete";
}
