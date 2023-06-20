<?php

namespace DBA;

class AgentBinary extends AbstractModel {
  private $agentBinaryId;
  private $type;
  private $version;
  private $operatingSystems;
  private $filename;
  private $updateTrack;
  private $updateAvailable;
  
  function __construct($agentBinaryId, $type, $version, $operatingSystems, $filename, $updateTrack, $updateAvailable) {
    $this->agentBinaryId = $agentBinaryId;
    $this->type = $type;
    $this->version = $version;
    $this->operatingSystems = $operatingSystems;
    $this->filename = $filename;
    $this->updateTrack = $updateTrack;
    $this->updateAvailable = $updateAvailable;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['agentBinaryId'] = $this->agentBinaryId;
    $dict['type'] = $this->type;
    $dict['version'] = $this->version;
    $dict['operatingSystems'] = $this->operatingSystems;
    $dict['filename'] = $this->filename;
    $dict['updateTrack'] = $this->updateTrack;
    $dict['updateAvailable'] = $this->updateAvailable;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['agentBinaryId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "agentBinaryId"];
    $dict['type'] = ['read_only' => False, "type" => "str(20)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "type"];
    $dict['version'] = ['read_only' => False, "type" => "str(20)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "version"];
    $dict['operatingSystems'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "operatingSystems"];
    $dict['filename'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "filename"];
    $dict['updateTrack'] = ['read_only' => False, "type" => "str(20)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "updateTrack"];
    $dict['updateAvailable'] = ['read_only' => True, "type" => "str(20)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "updateAvailable"];

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
  
  function getType() {
    return $this->type;
  }
  
  function setType($type) {
    $this->type = $type;
  }
  
  function getVersion() {
    return $this->version;
  }
  
  function setVersion($version) {
    $this->version = $version;
  }
  
  function getOperatingSystems() {
    return $this->operatingSystems;
  }
  
  function setOperatingSystems($operatingSystems) {
    $this->operatingSystems = $operatingSystems;
  }
  
  function getFilename() {
    return $this->filename;
  }
  
  function setFilename($filename) {
    $this->filename = $filename;
  }
  
  function getUpdateTrack() {
    return $this->updateTrack;
  }
  
  function setUpdateTrack($updateTrack) {
    $this->updateTrack = $updateTrack;
  }
  
  function getUpdateAvailable() {
    return $this->updateAvailable;
  }
  
  function setUpdateAvailable($updateAvailable) {
    $this->updateAvailable = $updateAvailable;
  }
  
  const AGENT_BINARY_ID = "agentBinaryId";
  const TYPE = "type";
  const VERSION = "version";
  const OPERATING_SYSTEMS = "operatingSystems";
  const FILENAME = "filename";
  const UPDATE_TRACK = "updateTrack";
  const UPDATE_AVAILABLE = "updateAvailable";

  const PERM_CREATE = "permAgentBinaryCreate";
  const PERM_READ = "permAgentBinaryRead";
  const PERM_UPDATE = "permAgentBinaryUpdate";
  const PERM_DELETE = "permAgentBinaryDelete";
}
