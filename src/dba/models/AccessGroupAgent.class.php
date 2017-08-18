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
  
  function getAccessGroupId(){
    return $this->accessGroupId;
  }
  
  function setAccessGroupId($accessGroupId){
    $this->accessGroupId = $accessGroupId;
  }
  
  function getAgentId(){
    return $this->agentId;
  }
  
  function setAgentId($agentId){
    $this->agentId = $agentId;
  }

  const ACCESS_GROUP_AGENT_ID = "accessGroupAgentId";
  const ACCESS_GROUP_ID = "accessGroupId";
  const AGENT_ID = "agentId";
}
