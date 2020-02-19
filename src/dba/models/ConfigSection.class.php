<?php

namespace DBA;

class ConfigSection extends AbstractModel {
  private $configSectionId;
  private $sectionName;
  
  function __construct($configSectionId, $sectionName) {
    $this->configSectionId = $configSectionId;
    $this->sectionName = $sectionName;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['configSectionId'] = $this->configSectionId;
    $dict['sectionName'] = $this->sectionName;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "configSectionId";
  }
  
  function getPrimaryKeyValue() {
    return $this->configSectionId;
  }
  
  function getId() {
    return $this->configSectionId;
  }
  
  function setId($id) {
    $this->configSectionId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getSectionName() {
    return $this->sectionName;
  }
  
  function setSectionName($sectionName) {
    $this->sectionName = $sectionName;
  }
  
  const CONFIG_SECTION_ID = "configSectionId";
  const SECTION_NAME = "sectionName";
}
