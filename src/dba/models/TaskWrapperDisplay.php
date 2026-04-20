<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class TaskWrapperDisplay extends AbstractModel {
  private ?int $taskWrapperId;
  private ?int $taskWrapperPriority;
  private ?int $taskWrapperMaxAgents;
  private ?int $taskType;
  private ?int $hashlistId;
  private ?int $accessGroupId;
  private ?string $taskWrapperName;
  private ?string $displayName;
  private ?int $taskWrapperIsArchived;
  private ?int $cracked;
  private ?int $taskId;
  private ?string $taskName;
  private ?string $attackCmd;
  private ?int $chunkTime;
  private ?int $statusTimer;
  private ?int $keyspace;
  private ?int $keyspaceProgress;
  private ?int $taskPriority;
  private ?int $taskMaxAgents;
  private ?int $isSmall;
  private ?int $isCpuTask;
  private ?int $taskIsArchived;
  private ?int $taskUsePreprocessor;
  private ?string $hashlistName;
  private ?int $hashCount;
  private ?int $hashlistCracked;
  private ?int $hashTypeId;
  private ?string $hashTypeDescription;
  private ?string $groupName;
  
  function __construct(?int $taskWrapperId, ?int $taskWrapperPriority, ?int $taskWrapperMaxAgents, ?int $taskType, ?int $hashlistId, ?int $accessGroupId, ?string $taskWrapperName, ?string $displayName, ?int $taskWrapperIsArchived, ?int $cracked, ?int $taskId, ?string $taskName, ?string $attackCmd, ?int $chunkTime, ?int $statusTimer, ?int $keyspace, ?int $keyspaceProgress, ?int $taskPriority, ?int $taskMaxAgents, ?int $isSmall, ?int $isCpuTask, ?int $taskIsArchived, ?int $taskUsePreprocessor, ?string $hashlistName, ?int $hashCount, ?int $hashlistCracked, ?int $hashTypeId, ?string $hashTypeDescription, ?string $groupName) {
    $this->taskWrapperId = $taskWrapperId;
    $this->taskWrapperPriority = $taskWrapperPriority;
    $this->taskWrapperMaxAgents = $taskWrapperMaxAgents;
    $this->taskType = $taskType;
    $this->hashlistId = $hashlistId;
    $this->accessGroupId = $accessGroupId;
    $this->taskWrapperName = $taskWrapperName;
    $this->displayName = $displayName;
    $this->taskWrapperIsArchived = $taskWrapperIsArchived;
    $this->cracked = $cracked;
    $this->taskId = $taskId;
    $this->taskName = $taskName;
    $this->attackCmd = $attackCmd;
    $this->chunkTime = $chunkTime;
    $this->statusTimer = $statusTimer;
    $this->keyspace = $keyspace;
    $this->keyspaceProgress = $keyspaceProgress;
    $this->taskPriority = $taskPriority;
    $this->taskMaxAgents = $taskMaxAgents;
    $this->isSmall = $isSmall;
    $this->isCpuTask = $isCpuTask;
    $this->taskIsArchived = $taskIsArchived;
    $this->taskUsePreprocessor = $taskUsePreprocessor;
    $this->hashlistName = $hashlistName;
    $this->hashCount = $hashCount;
    $this->hashlistCracked = $hashlistCracked;
    $this->hashTypeId = $hashTypeId;
    $this->hashTypeDescription = $hashTypeDescription;
    $this->groupName = $groupName;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['taskWrapperId'] = $this->taskWrapperId;
    $dict['taskWrapperPriority'] = $this->taskWrapperPriority;
    $dict['taskWrapperMaxAgents'] = $this->taskWrapperMaxAgents;
    $dict['taskType'] = $this->taskType;
    $dict['hashlistId'] = $this->hashlistId;
    $dict['accessGroupId'] = $this->accessGroupId;
    $dict['taskWrapperName'] = $this->taskWrapperName;
    $dict['displayName'] = $this->displayName;
    $dict['taskWrapperIsArchived'] = $this->taskWrapperIsArchived;
    $dict['cracked'] = $this->cracked;
    $dict['taskId'] = $this->taskId;
    $dict['taskName'] = $this->taskName;
    $dict['attackCmd'] = $this->attackCmd;
    $dict['chunkTime'] = $this->chunkTime;
    $dict['statusTimer'] = $this->statusTimer;
    $dict['keyspace'] = $this->keyspace;
    $dict['keyspaceProgress'] = $this->keyspaceProgress;
    $dict['taskPriority'] = $this->taskPriority;
    $dict['taskMaxAgents'] = $this->taskMaxAgents;
    $dict['isSmall'] = $this->isSmall;
    $dict['isCpuTask'] = $this->isCpuTask;
    $dict['taskIsArchived'] = $this->taskIsArchived;
    $dict['taskUsePreprocessor'] = $this->taskUsePreprocessor;
    $dict['hashlistName'] = $this->hashlistName;
    $dict['hashCount'] = $this->hashCount;
    $dict['hashlistCracked'] = $this->hashlistCracked;
    $dict['hashTypeId'] = $this->hashTypeId;
    $dict['hashTypeDescription'] = $this->hashTypeDescription;
    $dict['groupName'] = $this->groupName;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['taskWrapperId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "taskWrapperId", "public" => False, "dba_mapping" => False];
    $dict['taskWrapperPriority'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskWrapperPriority", "public" => False, "dba_mapping" => False];
    $dict['taskWrapperMaxAgents'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskWrapperMaxAgents", "public" => False, "dba_mapping" => False];
    $dict['taskType'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => [0 => "TaskType is Task", 1 => "TaskType is Supertask", ], "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "taskType", "public" => False, "dba_mapping" => False];
    $dict['hashlistId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "hashlistId", "public" => False, "dba_mapping" => False];
    $dict['accessGroupId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "accessGroupId", "public" => False, "dba_mapping" => False];
    $dict['taskWrapperName'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskWrapperName", "public" => False, "dba_mapping" => False];
    $dict['displayName'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "displayName", "public" => False, "dba_mapping" => False];
    $dict['taskWrapperIsArchived'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskWrapperIsArchived", "public" => False, "dba_mapping" => False];
    $dict['cracked'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "cracked", "public" => False, "dba_mapping" => False];
    $dict['taskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "taskId", "public" => False, "dba_mapping" => False];
    $dict['taskName'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskName", "public" => False, "dba_mapping" => False];
    $dict['attackCmd'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "attackCmd", "public" => False, "dba_mapping" => False];
    $dict['chunkTime'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "chunkTime", "public" => False, "dba_mapping" => False];
    $dict['statusTimer'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "statusTimer", "public" => False, "dba_mapping" => False];
    $dict['keyspace'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "keyspace", "public" => False, "dba_mapping" => False];
    $dict['keyspaceProgress'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "keyspaceProgress", "public" => False, "dba_mapping" => False];
    $dict['taskPriority'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskPriority", "public" => False, "dba_mapping" => False];
    $dict['taskMaxAgents'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskMaxAgents", "public" => False, "dba_mapping" => False];
    $dict['isSmall'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isSmall", "public" => False, "dba_mapping" => False];
    $dict['isCpuTask'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isCpuTask", "public" => False, "dba_mapping" => False];
    $dict['taskIsArchived'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskIsArchived", "public" => False, "dba_mapping" => False];
    $dict['taskUsePreprocessor'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "preprocessorId", "public" => False, "dba_mapping" => False];
    $dict['hashlistName'] = ['read_only' => True, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "hashlistName", "public" => False, "dba_mapping" => False];
    $dict['hashCount'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "hashCount", "public" => False, "dba_mapping" => False];
    $dict['hashlistCracked'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "hashlistCracked", "public" => False, "dba_mapping" => False];
    $dict['hashTypeId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "hashTypeId", "public" => False, "dba_mapping" => False];
    $dict['hashTypeDescription'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "hashTypeDescription", "public" => False, "dba_mapping" => False];
    $dict['groupName'] = ['read_only' => True, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "groupName", "public" => False, "dba_mapping" => False];

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
  
  function getTaskWrapperPriority(): ?int {
    return $this->taskWrapperPriority;
  }
  
  function setTaskWrapperPriority(?int $taskWrapperPriority): void {
    $this->taskWrapperPriority = $taskWrapperPriority;
  }
  
  function getTaskWrapperMaxAgents(): ?int {
    return $this->taskWrapperMaxAgents;
  }
  
  function setTaskWrapperMaxAgents(?int $taskWrapperMaxAgents): void {
    $this->taskWrapperMaxAgents = $taskWrapperMaxAgents;
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
  
  function getDisplayName(): ?string {
    return $this->displayName;
  }
  
  function setDisplayName(?string $displayName): void {
    $this->displayName = $displayName;
  }
  
  function getTaskWrapperIsArchived(): ?int {
    return $this->taskWrapperIsArchived;
  }
  
  function setTaskWrapperIsArchived(?int $taskWrapperIsArchived): void {
    $this->taskWrapperIsArchived = $taskWrapperIsArchived;
  }
  
  function getCracked(): ?int {
    return $this->cracked;
  }
  
  function setCracked(?int $cracked): void {
    $this->cracked = $cracked;
  }
  
  function getTaskId(): ?int {
    return $this->taskId;
  }
  
  function setTaskId(?int $taskId): void {
    $this->taskId = $taskId;
  }
  
  function getTaskName(): ?string {
    return $this->taskName;
  }
  
  function setTaskName(?string $taskName): void {
    $this->taskName = $taskName;
  }
  
  function getAttackCmd(): ?string {
    return $this->attackCmd;
  }
  
  function setAttackCmd(?string $attackCmd): void {
    $this->attackCmd = $attackCmd;
  }
  
  function getChunkTime(): ?int {
    return $this->chunkTime;
  }
  
  function setChunkTime(?int $chunkTime): void {
    $this->chunkTime = $chunkTime;
  }
  
  function getStatusTimer(): ?int {
    return $this->statusTimer;
  }
  
  function setStatusTimer(?int $statusTimer): void {
    $this->statusTimer = $statusTimer;
  }
  
  function getKeyspace(): ?int {
    return $this->keyspace;
  }
  
  function setKeyspace(?int $keyspace): void {
    $this->keyspace = $keyspace;
  }
  
  function getKeyspaceProgress(): ?int {
    return $this->keyspaceProgress;
  }
  
  function setKeyspaceProgress(?int $keyspaceProgress): void {
    $this->keyspaceProgress = $keyspaceProgress;
  }
  
  function getTaskPriority(): ?int {
    return $this->taskPriority;
  }
  
  function setTaskPriority(?int $taskPriority): void {
    $this->taskPriority = $taskPriority;
  }
  
  function getTaskMaxAgents(): ?int {
    return $this->taskMaxAgents;
  }
  
  function setTaskMaxAgents(?int $taskMaxAgents): void {
    $this->taskMaxAgents = $taskMaxAgents;
  }
  
  function getIsSmall(): ?int {
    return $this->isSmall;
  }
  
  function setIsSmall(?int $isSmall): void {
    $this->isSmall = $isSmall;
  }
  
  function getIsCpuTask(): ?int {
    return $this->isCpuTask;
  }
  
  function setIsCpuTask(?int $isCpuTask): void {
    $this->isCpuTask = $isCpuTask;
  }
  
  function getTaskIsArchived(): ?int {
    return $this->taskIsArchived;
  }
  
  function setTaskIsArchived(?int $taskIsArchived): void {
    $this->taskIsArchived = $taskIsArchived;
  }
  
  function getTaskUsePreprocessor(): ?int {
    return $this->taskUsePreprocessor;
  }
  
  function setTaskUsePreprocessor(?int $taskUsePreprocessor): void {
    $this->taskUsePreprocessor = $taskUsePreprocessor;
  }
  
  function getHashlistName(): ?string {
    return $this->hashlistName;
  }
  
  function setHashlistName(?string $hashlistName): void {
    $this->hashlistName = $hashlistName;
  }
  
  function getHashCount(): ?int {
    return $this->hashCount;
  }
  
  function setHashCount(?int $hashCount): void {
    $this->hashCount = $hashCount;
  }
  
  function getHashlistCracked(): ?int {
    return $this->hashlistCracked;
  }
  
  function setHashlistCracked(?int $hashlistCracked): void {
    $this->hashlistCracked = $hashlistCracked;
  }
  
  function getHashTypeId(): ?int {
    return $this->hashTypeId;
  }
  
  function setHashTypeId(?int $hashTypeId): void {
    $this->hashTypeId = $hashTypeId;
  }
  
  function getHashTypeDescription(): ?string {
    return $this->hashTypeDescription;
  }
  
  function setHashTypeDescription(?string $hashTypeDescription): void {
    $this->hashTypeDescription = $hashTypeDescription;
  }
  
  function getGroupName(): ?string {
    return $this->groupName;
  }
  
  function setGroupName(?string $groupName): void {
    $this->groupName = $groupName;
  }
  
  const TASK_WRAPPER_ID = "taskWrapperId";
  const TASK_WRAPPER_PRIORITY = "taskWrapperPriority";
  const TASK_WRAPPER_MAX_AGENTS = "taskWrapperMaxAgents";
  const TASK_TYPE = "taskType";
  const HASHLIST_ID = "hashlistId";
  const ACCESS_GROUP_ID = "accessGroupId";
  const TASK_WRAPPER_NAME = "taskWrapperName";
  const DISPLAY_NAME = "displayName";
  const TASK_WRAPPER_IS_ARCHIVED = "taskWrapperIsArchived";
  const CRACKED = "cracked";
  const TASK_ID = "taskId";
  const TASK_NAME = "taskName";
  const ATTACK_CMD = "attackCmd";
  const CHUNK_TIME = "chunkTime";
  const STATUS_TIMER = "statusTimer";
  const KEYSPACE = "keyspace";
  const KEYSPACE_PROGRESS = "keyspaceProgress";
  const TASK_PRIORITY = "taskPriority";
  const TASK_MAX_AGENTS = "taskMaxAgents";
  const IS_SMALL = "isSmall";
  const IS_CPU_TASK = "isCpuTask";
  const TASK_IS_ARCHIVED = "taskIsArchived";
  const TASK_USE_PREPROCESSOR = "taskUsePreprocessor";
  const HASHLIST_NAME = "hashlistName";
  const HASH_COUNT = "hashCount";
  const HASHLIST_CRACKED = "hashlistCracked";
  const HASH_TYPE_ID = "hashTypeId";
  const HASH_TYPE_DESCRIPTION = "hashTypeDescription";
  const GROUP_NAME = "groupName";

  const PERM_CREATE = "permTaskWrapperDisplayCreate";
  const PERM_READ = "permTaskWrapperDisplayRead";
  const PERM_UPDATE = "permTaskWrapperDisplayUpdate";
  const PERM_DELETE = "permTaskWrapperDisplayDelete";
}
