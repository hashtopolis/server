<?php

namespace DBA;

class File extends AbstractModel {
  private $fileId;
  private $filename;
  private $size;
  private $isSecret;
  private $fileType;
  
  function __construct($fileId, $filename, $size, $isSecret, $fileType) {
    $this->fileId = $fileId;
    $this->filename = $filename;
    $this->size = $size;
    $this->isSecret = $isSecret;
    $this->fileType = $fileType;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['fileId'] = $this->fileId;
    $dict['filename'] = $this->filename;
    $dict['size'] = $this->size;
    $dict['isSecret'] = $this->isSecret;
    $dict['fileType'] = $this->fileType;
    
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
  
  function getFilename(){
    return $this->filename;
  }
  
  function setFilename($filename){
    $this->filename = $filename;
  }
  
  function getSize(){
    return $this->size;
  }
  
  function setSize($size){
    $this->size = $size;
  }
  
  function getIsSecret(){
    return $this->isSecret;
  }
  
  function setIsSecret($isSecret){
    $this->isSecret = $isSecret;
  }
  
  function getFileType(){
    return $this->fileType;
  }
  
  function setFileType($fileType){
    $this->fileType = $fileType;
  }

  const FILE_ID = "fileId";
  const FILENAME = "filename";
  const SIZE = "size";
  const IS_SECRET = "isSecret";
  const FILE_TYPE = "fileType";
}
