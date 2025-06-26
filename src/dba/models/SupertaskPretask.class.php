<?php

namespace DBA;

class SupertaskPretask extends AbstractModel {
  private $supertaskPretaskId;
  private $supertaskId;
  private $pretaskId;
  
  function __construct($supertaskPretaskId, $supertaskId, $pretaskId) {
    $this->supertaskPretaskId = $supertaskPretaskId;
    $this->supertaskId = $supertaskId;
    $this->pretaskId = $pretaskId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['supertaskPretaskId'] = $this->supertaskPretaskId;
    $dict['supertaskId'] = $this->supertaskId;
    $dict['pretaskId'] = $this->pretaskId;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['supertaskPretaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "supertaskPretaskId", "public" => False];
    $dict['supertaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "supertaskId", "public" => False];
    $dict['pretaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "pretaskId", "public" => False];

    return $dict;
  }

  function getPrimaryKey() {
    return "supertaskPretaskId";
  }
  
  function getPrimaryKeyValue() {
    return $this->supertaskPretaskId;
  }
  
  function getId() {
    return $this->supertaskPretaskId;
  }
  
  function setId($id) {
    $this->supertaskPretaskId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getSupertaskId() {
    return $this->supertaskId;
  }
  
  function setSupertaskId($supertaskId) {
    $this->supertaskId = $supertaskId;
  }
  
  function getPretaskId() {
    return $this->pretaskId;
  }
  
  function setPretaskId($pretaskId) {
    $this->pretaskId = $pretaskId;
  }
  
  const SUPERTASK_PRETASK_ID = "supertaskPretaskId";
  const SUPERTASK_ID = "supertaskId";
  const PRETASK_ID = "pretaskId";

  const PERM_CREATE = "permSupertaskPretaskCreate";
  const PERM_READ = "permSupertaskPretaskRead";
  const PERM_UPDATE = "permSupertaskPretaskUpdate";
  const PERM_DELETE = "permSupertaskPretaskDelete";
}
