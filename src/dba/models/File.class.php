<?php

namespace DBA;

class File extends AbstractModel {
  private ?int $fileId;
  private ?string $filename;
  private ?int $size;
  private ?int $isSecret;
  private ?int $fileType;
  private ?int $accessGroupId;
  private ?int $lineCount;
  
  function __construct(?int $fileId, ?string $filename, ?int $size, ?int $isSecret, ?int $fileType, ?int $accessGroupId, ?int $lineCount) {
    $this->fileId = $fileId;
    $this->filename = $filename;
    $this->size = $size;
    $this->isSecret = $isSecret;
    $this->fileType = $fileType;
    $this->accessGroupId = $accessGroupId;
    $this->lineCount = $lineCount;
  }
  
  function getKeyValueDict(): array {
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
  
  static function getFeatures(): array {
    $dict = array();
    $dict['fileId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "fileId", "public" => False];
    $dict['filename'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "filename", "public" => False];
    $dict['size'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "size", "public" => False];
    $dict['isSecret'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isSecret", "public" => False];
    $dict['fileType'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "fileType", "public" => False];
    $dict['accessGroupId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "accessGroupId", "public" => False];
    $dict['lineCount'] = ['read_only' => True, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "lineCount", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "fileId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->fileId;
  }
  
  function getId(): ?int {
    return $this->fileId;
  }
  
  function setId($id): void {
    $this->fileId = $id;
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
  
  function getSize(): ?int {
    return $this->size;
  }
  
  function setSize(?int $size): void {
    $this->size = $size;
  }
  
  function getIsSecret(): ?int {
    return $this->isSecret;
  }
  
  function setIsSecret(?int $isSecret): void {
    $this->isSecret = $isSecret;
  }
  
  function getFileType(): ?int {
    return $this->fileType;
  }
  
  function setFileType(?int $fileType): void {
    $this->fileType = $fileType;
  }
  
  function getAccessGroupId(): ?int {
    return $this->accessGroupId;
  }
  
  function setAccessGroupId(?int $accessGroupId): void {
    $this->accessGroupId = $accessGroupId;
  }
  
  function getLineCount(): ?int {
    return $this->lineCount;
  }
  
  function setLineCount(?int $lineCount): void {
    $this->lineCount = $lineCount;
  }
  
  const FILE_ID = "fileId";
  const FILENAME = "filename";
  const SIZE = "size";
  const IS_SECRET = "isSecret";
  const FILE_TYPE = "fileType";
  const ACCESS_GROUP_ID = "accessGroupId";
  const LINE_COUNT = "lineCount";

  const PERM_CREATE = "permFileCreate";
  const PERM_READ = "permFileRead";
  const PERM_UPDATE = "permFileUpdate";
  const PERM_DELETE = "permFileDelete";
}
