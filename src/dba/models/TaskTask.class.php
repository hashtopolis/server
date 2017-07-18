<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class TaskTask extends AbstractModel {
  private $taskTaskId;
  private $taskId;
  private $subtaskId;
  
  function __construct($taskTaskId, $taskId, $subtaskId) {
    $this->taskTaskId = $taskTaskId;
    $this->taskId = $taskId;
    $this->subtaskId = $subtaskId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['taskTaskId'] = $this->taskTaskId;
    $dict['taskId'] = $this->taskId;
    $dict['subtaskId'] = $this->subtaskId;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "taskTaskId";
  }
  
  function getPrimaryKeyValue() {
    return $this->taskTaskId;
  }
  
  function getId() {
    return $this->taskTaskId;
  }
  
  function setId($id) {
    $this->taskTaskId = $id;
  }
  
  function getTaskId(){
    return $this->taskId;
  }
  
  function setTaskId($taskId){
    $this->taskId = $taskId;
  }
  
  function getSubtaskId(){
    return $this->subtaskId;
  }
  
  function setSubtaskId($subtaskId){
    $this->subtaskId = $subtaskId;
  }

  const TASK_TASK_ID = "taskTaskId";
  const TASK_ID = "taskId";
  const SUBTASK_ID = "subtaskId";
}
