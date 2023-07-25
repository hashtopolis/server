<?php

namespace DBA;

class File extends AbstractModel {
  private $fileId;
  private $filename;
  private $size;
  private $isSecret;
  private $fileType;
  private $accessGroupId;
  private $lineCount;
  
  function __construct($fileId, $filename, $size, $isSecret, $fileType, $accessGroupId, $lineCount) {
    $this->fileId = $fileId;
    $this->filename = $filename;
    $this->size = $size;
    $this->isSecret = $isSecret;
    $this->fileType = $fileType;
    $this->accessGroupId = $accessGroupId;
    $this->lineCount = $lineCount;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['fileId'] = $this->fileId;
    $dict['filename'] = $this->filename;
    $dict['size'] = $this->size;
    $dict['isSecret'] = $this->isSecret;
    $dict['fileType'] = $this->fileType;
    $dict['accessGroupId'] = $this->accessGroupId;
    $dict['lineCount'] = $this->lineCount;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['fileId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "fileId"];
    $dict['filename'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "filename"];
    $dict['size'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "size"];
    $dict['isSecret'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isSecret"];
    $dict['fileType'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "fileType"];
    $dict['accessGroupId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "accessGroupId"];
    $dict['lineCount'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lineCount"];

    return $dict;
  }

  function getPrimaryKey() {
    return "fileId";
  }
  
  function getPrimaryKeyValue() {
    return $this->fileId;
  }
  
  function getId() {
    return $this->fileId;
  }
  
  function setId($id) {
    $this->fileId = $id;
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
  
  function getSize() {
    return $this->size;
  }
  
  function setSize($size) {
    $this->size = $size;
  }
  
  function getIsSecret() {
    return $this->isSecret;
  }
  
  function setIsSecret($isSecret) {
    $this->isSecret = $isSecret;
  }
  
  function getFileType() {
    return $this->fileType;
  }
  
  function setFileType($fileType) {
    $this->fileType = $fileType;
  }
  
  function getAccessGroupId() {
    return $this->accessGroupId;
  }
  
  function setAccessGroupId($accessGroupId) {
    $this->accessGroupId = $accessGroupId;
  }
  
  function getLineCount() {
    return $this->lineCount;
  }
  
  function setLineCount($lineCount) {
    $this->lineCount = $lineCount;
  }
  
  const FILE_ID = "fileId";
  const FILENAME = "filename";
  const SIZE = "size";
  const IS_SECRET = "isSecret";
  const FILE_TYPE = "fileType";
  const ACCESS_GROUP_ID = "accessGroupId";
  const LINE_COUNT = "lineCount";
}
