<?php

namespace DBA;

class GroupAgent extends AbstractModel {
  private $groupAgentId;
  private $groupId;
  private $agentId;
  
  function __construct($groupAgentId, $groupId, $agentId) {
    $this->groupAgentId = $groupAgentId;
    $this->groupId = $groupId;
    $this->agentId = $agentId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['groupAgentId'] = $this->groupAgentId;
    $dict['groupId'] = $this->groupId;
    $dict['agentId'] = $this->agentId;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "groupAgentId";
  }
  
  function getPrimaryKeyValue() {
    return $this->groupAgentId;
  }
  
  function getId() {
    return $this->groupAgentId;
  }
  
  function setId($id) {
    $this->groupAgentId = $id;
  }
  
  function getGroupId(){
    return $this->groupId;
  }
  
  function setGroupId($groupId){
    $this->groupId = $groupId;
  }
  
  function getAgentId(){
    return $this->agentId;
  }
  
  function setAgentId($agentId){
    $this->agentId = $agentId;
  }

  const GROUP_AGENT_ID = "groupAgentId";
  const GROUP_ID = "groupId";
  const AGENT_ID = "agentId";
}
