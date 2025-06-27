<?php

namespace DBA;

class AgentStat extends AbstractModel {
  private ?int $agentStatId;
  private ?int $agentId;
  private ?int $statType;
  private ?int $time;
  private ?string $value;
  
  function __construct(?int $agentStatId, ?int $agentId, ?int $statType, ?int $time, ?string $value) {
    $this->agentStatId = $agentStatId;
    $this->agentId = $agentId;
    $this->statType = $statType;
    $this->time = $time;
    $this->value = $value;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['agentStatId'] = $this->agentStatId;
    $dict['agentId'] = $this->agentId;
    $dict['statType'] = $this->statType;
    $dict['time'] = $this->time;
    $dict['value'] = $this->value;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['agentStatId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "agentStatId", "public" => False];
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "agentId", "public" => False];
    $dict['statType'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "statType", "public" => False];
    $dict['time'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "time", "public" => False];
    $dict['value'] = ['read_only' => True, "type" => "array", "subtype" => "int", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "value", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "agentStatId";
  }
  
  function getPrimaryKeyValue(): int {
    return $this->agentStatId;
  }
  
  function getId(): int {
    return $this->agentStatId;
  }
  
  function setId($id): void {
    $this->agentStatId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getAgentId(): ?int {
    return $this->agentId;
  }
  
  function setAgentId(?int $agentId): void {
    $this->agentId = $agentId;
  }
  
  function getStatType(): ?int {
    return $this->statType;
  }
  
  function setStatType(?int $statType): void {
    $this->statType = $statType;
  }
  
  function getTime(): ?int {
    return $this->time;
  }
  
  function setTime(?int $time): void {
    $this->time = $time;
  }
  
  function getValue(): ?string {
    return $this->value;
  }
  
  function setValue(?string $value): void {
    $this->value = $value;
  }
  
  const AGENT_STAT_ID = "agentStatId";
  const AGENT_ID = "agentId";
  const STAT_TYPE = "statType";
  const TIME = "time";
  const VALUE = "value";

  const PERM_CREATE = "permAgentStatCreate";
  const PERM_READ = "permAgentStatRead";
  const PERM_UPDATE = "permAgentStatUpdate";
  const PERM_DELETE = "permAgentStatDelete";
}
