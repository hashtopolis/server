<?php

namespace DBA;

class TaskWrapper extends AbstractModel {
  private ?int $taskWrapperId;
  private ?int $priority;
  private ?int $maxAgents;
  private ?int $taskType;
  private ?int $hashlistId;
  private ?int $accessGroupId;
  private ?string $taskWrapperName;
  private ?int $isArchived;
  private ?int $cracked;
  
  function __construct(?int $taskWrapperId, ?int $priority, ?int $maxAgents, ?int $taskType, ?int $hashlistId, ?int $accessGroupId, ?string $taskWrapperName, ?int $isArchived, ?int $cracked) {
    $this->taskWrapperId = $taskWrapperId;
    $this->priority = $priority;
    $this->maxAgents = $maxAgents;
    $this->taskType = $taskType;
    $this->hashlistId = $hashlistId;
    $this->accessGroupId = $accessGroupId;
    $this->taskWrapperName = $taskWrapperName;
    $this->isArchived = $isArchived;
    $this->cracked = $cracked;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['taskWrapperId'] = $this->taskWrapperId;
    $dict['priority'] = $this->priority;
    $dict['maxAgents'] = $this->maxAgents;
    $dict['taskType'] = $this->taskType;
    $dict['hashlistId'] = $this->hashlistId;
    $dict['accessGroupId'] = $this->accessGroupId;
    $dict['taskWrapperName'] = $this->taskWrapperName;
    $dict['isArchived'] = $this->isArchived;
    $dict['cracked'] = $this->cracked;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['taskWrapperId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "taskWrapperId", "public" => False];
    $dict['priority'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "priority", "public" => False];
    $dict['maxAgents'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "maxAgents", "public" => False];
    $dict['taskType'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => [0 => "TaskType is Task", 1 => "TaskType is Supertask", ], "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "taskType", "public" => False];
    $dict['hashlistId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "hashlistId", "public" => False];
    $dict['accessGroupId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "accessGroupId", "public" => False];
    $dict['taskWrapperName'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskWrapperName", "public" => False];
    $dict['isArchived'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isArchived", "public" => False];
    $dict['cracked'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "cracked", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "taskWrapperId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->taskWrapperId;
  }
  
  function getId(): ?int {
    return $this->taskWrapperId;
  }
  
  function setId($id): void {
    $this->taskWrapperId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getPriority(): ?int {
    return $this->priority;
  }
  
  function setPriority(?int $priority): void {
    $this->priority = $priority;
  }
  
  function getMaxAgents(): ?int {
    return $this->maxAgents;
  }
  
  function setMaxAgents(?int $maxAgents): void {
    $this->maxAgents = $maxAgents;
  }
  
  function getTaskType(): ?int {
    return $this->taskType;
  }
  
  function setTaskType(?int $taskType): void {
    $this->taskType = $taskType;
  }
  
  function getHashlistId(): ?int {
    return $this->hashlistId;
  }
  
  function setHashlistId(?int $hashlistId): void {
    $this->hashlistId = $hashlistId;
  }
  
  function getAccessGroupId(): ?int {
    return $this->accessGroupId;
  }
  
  function setAccessGroupId(?int $accessGroupId): void {
    $this->accessGroupId = $accessGroupId;
  }
  
  function getTaskWrapperName(): ?string {
    return $this->taskWrapperName;
  }
  
  function setTaskWrapperName(?string $taskWrapperName): void {
    $this->taskWrapperName = $taskWrapperName;
  }
  
  function getIsArchived(): ?int {
    return $this->isArchived;
  }
  
  function setIsArchived(?int $isArchived): void {
    $this->isArchived = $isArchived;
  }
  
  function getCracked(): ?int {
    return $this->cracked;
  }
  
  function setCracked(?int $cracked): void {
    $this->cracked = $cracked;
  }
  
  const TASK_WRAPPER_ID = "taskWrapperId";
  const PRIORITY = "priority";
  const MAX_AGENTS = "maxAgents";
  const TASK_TYPE = "taskType";
  const HASHLIST_ID = "hashlistId";
  const ACCESS_GROUP_ID = "accessGroupId";
  const TASK_WRAPPER_NAME = "taskWrapperName";
  const IS_ARCHIVED = "isArchived";
  const CRACKED = "cracked";

  const PERM_CREATE = "permTaskWrapperCreate";
  const PERM_READ = "permTaskWrapperRead";
  const PERM_UPDATE = "permTaskWrapperUpdate";
  const PERM_DELETE = "permTaskWrapperDelete";
}
