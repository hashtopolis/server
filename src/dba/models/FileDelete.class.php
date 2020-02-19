<?php

namespace DBA;

class FileDelete extends AbstractModel {
  private $fileDeleteId;
  private $filename;
  private $time;
  
  function __construct($fileDeleteId, $filename, $time) {
    $this->fileDeleteId = $fileDeleteId;
    $this->filename = $filename;
    $this->time = $time;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['fileDeleteId'] = $this->fileDeleteId;
    $dict['filename'] = $this->filename;
    $dict['time'] = $this->time;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "fileDeleteId";
  }
  
  function getPrimaryKeyValue() {
    return $this->fileDeleteId;
  }
  
  function getId() {
    return $this->fileDeleteId;
  }
  
  function setId($id) {
    $this->fileDeleteId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getFilename() {
    return $this->filename;
  }
  
  function setFilename($filename) {
    $this->filename = $filename;
  }
  
  function getTime() {
    return $this->time;
  }
  
  function setTime($time) {
    $this->time = $time;
  }
  
  const FILE_DELETE_ID = "fileDeleteId";
  const FILENAME = "filename";
  const TIME = "time";
}
