<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class AgentBinary extends AbstractModel {
  private $agentBinaryId;
  private $language;
  private $operatingSystems;
  private $filename;
  
  function __construct($agentBinaryId, $language, $operatingSystems, $filename) {
    $this->agentBinaryId = $agentBinaryId;
    $this->language = $language;
    $this->operatingSystems = $operatingSystems;
    $this->filename = $filename;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['agentBinaryId'] = $this->agentBinaryId;
    $dict['language'] = $this->language;
    $dict['operatingSystems'] = $this->operatingSystems;
    $dict['filename'] = $this->filename;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "agentBinaryId";
  }
  
  function getPrimaryKeyValue() {
    return $this->agentBinaryId;
  }
  
  function getId() {
    return $this->agentBinaryId;
  }
  
  function setId($id) {
    $this->agentBinaryId = $id;
  }
  
  function getLanguage(){
    return $this->language;
  }
  
  function setLanguage($language){
    $this->language = $language;
  }
  
  function getOperatingSystems(){
    return $this->operatingSystems;
  }
  
  function setOperatingSystems($operatingSystems){
    $this->operatingSystems = $operatingSystems;
  }
  
  function getFilename(){
    return $this->filename;
  }
  
  function setFilename($filename){
    $this->filename = $filename;
  }
}
