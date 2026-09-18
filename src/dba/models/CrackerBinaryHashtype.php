<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class CrackerBinaryHashtype extends AbstractModel {
  private ?int $crackerBinaryHashtypeId;
  private ?int $crackerBinaryId;
  private ?int $hashTypeId;
  
  function __construct(?int $crackerBinaryHashtypeId, ?int $crackerBinaryId, ?int $hashTypeId) {
    $this->crackerBinaryHashtypeId = $crackerBinaryHashtypeId;
    $this->crackerBinaryId = $crackerBinaryId;
    $this->hashTypeId = $hashTypeId;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['crackerBinaryHashtypeId'] = $this->crackerBinaryHashtypeId;
    $dict['crackerBinaryId'] = $this->crackerBinaryId;
    $dict['hashTypeId'] = $this->hashTypeId;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['crackerBinaryHashtypeId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "crackerBinaryHashtypeId", "public" => False, "dba_mapping" => False];
    $dict['crackerBinaryId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "crackerBinaryId", "public" => False, "dba_mapping" => False];
    $dict['hashTypeId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hashTypeId", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "crackerBinaryHashtypeId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->crackerBinaryHashtypeId;
  }
  
  function getId(): ?int {
    return $this->crackerBinaryHashtypeId;
  }
  
  function setId($id): void {
    $this->crackerBinaryHashtypeId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getCrackerBinaryId(): ?int {
    return $this->crackerBinaryId;
  }
  
  function setCrackerBinaryId(?int $crackerBinaryId): void {
    $this->crackerBinaryId = $crackerBinaryId;
  }
  
  function getHashTypeId(): ?int {
    return $this->hashTypeId;
  }
  
  function setHashTypeId(?int $hashTypeId): void {
    $this->hashTypeId = $hashTypeId;
  }
  
  const CRACKER_BINARY_HASHTYPE_ID = "crackerBinaryHashtypeId";
  const CRACKER_BINARY_ID = "crackerBinaryId";
  const HASH_TYPE_ID = "hashTypeId";

  const PERM_CREATE = "permCrackerBinaryHashtypeCreate";
  const PERM_READ = "permCrackerBinaryHashtypeRead";
  const PERM_UPDATE = "permCrackerBinaryHashtypeUpdate";
  const PERM_DELETE = "permCrackerBinaryHashtypeDelete";
}
