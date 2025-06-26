<?php

namespace DBA;

class Config extends AbstractModel {
  private $configId;
  private $configSectionId;
  private $item;
  private $value;
  
  function __construct($configId, $configSectionId, $item, $value) {
    $this->configId = $configId;
    $this->configSectionId = $configSectionId;
    $this->item = $item;
    $this->value = $value;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['configId'] = $this->configId;
    $dict['configSectionId'] = $this->configSectionId;
    $dict['item'] = $this->item;
    $dict['value'] = $this->value;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['configId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "configId", "public" => False];
    $dict['configSectionId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "configSectionId", "public" => False];
    $dict['item'] = ['read_only' => False, "type" => "str(128)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "item", "public" => False];
    $dict['value'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "value", "public" => False];

    return $dict;
  }

  function getPrimaryKey() {
    return "configId";
  }
  
  function getPrimaryKeyValue() {
    return $this->configId;
  }
  
  function getId() {
    return $this->configId;
  }
  
  function setId($id) {
    $this->configId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getConfigSectionId() {
    return $this->configSectionId;
  }
  
  function setConfigSectionId($configSectionId) {
    $this->configSectionId = $configSectionId;
  }
  
  function getItem() {
    return $this->item;
  }
  
  function setItem($item) {
    $this->item = $item;
  }
  
  function getValue() {
    return $this->value;
  }
  
  function setValue($value) {
    $this->value = $value;
  }
  
  const CONFIG_ID = "configId";
  const CONFIG_SECTION_ID = "configSectionId";
  const ITEM = "item";
  const VALUE = "value";

  const PERM_CREATE = "permConfigCreate";
  const PERM_READ = "permConfigRead";
  const PERM_UPDATE = "permConfigUpdate";
  const PERM_DELETE = "permConfigDelete";
}
