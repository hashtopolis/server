<?php

namespace DBA;

class AccessGroupAgent extends AbstractModel {
  private $accessGroupAgentId;
  private $accessGroupId;
  private $agentId;
  
  function __construct($accessGroupAgentId, $accessGroupId, $agentId) {
    $this->accessGroupAgentId = $accessGroupAgentId;
    $this->accessGroupId = $accessGroupId;
    $this->agentId = $agentId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['accessGroupAgentId'] = $this->accessGroupAgentId;
    $dict['accessGroupId'] = $this->accessGroupId;
    $dict['agentId'] = $this->agentId;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['accessGroupAgentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "accessGroupAgentId"];
    $dict['accessGroupId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "accessGroupId"];
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "agentId"];

    return $dict;
  }

  function getPrimaryKey() {
    return "accessGroupAgentId";
  }
  
  function getPrimaryKeyValue() {
    return $this->accessGroupAgentId;
  }
  
  function getId() {
    return $this->accessGroupAgentId;
  }
  
  function setId($id) {
    $this->accessGroupAgentId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getAccessGroupId() {
    return $this->accessGroupId;
  }
  
  function setAccessGroupId($accessGroupId) {
    $this->accessGroupId = $accessGroupId;
  }
  
  function getAgentId() {
    return $this->agentId;
  }
  
  function setAgentId($agentId) {
    $this->agentId = $agentId;
  }
  
  const ACCESS_GROUP_AGENT_ID = "accessGroupAgentId";
  const ACCESS_GROUP_ID = "accessGroupId";
  const AGENT_ID = "agentId";

  const PERM_CREATE = "permAccessGroupAgentCreate";
  const PERM_READ = "permAccessGroupAgentRead";
  const PERM_UPDATE = "permAccessGroupAgentUpdate";
  const PERM_DELETE = "permAccessGroupAgentDelete";
}
