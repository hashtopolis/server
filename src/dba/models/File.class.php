<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class File extends AbstractModel {
  private $fileId;
  private $filename;
  private $size;
  private $secret;
  private $fileType;
  
  function __construct($fileId, $filename, $size, $secret, $fileType) {
    $this->fileId = $fileId;
    $this->filename = $filename;
    $this->size = $size;
    $this->secret = $secret;
    $this->fileType = $fileType;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['fileId'] = $this->fileId;
    $dict['filename'] = $this->filename;
    $dict['size'] = $this->size;
    $dict['secret'] = $this->secret;
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
  
  function getSecret(){
    return $this->secret;
  }
  
  function setSecret($secret){
    $this->secret = $secret;
  }
  
  function getFileType(){
    return $this->fileType;
  }
  
  function setFileType($fileType){
    $this->fileType = $fileType;
  }

  public const FILE_ID = "fileId";
  public const FILENAME = "filename";
  public const SIZE = "size";
  public const SECRET = "secret";
  public const FILE_TYPE = "fileType";
}
