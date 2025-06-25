<?php

namespace DBA;

class Zap extends AbstractModel {
  private $zapId;
  private $hash;
  private $solveTime;
  private $agentId;
  private $hashlistId;
  
  function __construct($zapId, $hash, $solveTime, $agentId, $hashlistId) {
    $this->zapId = $zapId;
    $this->hash = $hash;
    $this->solveTime = $solveTime;
    $this->agentId = $agentId;
    $this->hashlistId = $hashlistId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['zapId'] = $this->zapId;
    $dict['hash'] = $this->hash;
    $dict['solveTime'] = $this->solveTime;
    $dict['agentId'] = $this->agentId;
    $dict['hashlistId'] = $this->hashlistId;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['zapId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "zapId", "public" => False];
    $dict['hash'] = ['read_only' => True, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "hash", "public" => False];
    $dict['solveTime'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "solveTime", "public" => False];
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "agentId", "public" => False];
    $dict['hashlistId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "hashlistId", "public" => False];

    return $dict;
  }

  function getPrimaryKey() {
    return "zapId";
  }
  
  function getPrimaryKeyValue() {
    return $this->zapId;
  }
  
  function getId() {
    return $this->zapId;
  }
  
  function setId($id) {
    $this->zapId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getHash() {
    return $this->hash;
  }
  
  function setHash($hash) {
    $this->hash = $hash;
  }
  
  function getSolveTime() {
    return $this->solveTime;
  }
  
  function setSolveTime($solveTime) {
    $this->solveTime = $solveTime;
  }
  
  function getAgentId() {
    return $this->agentId;
  }
  
  function setAgentId($agentId) {
    $this->agentId = $agentId;
  }
  
  function getHashlistId() {
    return $this->hashlistId;
  }
  
  function setHashlistId($hashlistId) {
    $this->hashlistId = $hashlistId;
  }
  
  const ZAP_ID = "zapId";
  const HASH = "hash";
  const SOLVE_TIME = "solveTime";
  const AGENT_ID = "agentId";
  const HASHLIST_ID = "hashlistId";

  const PERM_CREATE = "permZapCreate";
  const PERM_READ = "permZapRead";
  const PERM_UPDATE = "permZapUpdate";
  const PERM_DELETE = "permZapDelete";
}
