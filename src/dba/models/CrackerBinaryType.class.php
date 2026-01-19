<?php

namespace DBA;

class CrackerBinaryType extends AbstractModel {
  private ?int $crackerBinaryTypeId;
  private ?string $typeName;
  private ?int $isChunkingAvailable;
  
  function __construct(?int $crackerBinaryTypeId, ?string $typeName, ?int $isChunkingAvailable) {
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
    $this->typeName = $typeName;
    $this->isChunkingAvailable = $isChunkingAvailable;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['crackerBinaryTypeId'] = $this->crackerBinaryTypeId;
    $dict['typeName'] = $this->typeName;
    $dict['isChunkingAvailable'] = $this->isChunkingAvailable;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['crackerBinaryTypeId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "crackerBinaryTypeId", "public" => False, "dba_mapping" => False];
    $dict['typeName'] = ['read_only' => False, "type" => "str(30)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "typeName", "public" => False, "dba_mapping" => False];
    $dict['isChunkingAvailable'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isChunkingAvailable", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "crackerBinaryTypeId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->crackerBinaryTypeId;
  }
  
  function getId(): ?int {
    return $this->crackerBinaryTypeId;
  }
  
  function setId($id): void {
    $this->crackerBinaryTypeId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getTypeName(): ?string {
    return $this->typeName;
  }
  
  function setTypeName(?string $typeName): void {
    $this->typeName = $typeName;
  }
  
  function getIsChunkingAvailable(): ?int {
    return $this->isChunkingAvailable;
  }
  
  function setIsChunkingAvailable(?int $isChunkingAvailable): void {
    $this->isChunkingAvailable = $isChunkingAvailable;
  }
  
  const CRACKER_BINARY_TYPE_ID = "crackerBinaryTypeId";
  const TYPE_NAME = "typeName";
  const IS_CHUNKING_AVAILABLE = "isChunkingAvailable";

  const PERM_CREATE = "permCrackerBinaryTypeCreate";
  const PERM_READ = "permCrackerBinaryTypeRead";
  const PERM_UPDATE = "permCrackerBinaryTypeUpdate";
  const PERM_DELETE = "permCrackerBinaryTypeDelete";
}
