<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class Pretask extends AbstractModel {
  private ?int $pretaskId;
  private ?string $taskName;
  private ?string $attackCmd;
  private ?int $chunkTime;
  private ?int $statusTimer;
  private ?string $color;
  private ?int $isSmall;
  private ?int $isCpuTask;
  private ?int $useNewBench;
  private ?int $priority;
  private ?int $maxAgents;
  private ?int $isMaskImport;
  private ?int $crackerBinaryTypeId;
  
  function __construct(?int $pretaskId, ?string $taskName, ?string $attackCmd, ?int $chunkTime, ?int $statusTimer, ?string $color, ?int $isSmall, ?int $isCpuTask, ?int $useNewBench, ?int $priority, ?int $maxAgents, ?int $isMaskImport, ?int $crackerBinaryTypeId) {
    $this->pretaskId = $pretaskId;
    $this->taskName = $taskName;
    $this->attackCmd = $attackCmd;
    $this->chunkTime = $chunkTime;
    $this->statusTimer = $statusTimer;
    $this->color = $color;
    $this->isSmall = $isSmall;
    $this->isCpuTask = $isCpuTask;
    $this->useNewBench = $useNewBench;
    $this->priority = $priority;
    $this->maxAgents = $maxAgents;
    $this->isMaskImport = $isMaskImport;
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['pretaskId'] = $this->pretaskId;
    $dict['taskName'] = $this->taskName;
    $dict['attackCmd'] = $this->attackCmd;
    $dict['chunkTime'] = $this->chunkTime;
    $dict['statusTimer'] = $this->statusTimer;
    $dict['color'] = $this->color;
    $dict['isSmall'] = $this->isSmall;
    $dict['isCpuTask'] = $this->isCpuTask;
    $dict['useNewBench'] = $this->useNewBench;
    $dict['priority'] = $this->priority;
    $dict['maxAgents'] = $this->maxAgents;
    $dict['isMaskImport'] = $this->isMaskImport;
    $dict['crackerBinaryTypeId'] = $this->crackerBinaryTypeId;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['pretaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "pretaskId", "public" => False, "dba_mapping" => False];
    $dict['taskName'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskName", "public" => False, "dba_mapping" => False];
    $dict['attackCmd'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "attackCmd", "public" => False, "dba_mapping" => False, "description" => "The cracker arguments the agents run, with #HL# standing in for the hashlist file."];
    $dict['chunkTime'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "chunkTime", "public" => False, "dba_mapping" => False, "description" => "How long one chunk of this task should keep an agent busy, in seconds. The chunk size is derived from it and the benchmark of the agent."];
    $dict['statusTimer'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "statusTimer", "public" => False, "dba_mapping" => False, "description" => "How often an agent working on this task reports its progress, in seconds."];
    $dict['color'] = ['read_only' => False, "type" => "str(20)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "color", "public" => False, "dba_mapping" => False, "description" => "Colour the web interface tints tasks created from this pretask with, as an RGB hex triplet without the leading hash sign."];
    $dict['isSmall'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isSmall", "public" => False, "dba_mapping" => False, "description" => "Whether the task is small, in which case only ever one agent is assigned to it."];
    $dict['isCpuTask'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isCpuTask", "public" => False, "dba_mapping" => False, "description" => "Whether the task is meant for CPU agents rather than for agents with a GPU."];
    $dict['useNewBench'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "useNewBench", "public" => False, "dba_mapping" => False, "description" => "Whether agents benchmark this task by speed rather than by runtime. Sent to the agent as benchType."];
    $dict['priority'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "priority", "public" => False, "dba_mapping" => False, "description" => "Dispatch order: of the tasks an agent may take, the one with the highest priority is handed out first."];
    $dict['maxAgents'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "maxAgents", "public" => False, "dba_mapping" => False, "description" => "How many agents may work on this task at the same time. Zero leaves it unlimited."];
    $dict['isMaskImport'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isMaskImport", "public" => False, "dba_mapping" => False, "description" => "Whether the attack command came from an imported mask file."];
    $dict['crackerBinaryTypeId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "crackerBinaryTypeId", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "pretaskId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->pretaskId;
  }
  
  function getId(): ?int {
    return $this->pretaskId;
  }
  
  function setId($id): void {
    $this->pretaskId = $id;
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
  
  function getIsMaskImport(): ?int {
    return $this->isMaskImport;
  }
  
  function setIsMaskImport(?int $isMaskImport): void {
    $this->isMaskImport = $isMaskImport;
  }
  
  function getCrackerBinaryTypeId(): ?int {
    return $this->crackerBinaryTypeId;
  }
  
  function setCrackerBinaryTypeId(?int $crackerBinaryTypeId): void {
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
  }
  
  const PRETASK_ID = "pretaskId";
  const TASK_NAME = "taskName";
  const ATTACK_CMD = "attackCmd";
  const CHUNK_TIME = "chunkTime";
  const STATUS_TIMER = "statusTimer";
  const COLOR = "color";
  const IS_SMALL = "isSmall";
  const IS_CPU_TASK = "isCpuTask";
  const USE_NEW_BENCH = "useNewBench";
  const PRIORITY = "priority";
  const MAX_AGENTS = "maxAgents";
  const IS_MASK_IMPORT = "isMaskImport";
  const CRACKER_BINARY_TYPE_ID = "crackerBinaryTypeId";

  const PERM_CREATE = "permPretaskCreate";
  const PERM_READ = "permPretaskRead";
  const PERM_UPDATE = "permPretaskUpdate";
  const PERM_DELETE = "permPretaskDelete";
}
