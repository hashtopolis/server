<?php

namespace DBA;

class StoredValue extends AbstractModel {
  private ?string $storedValueId;
  private ?string $val;
  
  function __construct(?string $storedValueId, ?string $val) {
    $this->storedValueId = $storedValueId;
    $this->val = $val;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['storedValueId'] = $this->storedValueId;
    $dict['val'] = $this->val;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['storedValueId'] = ['read_only' => True, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "storedValueId", "public" => False];
    $dict['val'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "val", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "storedValueId";
  }
  
  function getPrimaryKeyValue(): ?string {
    return $this->storedValueId;
  }
  
  function getId(): ?string {
    return $this->storedValueId;
  }
  
  function setId($id): void {
    $this->storedValueId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getVal(): ?string {
    return $this->val;
  }
  
  function setVal(?string $val): void {
    $this->val = $val;
  }
  
  const STORED_VALUE_ID = "storedValueId";
  const VAL = "val";

  const PERM_CREATE = "permStoredValueCreate";
  const PERM_READ = "permStoredValueRead";
  const PERM_UPDATE = "permStoredValueUpdate";
  const PERM_DELETE = "permStoredValueDelete";
}
