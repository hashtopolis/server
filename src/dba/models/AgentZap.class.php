<?php

namespace DBA;

class AgentZap extends AbstractModel {
  private ?int $agentZapId;
  private ?int $agentId;
  private ?string $lastZapId;
  
  function __construct(?int $agentZapId, ?int $agentId, ?string $lastZapId) {
    $this->agentZapId = $agentZapId;
    $this->agentId = $agentId;
    $this->lastZapId = $lastZapId;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['agentZapId'] = $this->agentZapId;
    $dict['agentId'] = $this->agentId;
    $dict['lastZapId'] = $this->lastZapId;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['agentZapId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "agentZapId", "public" => False];
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "agentId", "public" => False];
    $dict['lastZapId'] = ['read_only' => True, "type" => "str(128)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lastZapId", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "agentZapId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->agentZapId;
  }
  
  function getId(): ?int {
    return $this->agentZapId;
  }
  
  function setId($id): void {
    $this->agentZapId = $id;
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
  
  function getLastZapId(): ?string {
    return $this->lastZapId;
  }
  
  function setLastZapId(?string $lastZapId): void {
    $this->lastZapId = $lastZapId;
  }
  
  const AGENT_ZAP_ID = "agentZapId";
  const AGENT_ID = "agentId";
  const LAST_ZAP_ID = "lastZapId";

  const PERM_CREATE = "permAgentZapCreate";
  const PERM_READ = "permAgentZapRead";
  const PERM_UPDATE = "permAgentZapUpdate";
  const PERM_DELETE = "permAgentZapDelete";
}
