<?php

namespace DBA;

class HashType extends AbstractModel {
  private ?int $hashTypeId;
  private ?string $description;
  private ?bool $isSalted;
  private ?bool $isSlowHash;
  
  function __construct(?int $hashTypeId, ?string $description, ?bool $isSalted, ?bool $isSlowHash) {
    $this->hashTypeId = $hashTypeId;
    $this->description = $description;
    $this->isSalted = $isSalted;
    $this->isSlowHash = $isSlowHash;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['hashTypeId'] = $this->hashTypeId;
    $dict['description'] = $this->description;
    $dict['isSalted'] = $this->isSalted;
    $dict['isSlowHash'] = $this->isSlowHash;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['hashTypeId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => False, "private" => False, "alias" => "hashTypeId", "public" => False];
    $dict['description'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "description", "public" => False];
    $dict['isSalted'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isSalted", "public" => False];
    $dict['isSlowHash'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isSlowHash", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "hashTypeId";
  }
  
  function getPrimaryKeyValue(): int {
    return $this->hashTypeId;
  }
  
  function getId(): int {
    return $this->hashTypeId;
  }
  
  function setId($id): void {
    $this->hashTypeId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getDescription(): ?string {
    return $this->description;
  }
  
  function setDescription(?string $description): void {
    $this->description = $description;
  }
  
  function getIsSalted(): ?bool {
    return $this->isSalted;
  }
  
  function setIsSalted(?bool $isSalted): void {
    $this->isSalted = $isSalted;
  }
  
  function getIsSlowHash(): ?bool {
    return $this->isSlowHash;
  }
  
  function setIsSlowHash(?bool $isSlowHash): void {
    $this->isSlowHash = $isSlowHash;
  }
  
  const HASH_TYPE_ID = "hashTypeId";
  const DESCRIPTION = "description";
  const IS_SALTED = "isSalted";
  const IS_SLOW_HASH = "isSlowHash";

  const PERM_CREATE = "permHashTypeCreate";
  const PERM_READ = "permHashTypeRead";
  const PERM_UPDATE = "permHashTypeUpdate";
  const PERM_DELETE = "permHashTypeDelete";
}
