<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class AgentZap extends AbstractModel {
  private $agentId;
  private $lastZapId;
  
  function __construct($agentId, $lastZapId) {
    $this->agentId = $agentId;
    $this->lastZapId = $lastZapId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['agentId'] = $this->agentId;
    $dict['lastZapId'] = $this->lastZapId;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "agentId";
  }
  
  function getPrimaryKeyValue() {
    return $this->agentId;
  }
  
  function getId() {
    return $this->agentId;
  }
  
  function setId($id) {
    $this->agentId = $id;
  }
  
  function getLastZapId(){
    return $this->lastZapId;
  }
  
  function setLastZapId($lastZapId){
    $this->lastZapId = $lastZapId;
  }

  const AGENT_ID = "agentId";
  const LAST_ZAP_ID = "lastZapId";
}
