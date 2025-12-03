<?php

namespace DBA;

class HealthCheck extends AbstractModel {
  private ?int $healthCheckId;
  private ?int $time;
  private ?int $status;
  private ?int $checkType;
  private ?int $hashtypeId;
  private ?int $crackerBinaryId;
  private ?int $expectedCracks;
  private ?string $attackCmd;
  
  function __construct(?int $healthCheckId, ?int $time, ?int $status, ?int $checkType, ?int $hashtypeId, ?int $crackerBinaryId, ?int $expectedCracks, ?string $attackCmd) {
    $this->healthCheckId = $healthCheckId;
    $this->time = $time;
    $this->status = $status;
    $this->checkType = $checkType;
    $this->hashtypeId = $hashtypeId;
    $this->crackerBinaryId = $crackerBinaryId;
    $this->expectedCracks = $expectedCracks;
    $this->attackCmd = $attackCmd;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['healthCheckId'] = $this->healthCheckId;
    $dict['time'] = $this->time;
    $dict['status'] = $this->status;
    $dict['checkType'] = $this->checkType;
    $dict['hashtypeId'] = $this->hashtypeId;
    $dict['crackerBinaryId'] = $this->crackerBinaryId;
    $dict['expectedCracks'] = $this->expectedCracks;
    $dict['attackCmd'] = $this->attackCmd;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['healthCheckId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "healthCheckId", "public" => False, "dba_mapping" => False];
    $dict['time'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "time", "public" => False, "dba_mapping" => False];
    $dict['status'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "status", "public" => False, "dba_mapping" => False];
    $dict['checkType'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "checkType", "public" => False, "dba_mapping" => False];
    $dict['hashtypeId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hashtypeId", "public" => False, "dba_mapping" => False];
    $dict['crackerBinaryId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "crackerBinaryId", "public" => False, "dba_mapping" => False];
    $dict['expectedCracks'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "expectedCracks", "public" => False, "dba_mapping" => False];
    $dict['attackCmd'] = ['read_only' => True, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "attackCmd", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "healthCheckId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->healthCheckId;
  }
  
  function getId(): ?int {
    return $this->healthCheckId;
  }
  
  function setId($id): void {
    $this->healthCheckId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getTime(): ?int {
    return $this->time;
  }
  
  function setTime(?int $time): void {
    $this->time = $time;
  }
  
  function getStatus(): ?int {
    return $this->status;
  }
  
  function setStatus(?int $status): void {
    $this->status = $status;
  }
  
  function getCheckType(): ?int {
    return $this->checkType;
  }
  
  function setCheckType(?int $checkType): void {
    $this->checkType = $checkType;
  }
  
  function getHashtypeId(): ?int {
    return $this->hashtypeId;
  }
  
  function setHashtypeId(?int $hashtypeId): void {
    $this->hashtypeId = $hashtypeId;
  }
  
  function getCrackerBinaryId(): ?int {
    return $this->crackerBinaryId;
  }
  
  function setCrackerBinaryId(?int $crackerBinaryId): void {
    $this->crackerBinaryId = $crackerBinaryId;
  }
  
  function getExpectedCracks(): ?int {
    return $this->expectedCracks;
  }
  
  function setExpectedCracks(?int $expectedCracks): void {
    $this->expectedCracks = $expectedCracks;
  }
  
  function getAttackCmd(): ?string {
    return $this->attackCmd;
  }
  
  function setAttackCmd(?string $attackCmd): void {
    $this->attackCmd = $attackCmd;
  }
  
  const HEALTH_CHECK_ID = "healthCheckId";
  const TIME = "time";
  const STATUS = "status";
  const CHECK_TYPE = "checkType";
  const HASHTYPE_ID = "hashtypeId";
  const CRACKER_BINARY_ID = "crackerBinaryId";
  const EXPECTED_CRACKS = "expectedCracks";
  const ATTACK_CMD = "attackCmd";

  const PERM_CREATE = "permHealthCheckCreate";
  const PERM_READ = "permHealthCheckRead";
  const PERM_UPDATE = "permHealthCheckUpdate";
  const PERM_DELETE = "permHealthCheckDelete";
}
