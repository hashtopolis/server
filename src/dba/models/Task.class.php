<?php

namespace DBA;

class Task extends AbstractModel {
  private $taskId;
  private $taskName;
  private $attackCmd;
  private $chunkTime;
  private $statusTimer;
  private $keyspace;
  private $keyspaceProgress;
  private $priority;
  private $color;
  private $isSmall;
  private $isCpuTask;
  private $useNewBench;
  private $skipKeyspace;
  private $crackerBinaryId;
  private $crackerBinaryTypeId;
  private $taskWrapperId;
  private $isArchived;
  private $notes;
  private $staticChunks;
  private $chunkSize;
  private $forcePipe;
  private $usePreprocessor;
  private $preprocessorCommand;
  
  function __construct($taskId, $taskName, $attackCmd, $chunkTime, $statusTimer, $keyspace, $keyspaceProgress, $priority, $color, $isSmall, $isCpuTask, $useNewBench, $skipKeyspace, $crackerBinaryId, $crackerBinaryTypeId, $taskWrapperId, $isArchived, $notes, $staticChunks, $chunkSize, $forcePipe, $usePreprocessor, $preprocessorCommand) {
    $this->taskId = $taskId;
    $this->taskName = $taskName;
    $this->attackCmd = $attackCmd;
    $this->chunkTime = $chunkTime;
    $this->statusTimer = $statusTimer;
    $this->keyspace = $keyspace;
    $this->keyspaceProgress = $keyspaceProgress;
    $this->priority = $priority;
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
  
  function getKeyValueDict() {
    $dict = array();
    $dict['taskId'] = $this->taskId;
    $dict['taskName'] = $this->taskName;
    $dict['attackCmd'] = $this->attackCmd;
    $dict['chunkTime'] = $this->chunkTime;
    $dict['statusTimer'] = $this->statusTimer;
    $dict['keyspace'] = $this->keyspace;
    $dict['keyspaceProgress'] = $this->keyspaceProgress;
    $dict['priority'] = $this->priority;
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
  
  function getPrimaryKey() {
    return "taskId";
  }
  
  function getPrimaryKeyValue() {
    return $this->taskId;
  }
  
  function getId() {
    return $this->taskId;
  }
  
  function setId($id) {
    $this->taskId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getTaskName() {
    return $this->taskName;
  }
  
  function setTaskName($taskName) {
    $this->taskName = $taskName;
  }
  
  function getAttackCmd() {
    return $this->attackCmd;
  }
  
  function setAttackCmd($attackCmd) {
    $this->attackCmd = $attackCmd;
  }
  
  function getChunkTime() {
    return $this->chunkTime;
  }
  
  function setChunkTime($chunkTime) {
    $this->chunkTime = $chunkTime;
  }
  
  function getStatusTimer() {
    return $this->statusTimer;
  }
  
  function setStatusTimer($statusTimer) {
    $this->statusTimer = $statusTimer;
  }
  
  function getKeyspace() {
    return $this->keyspace;
  }
  
  function setKeyspace($keyspace) {
    $this->keyspace = $keyspace;
  }
  
  function getKeyspaceProgress() {
    return $this->keyspaceProgress;
  }
  
  function setKeyspaceProgress($keyspaceProgress) {
    $this->keyspaceProgress = $keyspaceProgress;
  }
  
  function getPriority() {
    return $this->priority;
  }
  
  function setPriority($priority) {
    $this->priority = $priority;
  }
  
  function getColor() {
    return $this->color;
  }
  
  function setColor($color) {
    $this->color = $color;
  }
  
  function getIsSmall() {
    return $this->isSmall;
  }
  
  function setIsSmall($isSmall) {
    $this->isSmall = $isSmall;
  }
  
  function getIsCpuTask() {
    return $this->isCpuTask;
  }
  
  function setIsCpuTask($isCpuTask) {
    $this->isCpuTask = $isCpuTask;
  }
  
  function getUseNewBench() {
    return $this->useNewBench;
  }
  
  function setUseNewBench($useNewBench) {
    $this->useNewBench = $useNewBench;
  }
  
  function getSkipKeyspace() {
    return $this->skipKeyspace;
  }
  
  function setSkipKeyspace($skipKeyspace) {
    $this->skipKeyspace = $skipKeyspace;
  }
  
  function getCrackerBinaryId() {
    return $this->crackerBinaryId;
  }
  
  function setCrackerBinaryId($crackerBinaryId) {
    $this->crackerBinaryId = $crackerBinaryId;
  }
  
  function getCrackerBinaryTypeId() {
    return $this->crackerBinaryTypeId;
  }
  
  function setCrackerBinaryTypeId($crackerBinaryTypeId) {
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
  }
  
  function getTaskWrapperId() {
    return $this->taskWrapperId;
  }
  
  function setTaskWrapperId($taskWrapperId) {
    $this->taskWrapperId = $taskWrapperId;
  }
  
  function getIsArchived() {
    return $this->isArchived;
  }
  
  function setIsArchived($isArchived) {
    $this->isArchived = $isArchived;
  }
  
  function getNotes() {
    return $this->notes;
  }
  
  function setNotes($notes) {
    $this->notes = $notes;
  }
  
  function getStaticChunks() {
    return $this->staticChunks;
  }
  
  function setStaticChunks($staticChunks) {
    $this->staticChunks = $staticChunks;
  }
  
  function getChunkSize() {
    return $this->chunkSize;
  }
  
  function setChunkSize($chunkSize) {
    $this->chunkSize = $chunkSize;
  }
  
  function getForcePipe() {
    return $this->forcePipe;
  }
  
  function setForcePipe($forcePipe) {
    $this->forcePipe = $forcePipe;
  }
  
  function getUsePreprocessor() {
    return $this->usePreprocessor;
  }
  
  function setUsePreprocessor($usePreprocessor) {
    $this->usePreprocessor = $usePreprocessor;
  }
  
  function getPreprocessorCommand() {
    return $this->preprocessorCommand;
  }
  
  function setPreprocessorCommand($preprocessorCommand) {
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
}
