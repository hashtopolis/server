<?php

namespace DBA;

class FileDelete extends AbstractModel {
  private ?int $fileDeleteId;
  private ?string $filename;
  private ?int $time;
  
  function __construct(?int $fileDeleteId, ?string $filename, ?int $time) {
    $this->fileDeleteId = $fileDeleteId;
    $this->filename = $filename;
    $this->time = $time;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['fileDeleteId'] = $this->fileDeleteId;
    $dict['filename'] = $this->filename;
    $dict['time'] = $this->time;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['fileDeleteId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "fileDeleteId", "public" => False];
    $dict['filename'] = ['read_only' => True, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "filename", "public" => False];
    $dict['time'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "time", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "fileDeleteId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->fileDeleteId;
  }
  
  function getId(): ?int {
    return $this->fileDeleteId;
  }
  
  function setId($id): void {
    $this->fileDeleteId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getFilename(): ?string {
    return $this->filename;
  }
  
  function setFilename(?string $filename): void {
    $this->filename = $filename;
  }
  
  function getTime(): ?int {
    return $this->time;
  }
  
  function setTime(?int $time): void {
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
