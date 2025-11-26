<?php

namespace DBA;

class Preprocessor extends AbstractModel {
  private ?int $preprocessorId;
  private ?string $name;
  private ?string $url;
  private ?string $binaryName;
  private ?string $keyspaceCommand;
  private ?string $skipCommand;
  private ?string $limitCommand;
  
  function __construct(?int $preprocessorId, ?string $name, ?string $url, ?string $binaryName, ?string $keyspaceCommand, ?string $skipCommand, ?string $limitCommand) {
    $this->preprocessorId = $preprocessorId;
    $this->name = $name;
    $this->url = $url;
    $this->binaryName = $binaryName;
    $this->keyspaceCommand = $keyspaceCommand;
    $this->skipCommand = $skipCommand;
    $this->limitCommand = $limitCommand;
  }
  
  function getKeyValueDict(): array {
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
  
  static function getFeatures(): array {
    $dict = array();
    $dict['preprocessorId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "preprocessorId", "public" => False, "dba_mapping" => False];
    $dict['name'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "name", "public" => False, "dba_mapping" => False];
    $dict['url'] = ['read_only' => False, "type" => "str(512)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "url", "public" => False, "dba_mapping" => False];
    $dict['binaryName'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "binaryName", "public" => False, "dba_mapping" => False];
    $dict['keyspaceCommand'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "keyspaceCommand", "public" => False, "dba_mapping" => False];
    $dict['skipCommand'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "skipCommand", "public" => False, "dba_mapping" => False];
    $dict['limitCommand'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "limitCommand", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "preprocessorId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->preprocessorId;
  }
  
  function getId(): ?int {
    return $this->preprocessorId;
  }
  
  function setId($id): void {
    $this->preprocessorId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getName(): ?string {
    return $this->name;
  }
  
  function setName(?string $name): void {
    $this->name = $name;
  }
  
  function getUrl(): ?string {
    return $this->url;
  }
  
  function setUrl(?string $url): void {
    $this->url = $url;
  }
  
  function getBinaryName(): ?string {
    return $this->binaryName;
  }
  
  function setBinaryName(?string $binaryName): void {
    $this->binaryName = $binaryName;
  }
  
  function getKeyspaceCommand(): ?string {
    return $this->keyspaceCommand;
  }
  
  function setKeyspaceCommand(?string $keyspaceCommand): void {
    $this->keyspaceCommand = $keyspaceCommand;
  }
  
  function getSkipCommand(): ?string {
    return $this->skipCommand;
  }
  
  function setSkipCommand(?string $skipCommand): void {
    $this->skipCommand = $skipCommand;
  }
  
  function getLimitCommand(): ?string {
    return $this->limitCommand;
  }
  
  function setLimitCommand(?string $limitCommand): void {
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
