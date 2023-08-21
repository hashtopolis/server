<?php

namespace DBA;

class Supertask extends AbstractModel {
  private $supertaskId;
  private $supertaskName;
  
  function __construct($supertaskId, $supertaskName) {
    $this->supertaskId = $supertaskId;
    $this->supertaskName = $supertaskName;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['supertaskId'] = $this->supertaskId;
    $dict['supertaskName'] = $this->supertaskName;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['supertaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "supertaskId"];
    $dict['supertaskName'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "supertaskName"];

    return $dict;
  }

  function getPrimaryKey() {
    return "supertaskId";
  }
  
  function getPrimaryKeyValue() {
    return $this->supertaskId;
  }
  
  function getId() {
    return $this->supertaskId;
  }
  
  function setId($id) {
    $this->supertaskId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getSupertaskName() {
    return $this->supertaskName;
  }
  
  function setSupertaskName($supertaskName) {
    $this->supertaskName = $supertaskName;
  }
  
  const SUPERTASK_ID = "supertaskId";
  const SUPERTASK_NAME = "supertaskName";

  const PERM_CREATE = "permSupertaskCreate";
  const PERM_READ = "permSupertaskRead";
  const PERM_UPDATE = "permSupertaskUpdate";
  const PERM_DELETE = "permSupertaskDelete";
}
