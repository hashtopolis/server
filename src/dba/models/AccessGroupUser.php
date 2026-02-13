<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class AccessGroupUser extends AbstractModel {
  private ?int $accessGroupUserId;
  private ?int $accessGroupId;
  private ?int $userId;
  
  function __construct(?int $accessGroupUserId, ?int $accessGroupId, ?int $userId) {
    $this->accessGroupUserId = $accessGroupUserId;
    $this->accessGroupId = $accessGroupId;
    $this->userId = $userId;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['accessGroupUserId'] = $this->accessGroupUserId;
    $dict['accessGroupId'] = $this->accessGroupId;
    $dict['userId'] = $this->userId;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['accessGroupUserId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "accessGroupUserId", "public" => False, "dba_mapping" => False];
    $dict['accessGroupId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "accessGroupId", "public" => False, "dba_mapping" => False];
    $dict['userId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "userId", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "accessGroupUserId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->accessGroupUserId;
  }
  
  function getId(): ?int {
    return $this->accessGroupUserId;
  }
  
  function setId($id): void {
    $this->accessGroupUserId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getAccessGroupId(): ?int {
    return $this->accessGroupId;
  }
  
  function setAccessGroupId(?int $accessGroupId): void {
    $this->accessGroupId = $accessGroupId;
  }
  
  function getUserId(): ?int {
    return $this->userId;
  }
  
  function setUserId(?int $userId): void {
    $this->userId = $userId;
  }
  
  const ACCESS_GROUP_USER_ID = "accessGroupUserId";
  const ACCESS_GROUP_ID = "accessGroupId";
  const USER_ID = "userId";

  const PERM_CREATE = "permAccessGroupUserCreate";
  const PERM_READ = "permAccessGroupUserRead";
  const PERM_UPDATE = "permAccessGroupUserUpdate";
  const PERM_DELETE = "permAccessGroupUserDelete";
}
