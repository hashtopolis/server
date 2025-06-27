<?php

namespace DBA;

class Task extends AbstractModel {
  private ?int $taskId;
  private ?string $taskName;
  private ?string $attackCmd;
  private ?int $chunkTime;
  private ?int $statusTimer;
  private ?int $keyspace;
  private ?int $keyspaceProgress;
  private ?int $priority;
  private ?int $maxAgents;
  private ?string $color;
  private ?int $isSmall;
  private ?int $isCpuTask;
  private ?int $useNewBench;
  private ?int $skipKeyspace;
  private ?int $crackerBinaryId;
  private ?int $crackerBinaryTypeId;
  private ?int $taskWrapperId;
  private ?int $isArchived;
  private ?string $notes;
  private ?int $staticChunks;
  private ?int $chunkSize;
  private ?int $forcePipe;
  private ?int $usePreprocessor;
  private ?string $preprocessorCommand;
  
  function __construct(?int $taskId, ?string $taskName, ?string $attackCmd, ?int $chunkTime, ?int $statusTimer, ?int $keyspace, ?int $keyspaceProgress, ?int $priority, ?int $maxAgents, ?string $color, ?int $isSmall, ?int $isCpuTask, ?int $useNewBench, ?int $skipKeyspace, ?int $crackerBinaryId, ?int $crackerBinaryTypeId, ?int $taskWrapperId, ?int $isArchived, ?string $notes, ?int $staticChunks, ?int $chunkSize, ?int $forcePipe, ?int $usePreprocessor, ?string $preprocessorCommand) {
    $this->taskId = $taskId;
    $this->taskName = $taskName;
    $this->attackCmd = $attackCmd;
    $this->chunkTime = $chunkTime;
    $this->statusTimer = $statusTimer;
    $this->keyspace = $keyspace;
    $this->keyspaceProgress = $keyspaceProgress;
    $this->priority = $priority;
    $this->maxAgents = $maxAgents;
    $this->color = $color;
    $this->isSmall = $isSmall;
    $this->isCpuTask = $isCpuTask;
    $this->useNewBench = $useNewBench;
    $this->skipKeyspace = $skipKeyspace;
    $this->crackerBinaryId = $crackerBinaryId;
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
    $this->taskWrapperId = $taskWrapperId;
    $this->isArchived = $isArchived;
    $this->notes = $notes;
    $this->staticChunks = $staticChunks;
    $this->chunkSize = $chunkSize;
    $this->forcePipe = $forcePipe;
    $this->usePreprocessor = $usePreprocessor;
    $this->preprocessorCommand = $preprocessorCommand;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['taskId'] = $this->taskId;
    $dict['taskName'] = $this->taskName;
    $dict['attackCmd'] = $this->attackCmd;
    $dict['chunkTime'] = $this->chunkTime;
    $dict['statusTimer'] = $this->statusTimer;
    $dict['keyspace'] = $this->keyspace;
    $dict['keyspaceProgress'] = $this->keyspaceProgress;
    $dict['priority'] = $this->priority;
    $dict['maxAgents'] = $this->maxAgents;
    $dict['color'] = $this->color;
    $dict['isSmall'] = $this->isSmall;
    $dict['isCpuTask'] = $this->isCpuTask;
    $dict['useNewBench'] = $this->useNewBench;
    $dict['skipKeyspace'] = $this->skipKeyspace;
    $dict['crackerBinaryId'] = $this->crackerBinaryId;
    $dict['crackerBinaryTypeId'] = $this->crackerBinaryTypeId;
    $dict['taskWrapperId'] = $this->taskWrapperId;
    $dict['isArchived'] = $this->isArchived;
    $dict['notes'] = $this->notes;
    $dict['staticChunks'] = $this->staticChunks;
    $dict['chunkSize'] = $this->chunkSize;
    $dict['forcePipe'] = $this->forcePipe;
    $dict['usePreprocessor'] = $this->usePreprocessor;
    $dict['preprocessorCommand'] = $this->preprocessorCommand;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['taskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "taskId", "public" => False];
    $dict['taskName'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskName", "public" => False];
    $dict['attackCmd'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "attackCmd", "public" => False];
    $dict['chunkTime'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "chunkTime", "public" => False];
    $dict['statusTimer'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "statusTimer", "public" => False];
    $dict['keyspace'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "keyspace", "public" => False];
    $dict['keyspaceProgress'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "keyspaceProgress", "public" => False];
    $dict['priority'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "priority", "public" => False];
    $dict['maxAgents'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "maxAgents", "public" => False];
    $dict['color'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "color", "public" => False];
    $dict['isSmall'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isSmall", "public" => False];
    $dict['isCpuTask'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isCpuTask", "public" => False];
    $dict['useNewBench'] = ['read_only' => True, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "useNewBench", "public" => False];
    $dict['skipKeyspace'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "skipKeyspace", "public" => False];
    $dict['crackerBinaryId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "crackerBinaryId", "public" => False];
    $dict['crackerBinaryTypeId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "crackerBinaryTypeId", "public" => False];
    $dict['taskWrapperId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "taskWrapperId", "public" => False];
    $dict['isArchived'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isArchived", "public" => False];
    $dict['notes'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "notes", "public" => False];
    $dict['staticChunks'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "staticChunks", "public" => False];
    $dict['chunkSize'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "chunkSize", "public" => False];
    $dict['forcePipe'] = ['read_only' => True, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "forcePipe", "public" => False];
    $dict['usePreprocessor'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "preprocessorId", "public" => False];
    $dict['preprocessorCommand'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "preprocessorCommand", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "taskId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->taskId;
  }
  
  function getId(): ?int {
    return $this->taskId;
  }
  
  function setId($id): void {
    $this->taskId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
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
  
  function getColor(): ?string {
    return $this->color;
  }
  
  function setColor(?string $color): void {
    $this->color = $color;
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
  
  function getUseNewBench(): ?int {
    return $this->useNewBench;
  }
  
  function setUseNewBench(?int $useNewBench): void {
    $this->useNewBench = $useNewBench;
  }
  
  function getSkipKeyspace(): ?int {
    return $this->skipKeyspace;
  }
  
  function setSkipKeyspace(?int $skipKeyspace): void {
    $this->skipKeyspace = $skipKeyspace;
  }
  
  function getCrackerBinaryId(): ?int {
    return $this->crackerBinaryId;
  }
  
  function setCrackerBinaryId(?int $crackerBinaryId): void {
    $this->crackerBinaryId = $crackerBinaryId;
  }
  
  function getCrackerBinaryTypeId(): ?int {
    return $this->crackerBinaryTypeId;
  }
  
  function setCrackerBinaryTypeId(?int $crackerBinaryTypeId): void {
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
  }
  
  function getTaskWrapperId(): ?int {
    return $this->taskWrapperId;
  }
  
  function setTaskWrapperId(?int $taskWrapperId): void {
    $this->taskWrapperId = $taskWrapperId;
  }
  
  function getIsArchived(): ?int {
    return $this->isArchived;
  }
  
  function setIsArchived(?int $isArchived): void {
    $this->isArchived = $isArchived;
  }
  
  function getNotes(): ?string {
    return $this->notes;
  }
  
  function setNotes(?string $notes): void {
    $this->notes = $notes;
  }
  
  function getStaticChunks(): ?int {
    return $this->staticChunks;
  }
  
  function setStaticChunks(?int $staticChunks): void {
    $this->staticChunks = $staticChunks;
  }
  
  function getChunkSize(): ?int {
    return $this->chunkSize;
  }
  
  function setChunkSize(?int $chunkSize): void {
    $this->chunkSize = $chunkSize;
  }
  
  function getForcePipe(): ?int {
    return $this->forcePipe;
  }
  
  function setForcePipe(?int $forcePipe): void {
    $this->forcePipe = $forcePipe;
  }
  
  function getUsePreprocessor(): ?int {
    return $this->usePreprocessor;
  }
  
  function setUsePreprocessor(?int $usePreprocessor): void {
    $this->usePreprocessor = $usePreprocessor;
  }
  
  function getPreprocessorCommand(): ?string {
    return $this->preprocessorCommand;
  }
  
  function setPreprocessorCommand(?string $preprocessorCommand): void {
    $this->preprocessorCommand = $preprocessorCommand;
  }
  
  const TASK_ID = "taskId";
  const TASK_NAME = "taskName";
  const ATTACK_CMD = "attackCmd";
  const CHUNK_TIME = "chunkTime";
  const STATUS_TIMER = "statusTimer";
  const KEYSPACE = "keyspace";
  const KEYSPACE_PROGRESS = "keyspaceProgress";
  const PRIORITY = "priority";
  const MAX_AGENTS = "maxAgents";
  const COLOR = "color";
  const IS_SMALL = "isSmall";
  const IS_CPU_TASK = "isCpuTask";
  const USE_NEW_BENCH = "useNewBench";
  const SKIP_KEYSPACE = "skipKeyspace";
  const CRACKER_BINARY_ID = "crackerBinaryId";
  const CRACKER_BINARY_TYPE_ID = "crackerBinaryTypeId";
  const TASK_WRAPPER_ID = "taskWrapperId";
  const IS_ARCHIVED = "isArchived";
  const NOTES = "notes";
  const STATIC_CHUNKS = "staticChunks";
  const CHUNK_SIZE = "chunkSize";
  const FORCE_PIPE = "forcePipe";
  const USE_PREPROCESSOR = "usePreprocessor";
  const PREPROCESSOR_COMMAND = "preprocessorCommand";

  const PERM_CREATE = "permTaskCreate";
  const PERM_READ = "permTaskRead";
  const PERM_UPDATE = "permTaskUpdate";
  const PERM_DELETE = "permTaskDelete";
}
