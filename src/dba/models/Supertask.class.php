<?php

namespace DBA;

class Supertask extends AbstractModel {
  private ?int $supertaskId;
  private ?string $supertaskName;
  
  function __construct(?int $supertaskId, ?string $supertaskName) {
    $this->supertaskId = $supertaskId;
    $this->supertaskName = $supertaskName;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['supertaskId'] = $this->supertaskId;
    $dict['supertaskName'] = $this->supertaskName;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['supertaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "supertaskId", "public" => False, "dba_mapping" => False];
    $dict['supertaskName'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "supertaskName", "public" => False, "dba_mapping" => False];

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
  
  const SUPERTASK_ID = "supertaskId";
  const SUPERTASK_NAME = "supertaskName";

  const PERM_CREATE = "permSupertaskCreate";
  const PERM_READ = "permSupertaskRead";
  const PERM_UPDATE = "permSupertaskUpdate";
  const PERM_DELETE = "permSupertaskDelete";
}
