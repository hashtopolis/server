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
  private $taskWrapperId;
  
  function __construct($taskId, $taskName, $attackCmd, $chunkTime, $statusTimer, $keyspace, $keyspaceProgress, $priority, $color, $isSmall, $isCpuTask, $useNewBench, $skipKeyspace, $crackerBinaryId, $taskWrapperId) {
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
    $this->taskWrapperId = $taskWrapperId;
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
    $dict['taskWrapperId'] = $this->taskWrapperId;
    
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
  
  function getTaskName(){
    return $this->taskName;
  }
  
  function setTaskName($taskName){
    $this->taskName = $taskName;
  }
  
  function getAttackCmd(){
    return $this->attackCmd;
  }
  
  function setAttackCmd($attackCmd){
    $this->attackCmd = $attackCmd;
  }
  
  function getChunkTime(){
    return $this->chunkTime;
  }
  
  function setChunkTime($chunkTime){
    $this->chunkTime = $chunkTime;
  }
  
  function getStatusTimer(){
    return $this->statusTimer;
  }
  
  function setStatusTimer($statusTimer){
    $this->statusTimer = $statusTimer;
  }
  
  function getKeyspace(){
    return $this->keyspace;
  }
  
  function setKeyspace($keyspace){
    $this->keyspace = $keyspace;
  }
  
  function getKeyspaceProgress(){
    return $this->keyspaceProgress;
  }
  
  function setKeyspaceProgress($keyspaceProgress){
    $this->keyspaceProgress = $keyspaceProgress;
  }
  
  function getPriority(){
    return $this->priority;
  }
  
  function setPriority($priority){
    $this->priority = $priority;
  }
  
  function getColor(){
    return $this->color;
  }
  
  function setColor($color){
    $this->color = $color;
  }
  
  function getIsSmall(){
    return $this->isSmall;
  }
  
  function setIsSmall($isSmall){
    $this->isSmall = $isSmall;
  }
  
  function getIsCpuTask(){
    return $this->isCpuTask;
  }
  
  function setIsCpuTask($isCpuTask){
    $this->isCpuTask = $isCpuTask;
  }
  
  function getUseNewBench(){
    return $this->useNewBench;
  }
  
  function setUseNewBench($useNewBench){
    $this->useNewBench = $useNewBench;
  }
  
  function getSkipKeyspace(){
    return $this->skipKeyspace;
  }
  
  function setSkipKeyspace($skipKeyspace){
    $this->skipKeyspace = $skipKeyspace;
  }
  
  function getCrackerBinaryId(){
    return $this->crackerBinaryId;
  }
  
  function setCrackerBinaryId($crackerBinaryId){
    $this->crackerBinaryId = $crackerBinaryId;
  }
  
  function getTaskWrapperId(){
    return $this->taskWrapperId;
  }
  
  function setTaskWrapperId($taskWrapperId){
    $this->taskWrapperId = $taskWrapperId;
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
  const TASK_WRAPPER_ID = "taskWrapperId";
}
