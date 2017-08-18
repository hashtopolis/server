<?php

namespace DBA;

class TaskWrapper extends AbstractModel {
  private $taskWrapperId;
  private $priority;
  private $taskType;
  private $hashlistId;
  private $groupId;
  
  function __construct($taskWrapperId, $priority, $taskType, $hashlistId, $groupId) {
    $this->taskWrapperId = $taskWrapperId;
    $this->priority = $priority;
    $this->taskType = $taskType;
    $this->hashlistId = $hashlistId;
    $this->groupId = $groupId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['taskWrapperId'] = $this->taskWrapperId;
    $dict['priority'] = $this->priority;
    $dict['taskType'] = $this->taskType;
    $dict['hashlistId'] = $this->hashlistId;
    $dict['groupId'] = $this->groupId;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "taskWrapperId";
  }
  
  function getPrimaryKeyValue() {
    return $this->taskWrapperId;
  }
  
  function getId() {
    return $this->taskWrapperId;
  }
  
  function setId($id) {
    $this->taskWrapperId = $id;
  }
  
  function getPriority(){
    return $this->priority;
  }
  
  function setPriority($priority){
    $this->priority = $priority;
  }
  
  function getTaskType(){
    return $this->taskType;
  }
  
  function setTaskType($taskType){
    $this->taskType = $taskType;
  }
  
  function getHashlistId(){
    return $this->hashlistId;
  }
  
  function setHashlistId($hashlistId){
    $this->hashlistId = $hashlistId;
  }
  
  function getGroupId(){
    return $this->groupId;
  }
  
  function setGroupId($groupId){
    $this->groupId = $groupId;
  }

  const TASK_WRAPPER_ID = "taskWrapperId";
  const PRIORITY = "priority";
  const TASK_TYPE = "taskType";
  const HASHLIST_ID = "hashlistId";
  const GROUP_ID = "groupId";
}
