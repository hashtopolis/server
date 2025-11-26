<?php

namespace DBA;

class Agent extends AbstractModel {
  private ?int $agentId;
  private ?string $agentName;
  private ?string $uid;
  private ?int $os;
  private ?string $devices;
  private ?string $cmdPars;
  private ?int $ignoreErrors;
  private ?int $isActive;
  private ?int $isTrusted;
  private ?string $token;
  private ?string $lastAct;
  private ?int $lastTime;
  private ?string $lastIp;
  private ?int $userId;
  private ?int $cpuOnly;
  private ?string $clientSignature;
  
  function __construct(?int $agentId, ?string $agentName, ?string $uid, ?int $os, ?string $devices, ?string $cmdPars, ?int $ignoreErrors, ?int $isActive, ?int $isTrusted, ?string $token, ?string $lastAct, ?int $lastTime, ?string $lastIp, ?int $userId, ?int $cpuOnly, ?string $clientSignature) {
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
  
  function getKeyValueDict(): array {
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
  
  static function getFeatures(): array {
    $dict = array();
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "agentId", "public" => False];
    $dict['agentName'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "agentName", "public" => False];
    $dict['uid'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "uid", "public" => False];
    $dict['os'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "os", "public" => False];
    $dict['devices'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "devices", "public" => False];
    $dict['cmdPars'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "cmdPars", "public" => False];
    $dict['ignoreErrors'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => [0 => "Deactivate agent on error", 1 => "Keep agent running, but save errors", 2 => "Keep agent running and discard errors", ], "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "ignoreErrors", "public" => False];
    $dict['isActive'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isActive", "public" => False];
    $dict['isTrusted'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isTrusted", "public" => False];
    $dict['token'] = ['read_only' => False, "type" => "str(30)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "token", "public" => False];
    $dict['lastAct'] = ['read_only' => True, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lastAct", "public" => False];
    $dict['lastTime'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lastTime", "public" => False];
    $dict['lastIp'] = ['read_only' => True, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lastIp", "public" => False];
    $dict['userId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "userId", "public" => False];
    $dict['cpuOnly'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "cpuOnly", "public" => False];
    $dict['clientSignature'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "clientSignature", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "agentId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->agentId;
  }
  
  function getId(): ?int {
    return $this->agentId;
  }
  
  function setId($id): void {
    $this->agentId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getAgentName(): ?string {
    return $this->agentName;
  }
  
  function setAgentName(?string $agentName): void {
    $this->agentName = $agentName;
  }
  
  function getUid(): ?string {
    return $this->uid;
  }
  
  function setUid(?string $uid): void {
    $this->uid = $uid;
  }
  
  function getOs(): ?int {
    return $this->os;
  }
  
  function setOs(?int $os): void {
    $this->os = $os;
  }
  
  function getDevices(): ?string {
    return $this->devices;
  }
  
  function setDevices(?string $devices): void {
    $this->devices = $devices;
  }
  
  function getCmdPars(): ?string {
    return $this->cmdPars;
  }
  
  function setCmdPars(?string $cmdPars): void {
    $this->cmdPars = $cmdPars;
  }
  
  function getIgnoreErrors(): ?int {
    return $this->ignoreErrors;
  }
  
  function setIgnoreErrors(?int $ignoreErrors): void {
    $this->ignoreErrors = $ignoreErrors;
  }
  
  function getIsActive(): ?int {
    return $this->isActive;
  }
  
  function setIsActive(?int $isActive): void {
    $this->isActive = $isActive;
  }
  
  function getIsTrusted(): ?int {
    return $this->isTrusted;
  }
  
  function setIsTrusted(?int $isTrusted): void {
    $this->isTrusted = $isTrusted;
  }
  
  function getToken(): ?string {
    return $this->token;
  }
  
  function setToken(?string $token): void {
    $this->token = $token;
  }
  
  function getLastAct(): ?string {
    return $this->lastAct;
  }
  
  function setLastAct(?string $lastAct): void {
    $this->lastAct = $lastAct;
  }
  
  function getLastTime(): ?int {
    return $this->lastTime;
  }
  
  function setLastTime(?int $lastTime): void {
    $this->lastTime = $lastTime;
  }
  
  function getLastIp(): ?string {
    return $this->lastIp;
  }
  
  function setLastIp(?string $lastIp): void {
    $this->lastIp = $lastIp;
  }
  
  function getUserId(): ?int {
    return $this->userId;
  }
  
  function setUserId(?int $userId): void {
    $this->userId = $userId;
  }
  
  function getCpuOnly(): ?int {
    return $this->cpuOnly;
  }
  
  function setCpuOnly(?int $cpuOnly): void {
    $this->cpuOnly = $cpuOnly;
  }
  
  function getClientSignature(): ?string {
    return $this->clientSignature;
  }
  
  function setClientSignature(?string $clientSignature): void {
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
