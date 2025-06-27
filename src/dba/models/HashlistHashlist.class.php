<?php

namespace DBA;

class HashlistHashlist extends AbstractModel {
  private ?int $hashlistHashlistId;
  private ?int $parentHashlistId;
  private ?int $hashlistId;
  
  function __construct(?int $hashlistHashlistId, ?int $parentHashlistId, ?int $hashlistId) {
    $this->hashlistHashlistId = $hashlistHashlistId;
    $this->parentHashlistId = $parentHashlistId;
    $this->hashlistId = $hashlistId;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['hashlistHashlistId'] = $this->hashlistHashlistId;
    $dict['parentHashlistId'] = $this->parentHashlistId;
    $dict['hashlistId'] = $this->hashlistId;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['hashlistHashlistId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "hashlistHashlistId", "public" => False];
    $dict['parentHashlistId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "parentHashlistId", "public" => False];
    $dict['hashlistId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hashlistId", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "hashlistHashlistId";
  }
  
  function getPrimaryKeyValue(): int {
    return $this->hashlistHashlistId;
  }
  
  function getId(): int {
    return $this->hashlistHashlistId;
  }
  
  function setId($id): void {
    $this->hashlistHashlistId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getParentHashlistId(): ?int {
    return $this->parentHashlistId;
  }
  
  function setParentHashlistId(?int $parentHashlistId): void {
    $this->parentHashlistId = $parentHashlistId;
  }
  
  function getHashlistId(): ?int {
    return $this->hashlistId;
  }
  
  function setHashlistId(?int $hashlistId): void {
    $this->hashlistId = $hashlistId;
  }
  
  const HASHLIST_HASHLIST_ID = "hashlistHashlistId";
  const PARENT_HASHLIST_ID = "parentHashlistId";
  const HASHLIST_ID = "hashlistId";

  const PERM_CREATE = "permHashlistHashlistCreate";
  const PERM_READ = "permHashlistHashlistRead";
  const PERM_UPDATE = "permHashlistHashlistUpdate";
  const PERM_DELETE = "permHashlistHashlistDelete";
}
