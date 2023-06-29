<?php

namespace DBA;

class AgentZap extends AbstractModel {
  private $agentZapId;
  private $agentId;
  private $lastZapId;
  
  function __construct($agentZapId, $agentId, $lastZapId) {
    $this->agentZapId = $agentZapId;
    $this->agentId = $agentId;
    $this->lastZapId = $lastZapId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['agentZapId'] = $this->agentZapId;
    $dict['agentId'] = $this->agentId;
    $dict['lastZapId'] = $this->lastZapId;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['agentZapId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "agentZapId"];
    $dict['agentId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "agentId"];
    $dict['lastZapId'] = ['read_only' => False, "type" => "str(128)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "lastZapId"];

    return $dict;
  }

  function getPrimaryKey() {
    return "agentZapId";
  }
  
  function getPrimaryKeyValue() {
    return $this->agentZapId;
  }
  
  function getId() {
    return $this->agentZapId;
  }
  
  function setId($id) {
    $this->agentZapId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getAgentId() {
    return $this->agentId;
  }
  
  function setAgentId($agentId) {
    $this->agentId = $agentId;
  }
  
  function getLastZapId() {
    return $this->lastZapId;
  }
  
  function setLastZapId($lastZapId) {
    $this->lastZapId = $lastZapId;
  }
  
  const AGENT_ZAP_ID = "agentZapId";
  const AGENT_ID = "agentId";
  const LAST_ZAP_ID = "lastZapId";

  const PERM_CREATE = "permAgentZapCreate";
  const PERM_READ = "permAgentZapRead";
  const PERM_UPDATE = "permAgentZapUpdate";
  const PERM_DELETE = "permAgentZapDelete";
}
