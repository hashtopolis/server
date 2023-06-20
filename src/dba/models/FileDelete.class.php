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
  
  static function getFeatures() {
    $dict = array();
    $dict['fileDeleteId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "fileDeleteId"];
    $dict['filename'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "filename"];
    $dict['time'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "time"];

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

  const PERM_CREATE = "permFileDeleteCreate";
  const PERM_READ = "permFileDeleteRead";
  const PERM_UPDATE = "permFileDeleteUpdate";
  const PERM_DELETE = "permFileDeleteDelete";
}
