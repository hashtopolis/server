<?php

namespace DBA;

class FilePretask extends AbstractModel {
  private ?int $filePretaskId;
  private ?int $fileId;
  private ?int $pretaskId;
  
  function __construct(?int $filePretaskId, ?int $fileId, ?int $pretaskId) {
    $this->filePretaskId = $filePretaskId;
    $this->fileId = $fileId;
    $this->pretaskId = $pretaskId;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['filePretaskId'] = $this->filePretaskId;
    $dict['fileId'] = $this->fileId;
    $dict['pretaskId'] = $this->pretaskId;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['filePretaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "filePretaskId", "public" => False, "dba_mapping" => False];
    $dict['fileId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "fileId", "public" => False, "dba_mapping" => False];
    $dict['pretaskId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "pretaskId", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "filePretaskId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->filePretaskId;
  }
  
  function getId(): ?int {
    return $this->filePretaskId;
  }
  
  function setId($id): void {
    $this->filePretaskId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getFileId(): ?int {
    return $this->fileId;
  }
  
  function setFileId(?int $fileId): void {
    $this->fileId = $fileId;
  }
  
  function getPretaskId(): ?int {
    return $this->pretaskId;
  }
  
  function setPretaskId(?int $pretaskId): void {
    $this->pretaskId = $pretaskId;
  }
  
  const FILE_PRETASK_ID = "filePretaskId";
  const FILE_ID = "fileId";
  const PRETASK_ID = "pretaskId";

  const PERM_CREATE = "permFilePretaskCreate";
  const PERM_READ = "permFilePretaskRead";
  const PERM_UPDATE = "permFilePretaskUpdate";
  const PERM_DELETE = "permFilePretaskDelete";
}
