<?php

namespace DBA;

class FileTask extends AbstractModel {
  private $fileTaskId;
  private $fileId;
  private $taskId;
  
  function __construct($fileTaskId, $fileId, $taskId) {
    $this->fileTaskId = $fileTaskId;
    $this->fileId = $fileId;
    $this->taskId = $taskId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['fileTaskId'] = $this->fileTaskId;
    $dict['fileId'] = $this->fileId;
    $dict['taskId'] = $this->taskId;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['fileTaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "fileTaskId"];
    $dict['fileId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "fileId"];
    $dict['taskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskId"];

    return $dict;
  }

  function getPrimaryKey() {
    return "fileTaskId";
  }
  
  function getPrimaryKeyValue() {
    return $this->fileTaskId;
  }
  
  function getId() {
    return $this->fileTaskId;
  }
  
  function setId($id) {
    $this->fileTaskId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getFileId() {
    return $this->fileId;
  }
  
  function setFileId($fileId) {
    $this->fileId = $fileId;
  }
  
  function getTaskId() {
    return $this->taskId;
  }
  
  function setTaskId($taskId) {
    $this->taskId = $taskId;
  }
  
  const FILE_TASK_ID = "fileTaskId";
  const FILE_ID = "fileId";
  const TASK_ID = "taskId";
}
