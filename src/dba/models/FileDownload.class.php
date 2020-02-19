<?php

namespace DBA;

class FileDownload extends AbstractModel {
  private $fileDownloadId;
  private $time;
  private $fileId;
  private $status;
  
  function __construct($fileDownloadId, $time, $fileId, $status) {
    $this->fileDownloadId = $fileDownloadId;
    $this->time = $time;
    $this->fileId = $fileId;
    $this->status = $status;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['fileDownloadId'] = $this->fileDownloadId;
    $dict['time'] = $this->time;
    $dict['fileId'] = $this->fileId;
    $dict['status'] = $this->status;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "fileDownloadId";
  }
  
  function getPrimaryKeyValue() {
    return $this->fileDownloadId;
  }
  
  function getId() {
    return $this->fileDownloadId;
  }
  
  function setId($id) {
    $this->fileDownloadId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getTime() {
    return $this->time;
  }
  
  function setTime($time) {
    $this->time = $time;
  }
  
  function getFileId() {
    return $this->fileId;
  }
  
  function setFileId($fileId) {
    $this->fileId = $fileId;
  }
  
  function getStatus() {
    return $this->status;
  }
  
  function setStatus($status) {
    $this->status = $status;
  }
  
  const FILE_DOWNLOAD_ID = "fileDownloadId";
  const TIME = "time";
  const FILE_ID = "fileId";
  const STATUS = "status";
}
