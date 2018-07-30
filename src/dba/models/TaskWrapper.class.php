<?php

namespace DBA;

class TaskWrapper extends AbstractModel {
  private $taskWrapperId;
  private $priority;
  private $taskType;
  private $hashlistId;
  private $accessGroupId;
  private $taskWrapperName;
  private $isArchived;
  
  function __construct($taskWrapperId, $priority, $taskType, $hashlistId, $accessGroupId, $taskWrapperName, $isArchived) {
    $this->taskWrapperId = $taskWrapperId;
    $this->priority = $priority;
    $this->taskType = $taskType;
    $this->hashlistId = $hashlistId;
    $this->accessGroupId = $accessGroupId;
    $this->taskWrapperName = $taskWrapperName;
    $this->isArchived = $isArchived;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['taskWrapperId'] = $this->taskWrapperId;
    $dict['priority'] = $this->priority;
    $dict['taskType'] = $this->taskType;
    $dict['hashlistId'] = $this->hashlistId;
    $dict['accessGroupId'] = $this->accessGroupId;
    $dict['taskWrapperName'] = $this->taskWrapperName;
    $dict['isArchived'] = $this->isArchived;
    
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
  
  function getAccessGroupId(){
    return $this->accessGroupId;
  }
  
  function setAccessGroupId($accessGroupId){
    $this->accessGroupId = $accessGroupId;
  }
  
  function getTaskWrapperName(){
    return $this->taskWrapperName;
  }
  
  function setTaskWrapperName($taskWrapperName){
    $this->taskWrapperName = $taskWrapperName;
  }
  
  function getIsArchived(){
    return $this->isArchived;
  }
  
  function setIsArchived($isArchived){
    $this->isArchived = $isArchived;
  }

  const TASK_WRAPPER_ID = "taskWrapperId";
  const PRIORITY = "priority";
  const TASK_TYPE = "taskType";
  const HASHLIST_ID = "hashlistId";
  const ACCESS_GROUP_ID = "accessGroupId";
  const TASK_WRAPPER_NAME = "taskWrapperName";
  const IS_ARCHIVED = "isArchived";
}
