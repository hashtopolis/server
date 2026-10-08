<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class Supertask extends AbstractModel {
  private ?int $supertaskId;
  private ?string $supertaskName;
  private ?int $crackerBinaryTypeId;
  
  function __construct(?int $supertaskId, ?string $supertaskName, ?int $crackerBinaryTypeId) {
    $this->supertaskId = $supertaskId;
    $this->supertaskName = $supertaskName;
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['supertaskId'] = $this->supertaskId;
    $dict['supertaskName'] = $this->supertaskName;
    $dict['crackerBinaryTypeId'] = $this->crackerBinaryTypeId;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['supertaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "supertaskId", "public" => False, "dba_mapping" => False];
    $dict['supertaskName'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "supertaskName", "public" => False, "dba_mapping" => False];
    $dict['crackerBinaryTypeId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "crackerBinaryTypeId", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "supertaskId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->supertaskId;
  }
  
  function getId(): ?int {
    return $this->supertaskId;
  }
  
  function setId($id): void {
    $this->supertaskId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getSupertaskName(): ?string {
    return $this->supertaskName;
  }
  
  function setSupertaskName(?string $supertaskName): void {
    $this->supertaskName = $supertaskName;
  }
  
  function getCrackerBinaryTypeId(): ?int {
    return $this->crackerBinaryTypeId;
  }
  
  function setCrackerBinaryTypeId(?int $crackerBinaryTypeId): void {
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
  }
  
  const SUPERTASK_ID = "supertaskId";
  const SUPERTASK_NAME = "supertaskName";
  const CRACKER_BINARY_TYPE_ID = "crackerBinaryTypeId";

  const PERM_CREATE = "permSupertaskCreate";
  const PERM_READ = "permSupertaskRead";
  const PERM_UPDATE = "permSupertaskUpdate";
  const PERM_DELETE = "permSupertaskDelete";
}
