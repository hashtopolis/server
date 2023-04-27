<?php

namespace DBA;

class Agent extends AbstractModel {
  private $agentId;
  private $agentName;
  private $uid;
  private $os;
  private $hardwareGroupId;
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
  
  function __construct($agentId, $agentName, $uid, $os, $hardwareGroupId, $cmdPars, $ignoreErrors, $isActive, $isTrusted, $token, $lastAct, $lastTime, $lastIp, $userId, $cpuOnly, $clientSignature) {
    $this->agentId = $agentId;
    $this->agentName = $agentName;
    $this->uid = $uid;
    $this->os = $os;
    $this->hardwareGroupId = $hardwareGroupId;
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
    $dict['hardwareGroupId'] = $this->hardwareGroupId;
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
  
  function getHardwareGroupId() {
    return $this->hardwareGroupId;
  }
  
  function setHardwareGroupId($hardwareGroupId) {
    $this->hardwareGroupId = $hardwareGroupId;
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
  const HARDWARE_GROUP_ID = "hardwareGroupId";
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
}
