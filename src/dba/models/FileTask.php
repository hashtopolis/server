<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class FileTask extends AbstractModel {
  private ?int $fileTaskId;
  private ?int $fileId;
  private ?int $taskId;
  
  function __construct(?int $fileTaskId, ?int $fileId, ?int $taskId) {
    $this->fileTaskId = $fileTaskId;
    $this->fileId = $fileId;
    $this->taskId = $taskId;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['fileTaskId'] = $this->fileTaskId;
    $dict['fileId'] = $this->fileId;
    $dict['taskId'] = $this->taskId;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['fileTaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "fileTaskId", "public" => False, "dba_mapping" => False];
    $dict['fileId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "fileId", "public" => False, "dba_mapping" => False];
    $dict['taskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "taskId", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "fileTaskId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->fileTaskId;
  }
  
  function getId(): ?int {
    return $this->fileTaskId;
  }
  
  function setId($id): void {
    $this->fileTaskId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getFileId(): ?int {
    return $this->fileId;
  }
  
  function setFileId(?int $fileId): void {
    $this->fileId = $fileId;
  }
  
  function getTaskId(): ?int {
    return $this->taskId;
  }
  
  function setTaskId(?int $taskId): void {
    $this->taskId = $taskId;
  }
  
  const FILE_TASK_ID = "fileTaskId";
  const FILE_ID = "fileId";
  const TASK_ID = "taskId";

  const PERM_CREATE = "permFileTaskCreate";
  const PERM_READ = "permFileTaskRead";
  const PERM_UPDATE = "permFileTaskUpdate";
  const PERM_DELETE = "permFileTaskDelete";
}
