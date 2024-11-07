<?php

namespace DBA;

class Agent extends AbstractModel {
  private $agentId;
  private $agentName;
  private $uid;
  private $os;
  private $devices;
  private $cmdPars;
  private $ignoreErrors;
  private $isActive;
  private $isTrusted;
  private $token;
  private $lastAct;
  private $lastTime;
  private $lastIp;
  private $userId;
  private $cpuOnly;
  private $clientSignature;
  
  function __construct($agentId, $agentName, $uid, $os, $devices, $cmdPars, $ignoreErrors, $isActive, $isTrusted, $token, $lastAct, $lastTime, $lastIp, $userId, $cpuOnly, $clientSignature) {
    $this->agentId = $agentId;
    $this->agentName = $agentName;
    $this->uid = $uid;
    $this->os = $os;
    $this->devices = $devices;
    $this->cmdPars = $cmdPars;
    $this->ignoreErrors = $ignoreErrors;
    $this->isActive = $isActive;
    $this->isTrusted = $isTrusted;
    $this->token = $token;
    $this->lastAct = $lastAct;
    $this->lastTime = $lastTime;
    $this->lastIp = $lastIp;
    $this->userId = $userId;
    $this->cpuOnly = $cpuOnly;
    $this->clientSignature = $clientSignature;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['agentId'] = $this->agentId;
    $dict['agentName'] = $this->agentName;
    $dict['uid'] = $this->uid;
    $dict['os'] = $this->os;
    $dict['devices'] = $this->devices;
    $dict['cmdPars'] = $this->cmdPars;
    $dict['ignoreErrors'] = $this->ignoreErrors;
    $dict['isActive'] = $this->isActive;
    $dict['isTrusted'] = $this->isTrusted;
    $dict['token'] = $this->token;
    $dict['lastAct'] = $this->lastAct;
    $dict['lastTime'] = $this->lastTime;
    $dict['lastIp'] = $this->lastIp;
    $dict['userId'] = $this->userId;
    $dict['cpuOnly'] = $this->cpuOnly;
    $dict['clientSignature'] = $this->clientSignature;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "agentId"];
    $dict['agentName'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "agentName"];
    $dict['uid'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "uid"];
    $dict['os'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "os"];
    $dict['devices'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "devices"];
    $dict['cmdPars'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "cmdPars"];
    $dict['ignoreErrors'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => [0 => "Deactivate agent on error", 1 => "Keep agent running, but save errors", 2 => "Keep agent running and discard errors", ], "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "ignoreErrors"];
    $dict['isActive'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isActive"];
    $dict['isTrusted'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isTrusted"];
    $dict['token'] = ['read_only' => False, "type" => "str(30)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "token"];
    $dict['lastAct'] = ['read_only' => True, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lastAct"];
    $dict['lastTime'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lastTime"];
    $dict['lastIp'] = ['read_only' => True, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lastIp"];
    $dict['userId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "userId"];
    $dict['cpuOnly'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "cpuOnly"];
    $dict['clientSignature'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "clientSignature"];

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
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getAgentName() {
    return $this->agentName;
  }
  
  function setAgentName($agentName) {
    $this->agentName = $agentName;
  }
  
  function getUid() {
    return $this->uid;
  }
  
  function setUid($uid) {
    $this->uid = $uid;
  }
  
  function getOs() {
    return $this->os;
  }
  
  function setOs($os) {
    $this->os = $os;
  }
  
  function getDevices() {
    return $this->devices;
  }
  
  function setDevices($devices) {
    $this->devices = $devices;
  }
  
  function getCmdPars() {
    return $this->cmdPars;
  }
  
  function setCmdPars($cmdPars) {
    $this->cmdPars = $cmdPars;
  }
  
  function getIgnoreErrors() {
    return $this->ignoreErrors;
  }
  
  function setIgnoreErrors($ignoreErrors) {
    $this->ignoreErrors = $ignoreErrors;
  }
  
  function getIsActive() {
    return $this->isActive;
  }
  
  function setIsActive($isActive) {
    $this->isActive = $isActive;
  }
  
  function getIsTrusted() {
    return $this->isTrusted;
  }
  
  function setIsTrusted($isTrusted) {
    $this->isTrusted = $isTrusted;
  }
  
  function getToken() {
    return $this->token;
  }
  
  function setToken($token) {
    $this->token = $token;
  }
  
  function getLastAct() {
    return $this->lastAct;
  }
  
  function setLastAct($lastAct) {
    $this->lastAct = $lastAct;
  }
  
  function getLastTime() {
    return $this->lastTime;
  }
  
  function setLastTime($lastTime) {
    $this->lastTime = $lastTime;
  }
  
  function getLastIp() {
    return $this->lastIp;
  }
  
  function setLastIp($lastIp) {
    $this->lastIp = $lastIp;
  }
  
  function getUserId() {
    return $this->userId;
  }
  
  function setUserId($userId) {
    $this->userId = $userId;
  }
  
  function getCpuOnly() {
    return $this->cpuOnly;
  }
  
  function setCpuOnly($cpuOnly) {
    $this->cpuOnly = $cpuOnly;
  }
  
  function getClientSignature() {
    return $this->clientSignature;
  }
  
  function setClientSignature($clientSignature) {
    $this->clientSignature = $clientSignature;
  }
  
  const AGENT_ID = "agentId";
  const AGENT_NAME = "agentName";
  const UID = "uid";
  const OS = "os";
  const DEVICES = "devices";
  const CMD_PARS = "cmdPars";
  const IGNORE_ERRORS = "ignoreErrors";
  const IS_ACTIVE = "isActive";
  const IS_TRUSTED = "isTrusted";
  const TOKEN = "token";
  const LAST_ACT = "lastAct";
  const LAST_TIME = "lastTime";
  const LAST_IP = "lastIp";
  const USER_ID = "userId";
  const CPU_ONLY = "cpuOnly";
  const CLIENT_SIGNATURE = "clientSignature";

  const PERM_CREATE = "permAgentCreate";
  const PERM_READ = "permAgentRead";
  const PERM_UPDATE = "permAgentUpdate";
  const PERM_DELETE = "permAgentDelete";
}
