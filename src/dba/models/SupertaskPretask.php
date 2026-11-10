<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class SupertaskPretask extends AbstractModel {
  private ?int $supertaskPretaskId;
  private ?int $supertaskId;
  private ?int $pretaskId;
  private ?int $priority;
  
  function __construct(?int $supertaskPretaskId, ?int $supertaskId, ?int $pretaskId, ?int $priority) {
    $this->supertaskPretaskId = $supertaskPretaskId;
    $this->supertaskId = $supertaskId;
    $this->pretaskId = $pretaskId;
    $this->priority = $priority;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['supertaskPretaskId'] = $this->supertaskPretaskId;
    $dict['supertaskId'] = $this->supertaskId;
    $dict['pretaskId'] = $this->pretaskId;
    $dict['priority'] = $this->priority;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['supertaskPretaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "supertaskPretaskId", "public" => False, "dba_mapping" => False];
    $dict['supertaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "supertaskId", "public" => False, "dba_mapping" => False];
    $dict['pretaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "pretaskId", "public" => False, "dba_mapping" => False];
    $dict['priority'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "priority", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "supertaskPretaskId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->supertaskPretaskId;
  }
  
  function getId(): ?int {
    return $this->supertaskPretaskId;
  }
  
  function setId($id): void {
    $this->supertaskPretaskId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getSupertaskId(): ?int {
    return $this->supertaskId;
  }
  
  function setSupertaskId(?int $supertaskId): void {
    $this->supertaskId = $supertaskId;
  }
  
  function getPretaskId(): ?int {
    return $this->pretaskId;
  }
  
  function setPretaskId(?int $pretaskId): void {
    $this->pretaskId = $pretaskId;
  }
  
  function getPriority(): ?int {
    return $this->priority;
  }
  
  function setPriority(?int $priority): void {
    $this->priority = $priority;
  }
  
  const SUPERTASK_PRETASK_ID = "supertaskPretaskId";
  const SUPERTASK_ID = "supertaskId";
  const PRETASK_ID = "pretaskId";
  const PRIORITY = "priority";

  const PERM_CREATE = "permSupertaskPretaskCreate";
  const PERM_READ = "permSupertaskPretaskRead";
  const PERM_UPDATE = "permSupertaskPretaskUpdate";
  const PERM_DELETE = "permSupertaskPretaskDelete";
}
