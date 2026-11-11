<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class Chunk extends AbstractModel {
  private ?int $chunkId;
  private ?int $taskId;
  private ?int $skip;
  private ?int $length;
  private ?int $agentId;
  private ?int $dispatchTime;
  private ?int $solveTime;
  private ?int $checkpoint;
  private ?int $progress;
  private ?int $state;
  private ?int $cracked;
  private ?int $speed;
  
  function __construct(?int $chunkId, ?int $taskId, ?int $skip, ?int $length, ?int $agentId, ?int $dispatchTime, ?int $solveTime, ?int $checkpoint, ?int $progress, ?int $state, ?int $cracked, ?int $speed) {
    $this->chunkId = $chunkId;
    $this->taskId = $taskId;
    $this->skip = $skip;
    $this->length = $length;
    $this->agentId = $agentId;
    $this->dispatchTime = $dispatchTime;
    $this->solveTime = $solveTime;
    $this->checkpoint = $checkpoint;
    $this->progress = $progress;
    $this->state = $state;
    $this->cracked = $cracked;
    $this->speed = $speed;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['chunkId'] = $this->chunkId;
    $dict['taskId'] = $this->taskId;
    $dict['skip'] = $this->skip;
    $dict['length'] = $this->length;
    $dict['agentId'] = $this->agentId;
    $dict['dispatchTime'] = $this->dispatchTime;
    $dict['solveTime'] = $this->solveTime;
    $dict['checkpoint'] = $this->checkpoint;
    $dict['progress'] = $this->progress;
    $dict['state'] = $this->state;
    $dict['cracked'] = $this->cracked;
    $dict['speed'] = $this->speed;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['chunkId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "chunkId", "public" => False, "dba_mapping" => False];
    $dict['taskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "taskId", "public" => False, "dba_mapping" => False];
    $dict['skip'] = ['read_only' => True, "type" => "uint64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "skip", "public" => False, "dba_mapping" => False, "description" => "Offset into the keyspace of the task at which this chunk starts."];
    $dict['length'] = ['read_only' => True, "type" => "uint64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "length", "public" => False, "dba_mapping" => False, "description" => "How much of the keyspace this chunk covers."];
    $dict['agentId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => True, "private" => False, "alias" => "agentId", "public" => False, "dba_mapping" => False];
    $dict['dispatchTime'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "dispatchTime", "public" => False, "dba_mapping" => False, "description" => "When the chunk was handed to an agent, as a unix timestamp."];
    $dict['solveTime'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "solveTime", "public" => False, "dba_mapping" => False, "description" => "When the agent last reported on this chunk, as a unix timestamp."];
    $dict['checkpoint'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "checkpoint", "public" => False, "dba_mapping" => False, "description" => "Keyspace offset of the last checkpoint, from which the chunk can be resumed."];
    $dict['progress'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "progress", "public" => False, "dba_mapping" => False, "description" => "How far the chunk has got, in hundredths of a percent, so that 10000 means finished."];
    $dict['state'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => [0 => "Starting up", 1 => "Tuning itself to the devices of the agent", 2 => "Working through its keyspace", 3 => "Paused", 4 => "Finished its keyspace without cracking every hash", 5 => "Finished because every hash of the hashlist was cracked", 6 => "Aborted", 7 => "Stopped on request", 8 => "Skipped because the task was bypassed", 9 => "Aborted at a checkpoint, so it can resume from there", 10 => "Aborted after reaching its runtime limit", ], "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "state", "public" => False, "dba_mapping" => False, "choice_names" => [0 => "INIT", 1 => "AUTOTUNE", 2 => "RUNNING", 3 => "PAUSED", 4 => "EXHAUSTED", 5 => "CRACKED", 6 => "ABORTED", 7 => "QUIT", 8 => "BYPASS", 9 => "ABORTED_CHECKPOINT", 10 => "STATUS_ABORTED_RUNTIME", ], "description" => "The state the cracker reported for this chunk, as a hashcat status value."];
    $dict['cracked'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "cracked", "public" => False, "dba_mapping" => False, "description" => "Number of hashes cracked while working on this chunk."];
    $dict['speed'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "speed", "public" => False, "dba_mapping" => False, "description" => "Candidates per second the agent last reported for this chunk."];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "chunkId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->chunkId;
  }
  
  function getId(): ?int {
    return $this->chunkId;
  }
  
  function setId($id): void {
    $this->chunkId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getTaskId(): ?int {
    return $this->taskId;
  }
  
  function setTaskId(?int $taskId): void {
    $this->taskId = $taskId;
  }
  
  function getSkip(): ?int {
    return $this->skip;
  }
  
  function setSkip(?int $skip): void {
    $this->skip = $skip;
  }
  
  function getLength(): ?int {
    return $this->length;
  }
  
  function setLength(?int $length): void {
    $this->length = $length;
  }
  
  function getAgentId(): ?int {
    return $this->agentId;
  }
  
  function setAgentId(?int $agentId): void {
    $this->agentId = $agentId;
  }
  
  function getDispatchTime(): ?int {
    return $this->dispatchTime;
  }
  
  function setDispatchTime(?int $dispatchTime): void {
    $this->dispatchTime = $dispatchTime;
  }
  
  function getSolveTime(): ?int {
    return $this->solveTime;
  }
  
  function setSolveTime(?int $solveTime): void {
    $this->solveTime = $solveTime;
  }
  
  function getCheckpoint(): ?int {
    return $this->checkpoint;
  }
  
  function setCheckpoint(?int $checkpoint): void {
    $this->checkpoint = $checkpoint;
  }
  
  function getProgress(): ?int {
    return $this->progress;
  }
  
  function setProgress(?int $progress): void {
    $this->progress = $progress;
  }
  
  function getState(): ?int {
    return $this->state;
  }
  
  function setState(?int $state): void {
    $this->state = $state;
  }
  
  function getCracked(): ?int {
    return $this->cracked;
  }
  
  function setCracked(?int $cracked): void {
    $this->cracked = $cracked;
  }
  
  function getSpeed(): ?int {
    return $this->speed;
  }
  
  function setSpeed(?int $speed): void {
    $this->speed = $speed;
  }
  
  const CHUNK_ID = "chunkId";
  const TASK_ID = "taskId";
  const SKIP = "skip";
  const LENGTH = "length";
  const AGENT_ID = "agentId";
  const DISPATCH_TIME = "dispatchTime";
  const SOLVE_TIME = "solveTime";
  const CHECKPOINT = "checkpoint";
  const PROGRESS = "progress";
  const STATE = "state";
  const CRACKED = "cracked";
  const SPEED = "speed";

  const PERM_CREATE = "permChunkCreate";
  const PERM_READ = "permChunkRead";
  const PERM_UPDATE = "permChunkUpdate";
  const PERM_DELETE = "permChunkDelete";
}
