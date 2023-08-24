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
    $dict['configId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "configId"];
    $dict['configSectionId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "configSectionId"];
    $dict['item'] = ['read_only' => False, "type" => "str(128)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "item"];
    $dict['value'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "value"];

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
}
