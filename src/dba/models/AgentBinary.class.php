<?php

namespace DBA;

class AgentBinary extends AbstractModel {
  private $agentBinaryId;
  private $type;
  private $version;
  private $operatingSystems;
  private $filename;
  private $updateAvailable;
  
  function __construct($agentBinaryId, $type, $version, $operatingSystems, $filename, $updateAvailable) {
    $this->agentBinaryId = $agentBinaryId;
    $this->type = $type;
    $this->version = $version;
    $this->operatingSystems = $operatingSystems;
    $this->filename = $filename;
    $this->updateAvailable = $updateAvailable;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['agentBinaryId'] = $this->agentBinaryId;
    $dict['type'] = $this->type;
    $dict['version'] = $this->version;
    $dict['operatingSystems'] = $this->operatingSystems;
    $dict['filename'] = $this->filename;
    $dict['updateAvailable'] = $this->updateAvailable;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "agentBinaryId";
  }
  
  function getPrimaryKeyValue() {
    return $this->agentBinaryId;
  }
  
  function getId() {
    return $this->agentBinaryId;
  }
  
  function setId($id) {
    $this->agentBinaryId = $id;
  }

  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }

  function getType(){
    return $this->type;
  }
  
  function setType($type){
    $this->type = $type;
  }
  
  function getVersion(){
    return $this->version;
  }
  
  function setVersion($version){
    $this->version = $version;
  }
  
  function getOperatingSystems(){
    return $this->operatingSystems;
  }
  
  function setOperatingSystems($operatingSystems){
    $this->operatingSystems = $operatingSystems;
  }
  
  function getFilename(){
    return $this->filename;
  }
  
  function setFilename($filename){
    $this->filename = $filename;
  }
  
  function getUpdateAvailable(){
    return $this->updateAvailable;
  }
  
  function setUpdateAvailable($updateAvailable){
    $this->updateAvailable = $updateAvailable;
  }

  const AGENT_BINARY_ID = "agentBinaryId";
  const TYPE = "type";
  const VERSION = "version";
  const OPERATING_SYSTEMS = "operatingSystems";
  const FILENAME = "filename";
  const UPDATE_AVAILABLE = "updateAvailable";
}
