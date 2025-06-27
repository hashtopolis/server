<?php

namespace DBA;

class AccessGroupAgent extends AbstractModel {
  private ?int $accessGroupAgentId;
  private ?int $accessGroupId;
  private ?int $agentId;
  
  function __construct(?int $accessGroupAgentId, ?int $accessGroupId, ?int $agentId) {
    $this->accessGroupAgentId = $accessGroupAgentId;
    $this->accessGroupId = $accessGroupId;
    $this->agentId = $agentId;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['accessGroupAgentId'] = $this->accessGroupAgentId;
    $dict['accessGroupId'] = $this->accessGroupId;
    $dict['agentId'] = $this->agentId;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['accessGroupAgentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "accessGroupAgentId", "public" => False];
    $dict['accessGroupId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "accessGroupId", "public" => False];
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "agentId", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "accessGroupAgentId";
  }
  
  function getPrimaryKeyValue(): int {
    return $this->accessGroupAgentId;
  }
  
  function getId(): int {
    return $this->accessGroupAgentId;
  }
  
  function setId($id): void {
    $this->accessGroupAgentId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getAccessGroupId(): ?int {
    return $this->accessGroupId;
  }
  
  function setAccessGroupId(?int $accessGroupId): void {
    $this->accessGroupId = $accessGroupId;
  }
  
  function getAgentId(): ?int {
    return $this->agentId;
  }
  
  function setAgentId(?int $agentId): void {
    $this->agentId = $agentId;
  }
  
  const ACCESS_GROUP_AGENT_ID = "accessGroupAgentId";
  const ACCESS_GROUP_ID = "accessGroupId";
  const AGENT_ID = "agentId";

  const PERM_CREATE = "permAccessGroupAgentCreate";
  const PERM_READ = "permAccessGroupAgentRead";
  const PERM_UPDATE = "permAccessGroupAgentUpdate";
  const PERM_DELETE = "permAccessGroupAgentDelete";
}
