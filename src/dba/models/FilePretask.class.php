<?php

namespace DBA;

class FilePretask extends AbstractModel {
  private $filePretaskId;
  private $fileId;
  private $pretaskId;
  
  function __construct($filePretaskId, $fileId, $pretaskId) {
    $this->filePretaskId = $filePretaskId;
    $this->fileId = $fileId;
    $this->pretaskId = $pretaskId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['filePretaskId'] = $this->filePretaskId;
    $dict['fileId'] = $this->fileId;
    $dict['pretaskId'] = $this->pretaskId;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "filePretaskId";
  }
  
  function getPrimaryKeyValue() {
    return $this->filePretaskId;
  }
  
  function getId() {
    return $this->filePretaskId;
  }
  
  function setId($id) {
    $this->filePretaskId = $id;
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
  
  function getPretaskId() {
    return $this->pretaskId;
  }
  
  function setPretaskId($pretaskId) {
    $this->pretaskId = $pretaskId;
  }
  
  const FILE_PRETASK_ID = "filePretaskId";
  const FILE_ID = "fileId";
  const PRETASK_ID = "pretaskId";
}
