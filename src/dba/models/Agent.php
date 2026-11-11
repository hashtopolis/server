<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

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
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "agentId", "public" => False, "dba_mapping" => False];
    $dict['agentName'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "agentName", "public" => False, "dba_mapping" => False];
    $dict['uid'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "uid", "public" => False, "dba_mapping" => False, "description" => "Identifier the agent reports for the machine it runs on, which the server uses to recognise it again."];
    $dict['os'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => [0 => "Linux", 1 => "Windows", 2 => "macOS", ], "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "os", "public" => False, "dba_mapping" => False, "choice_names" => [0 => "LINUX", 1 => "WINDOWS", 2 => "OSX", ], "description" => "Operating system the agent reported for the machine it runs on."];
    $dict['devices'] = ['read_only' => True, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "devices", "public" => False, "dba_mapping" => False, "description" => "The compute devices the agent last reported, one per line."];
    $dict['cmdPars'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "cmdPars", "public" => False, "dba_mapping" => False, "description" => "Extra cracker arguments added to every task this agent runs."];
    $dict['ignoreErrors'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => [0 => "Deactivate the agent when it reports an error", 1 => "Keep the agent running and record the errors it reports", 2 => "Keep the agent running and discard the errors it reports", ], "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "ignoreErrors", "public" => False, "dba_mapping" => False, "choice_names" => [0 => "NO", 1 => "IGNORE_SAVE", 2 => "IGNORE_NOSAVE", ], "description" => "What the server does when this agent reports an error."];
    $dict['isActive'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isActive", "public" => False, "dba_mapping" => False, "description" => "Whether the agent is allowed to pick up work."];
    $dict['isTrusted'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isTrusted", "public" => False, "dba_mapping" => False, "description" => "Whether the agent may receive secret hashlists and secret files."];
    $dict['token'] = ['read_only' => True, "type" => "str(30)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "token", "public" => False, "dba_mapping" => False, "description" => "The credential the agent authenticates its protocol requests with."];
    $dict['lastAct'] = ['read_only' => True, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lastAct", "public" => False, "dba_mapping" => False, "description" => "The protocol action the agent performed last."];
    $dict['lastTime'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lastTime", "public" => False, "dba_mapping" => False, "description" => "When the agent was last heard from, as a unix timestamp."];
    $dict['lastIp'] = ['read_only' => True, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lastIp", "public" => False, "dba_mapping" => False, "description" => "Address the agent was last seen from."];
    $dict['userId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "userId", "public" => False, "dba_mapping" => False];
    $dict['cpuOnly'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "cpuOnly", "public" => False, "dba_mapping" => False, "description" => "Whether the agent reported no usable GPU, so that it only takes CPU tasks."];
    $dict['clientSignature'] = ['read_only' => True, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "clientSignature", "public" => False, "dba_mapping" => False, "description" => "Name and version the agent software identifies itself with, for example generic-python."];

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
