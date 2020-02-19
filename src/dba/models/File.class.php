<?php

namespace DBA;

class File extends AbstractModel {
  private $fileId;
  private $filename;
  private $size;
  private $isSecret;
  private $fileType;
  private $accessGroupId;
  
  function __construct($fileId, $filename, $size, $isSecret, $fileType, $accessGroupId) {
    $this->fileId = $fileId;
    $this->filename = $filename;
    $this->size = $size;
    $this->isSecret = $isSecret;
    $this->fileType = $fileType;
    $this->accessGroupId = $accessGroupId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['fileId'] = $this->fileId;
    $dict['filename'] = $this->filename;
    $dict['size'] = $this->size;
    $dict['isSecret'] = $this->isSecret;
    $dict['fileType'] = $this->fileType;
    $dict['accessGroupId'] = $this->accessGroupId;
    
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
  
  const FILE_ID = "fileId";
  const FILENAME = "filename";
  const SIZE = "size";
  const IS_SECRET = "isSecret";
  const FILE_TYPE = "fileType";
  const ACCESS_GROUP_ID = "accessGroupId";
}
