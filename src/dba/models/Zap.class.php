<?php

namespace DBA;

class Zap extends AbstractModel {
  private ?int $zapId;
  private ?string $hash;
  private ?int $solveTime;
  private ?int $agentId;
  private ?int $hashlistId;
  
  function __construct(?int $zapId, ?string $hash, ?int $solveTime, ?int $agentId, ?int $hashlistId) {
    $this->zapId = $zapId;
    $this->hash = $hash;
    $this->solveTime = $solveTime;
    $this->agentId = $agentId;
    $this->hashlistId = $hashlistId;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['zapId'] = $this->zapId;
    $dict['hash'] = $this->hash;
    $dict['solveTime'] = $this->solveTime;
    $dict['agentId'] = $this->agentId;
    $dict['hashlistId'] = $this->hashlistId;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['zapId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "zapId", "public" => False];
    $dict['hash'] = ['read_only' => True, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "hash", "public" => False];
    $dict['solveTime'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "solveTime", "public" => False];
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "agentId", "public" => False];
    $dict['hashlistId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "hashlistId", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "zapId";
  }
  
  function getPrimaryKeyValue(): int {
    return $this->zapId;
  }
  
  function getId(): int {
    return $this->zapId;
  }
  
  function setId($id): void {
    $this->zapId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getHash(): ?string {
    return $this->hash;
  }
  
  function setHash(?string $hash): void {
    $this->hash = $hash;
  }
  
  function getSolveTime(): ?int {
    return $this->solveTime;
  }
  
  function setSolveTime(?int $solveTime): void {
    $this->solveTime = $solveTime;
  }
  
  function getAgentId(): ?int {
    return $this->agentId;
  }
  
  function setAgentId(?int $agentId): void {
    $this->agentId = $agentId;
  }
  
  function getHashlistId(): ?int {
    return $this->hashlistId;
  }
  
  function setHashlistId(?int $hashlistId): void {
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
