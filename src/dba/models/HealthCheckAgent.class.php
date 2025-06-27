<?php

namespace DBA;

class HealthCheckAgent extends AbstractModel {
  private ?int $healthCheckAgentId;
  private ?int $healthCheckId;
  private ?int $agentId;
  private ?int $status;
  private ?int $cracked;
  private ?int $numGpus;
  private ?int $start;
  private ?int $end;
  private ?string $errors;
  
  function __construct(?int $healthCheckAgentId, ?int $healthCheckId, ?int $agentId, ?int $status, ?int $cracked, ?int $numGpus, ?int $start, ?int $end, ?string $errors) {
    $this->healthCheckAgentId = $healthCheckAgentId;
    $this->healthCheckId = $healthCheckId;
    $this->agentId = $agentId;
    $this->status = $status;
    $this->cracked = $cracked;
    $this->numGpus = $numGpus;
    $this->start = $start;
    $this->end = $end;
    $this->errors = $errors;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['healthCheckAgentId'] = $this->healthCheckAgentId;
    $dict['healthCheckId'] = $this->healthCheckId;
    $dict['agentId'] = $this->agentId;
    $dict['status'] = $this->status;
    $dict['cracked'] = $this->cracked;
    $dict['numGpus'] = $this->numGpus;
    $dict['start'] = $this->start;
    $dict['end'] = $this->end;
    $dict['errors'] = $this->errors;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['healthCheckAgentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "healthCheckAgentId", "public" => False];
    $dict['healthCheckId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "healthCheckId", "public" => False];
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "agentId", "public" => False];
    $dict['status'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "status", "public" => False];
    $dict['cracked'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "cracked", "public" => False];
    $dict['numGpus'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "numGpus", "public" => False];
    $dict['start'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "start", "public" => False];
    $dict['end'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "end", "public" => False];
    $dict['errors'] = ['read_only' => True, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "errors", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "healthCheckAgentId";
  }
  
  function getPrimaryKeyValue(): int {
    return $this->healthCheckAgentId;
  }
  
  function getId(): int {
    return $this->healthCheckAgentId;
  }
  
  function setId($id): void {
    $this->healthCheckAgentId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getHealthCheckId(): ?int {
    return $this->healthCheckId;
  }
  
  function setHealthCheckId(?int $healthCheckId): void {
    $this->healthCheckId = $healthCheckId;
  }
  
  function getAgentId(): ?int {
    return $this->agentId;
  }
  
  function setAgentId(?int $agentId): void {
    $this->agentId = $agentId;
  }
  
  function getStatus(): ?int {
    return $this->status;
  }
  
  function setStatus(?int $status): void {
    $this->status = $status;
  }
  
  function getCracked(): ?int {
    return $this->cracked;
  }
  
  function setCracked(?int $cracked): void {
    $this->cracked = $cracked;
  }
  
  function getNumGpus(): ?int {
    return $this->numGpus;
  }
  
  function setNumGpus(?int $numGpus): void {
    $this->numGpus = $numGpus;
  }
  
  function getStart(): ?int {
    return $this->start;
  }
  
  function setStart(?int $start): void {
    $this->start = $start;
  }
  
  function getEnd(): ?int {
    return $this->end;
  }
  
  function setEnd(?int $end): void {
    $this->end = $end;
  }
  
  function getErrors(): ?string {
    return $this->errors;
  }
  
  function setErrors(?string $errors): void {
    $this->errors = $errors;
  }
  
  const HEALTH_CHECK_AGENT_ID = "healthCheckAgentId";
  const HEALTH_CHECK_ID = "healthCheckId";
  const AGENT_ID = "agentId";
  const STATUS = "status";
  const CRACKED = "cracked";
  const NUM_GPUS = "numGpus";
  const START = "start";
  const END = "end";
  const ERRORS = "errors";

  const PERM_CREATE = "permHealthCheckAgentCreate";
  const PERM_READ = "permHealthCheckAgentRead";
  const PERM_UPDATE = "permHealthCheckAgentUpdate";
  const PERM_DELETE = "permHealthCheckAgentDelete";
}
