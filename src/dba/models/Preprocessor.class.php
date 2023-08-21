<?php

namespace DBA;

class Preprocessor extends AbstractModel {
  private $preprocessorId;
  private $name;
  private $url;
  private $binaryName;
  private $keyspaceCommand;
  private $skipCommand;
  private $limitCommand;
  
  function __construct($preprocessorId, $name, $url, $binaryName, $keyspaceCommand, $skipCommand, $limitCommand) {
    $this->preprocessorId = $preprocessorId;
    $this->name = $name;
    $this->url = $url;
    $this->binaryName = $binaryName;
    $this->keyspaceCommand = $keyspaceCommand;
    $this->skipCommand = $skipCommand;
    $this->limitCommand = $limitCommand;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['preprocessorId'] = $this->preprocessorId;
    $dict['name'] = $this->name;
    $dict['url'] = $this->url;
    $dict['binaryName'] = $this->binaryName;
    $dict['keyspaceCommand'] = $this->keyspaceCommand;
    $dict['skipCommand'] = $this->skipCommand;
    $dict['limitCommand'] = $this->limitCommand;
    
    return $dict;
  }
  
  static function getFeatures() {
    $dict = array();
    $dict['preprocessorId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "preprocessorId"];
    $dict['name'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "name"];
    $dict['url'] = ['read_only' => False, "type" => "str(512)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "url"];
    $dict['binaryName'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "binaryName"];
    $dict['keyspaceCommand'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "keyspaceCommand"];
    $dict['skipCommand'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "skipCommand"];
    $dict['limitCommand'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "limitCommand"];

    return $dict;
  }

  function getPrimaryKey() {
    return "preprocessorId";
  }
  
  function getPrimaryKeyValue() {
    return $this->preprocessorId;
  }
  
  function getId() {
    return $this->preprocessorId;
  }
  
  function setId($id) {
    $this->preprocessorId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getName() {
    return $this->name;
  }
  
  function setName($name) {
    $this->name = $name;
  }
  
  function getUrl() {
    return $this->url;
  }
  
  function setUrl($url) {
    $this->url = $url;
  }
  
  function getBinaryName() {
    return $this->binaryName;
  }
  
  function setBinaryName($binaryName) {
    $this->binaryName = $binaryName;
  }
  
  function getKeyspaceCommand() {
    return $this->keyspaceCommand;
  }
  
  function setKeyspaceCommand($keyspaceCommand) {
    $this->keyspaceCommand = $keyspaceCommand;
  }
  
  function getSkipCommand() {
    return $this->skipCommand;
  }
  
  function setSkipCommand($skipCommand) {
    $this->skipCommand = $skipCommand;
  }
  
  function getLimitCommand() {
    return $this->limitCommand;
  }
  
  function setLimitCommand($limitCommand) {
    $this->limitCommand = $limitCommand;
  }
  
  const PREPROCESSOR_ID = "preprocessorId";
  const NAME = "name";
  const URL = "url";
  const BINARY_NAME = "binaryName";
  const KEYSPACE_COMMAND = "keyspaceCommand";
  const SKIP_COMMAND = "skipCommand";
  const LIMIT_COMMAND = "limitCommand";

  const PERM_CREATE = "permPreprocessorCreate";
  const PERM_READ = "permPreprocessorRead";
  const PERM_UPDATE = "permPreprocessorUpdate";
  const PERM_DELETE = "permPreprocessorDelete";
}
