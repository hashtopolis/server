<?php

namespace DBA;

class FileDownload extends AbstractModel {
  private ?int $fileDownloadId;
  private ?int $time;
  private ?int $fileId;
  private ?int $status;
  
  function __construct(?int $fileDownloadId, ?int $time, ?int $fileId, ?int $status) {
    $this->fileDownloadId = $fileDownloadId;
    $this->time = $time;
    $this->fileId = $fileId;
    $this->status = $status;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['fileDownloadId'] = $this->fileDownloadId;
    $dict['time'] = $this->time;
    $dict['fileId'] = $this->fileId;
    $dict['status'] = $this->status;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['fileDownloadId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "fileDownloadId", "public" => False, "dba_mapping" => False];
    $dict['time'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "time", "public" => False, "dba_mapping" => False];
    $dict['fileId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "fileId", "public" => False, "dba_mapping" => False];
    $dict['status'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "status", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "fileDownloadId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->fileDownloadId;
  }
  
  function getId(): ?int {
    return $this->fileDownloadId;
  }
  
  function setId($id): void {
    $this->fileDownloadId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getTime(): ?int {
    return $this->time;
  }
  
  function setTime(?int $time): void {
    $this->time = $time;
  }
  
  function getFileId(): ?int {
    return $this->fileId;
  }
  
  function setFileId(?int $fileId): void {
    $this->fileId = $fileId;
  }
  
  function getStatus(): ?int {
    return $this->status;
  }
  
  function setStatus(?int $status): void {
    $this->status = $status;
  }
  
  const FILE_DOWNLOAD_ID = "fileDownloadId";
  const TIME = "time";
  const FILE_ID = "fileId";
  const STATUS = "status";

  const PERM_CREATE = "permFileDownloadCreate";
  const PERM_READ = "permFileDownloadRead";
  const PERM_UPDATE = "permFileDownloadUpdate";
  const PERM_DELETE = "permFileDownloadDelete";
}
