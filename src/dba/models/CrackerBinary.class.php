<?php

namespace DBA;

class CrackerBinary extends AbstractModel {
  private ?int $crackerBinaryId;
  private ?int $crackerBinaryTypeId;
  private ?string $version;
  private ?string $downloadUrl;
  private ?string $binaryName;
  
  function __construct(?int $crackerBinaryId, ?int $crackerBinaryTypeId, ?string $version, ?string $downloadUrl, ?string $binaryName) {
    $this->crackerBinaryId = $crackerBinaryId;
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
    $this->version = $version;
    $this->downloadUrl = $downloadUrl;
    $this->binaryName = $binaryName;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['crackerBinaryId'] = $this->crackerBinaryId;
    $dict['crackerBinaryTypeId'] = $this->crackerBinaryTypeId;
    $dict['version'] = $this->version;
    $dict['downloadUrl'] = $this->downloadUrl;
    $dict['binaryName'] = $this->binaryName;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['crackerBinaryId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "crackerBinaryId", "public" => False];
    $dict['crackerBinaryTypeId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "crackerBinaryTypeId", "public" => False];
    $dict['version'] = ['read_only' => False, "type" => "str(20)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "version", "public" => False];
    $dict['downloadUrl'] = ['read_only' => False, "type" => "str(150)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "downloadUrl", "public" => False];
    $dict['binaryName'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "binaryName", "public" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "crackerBinaryId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->crackerBinaryId;
  }
  
  function getId(): ?int {
    return $this->crackerBinaryId;
  }
  
  function setId($id): void {
    $this->crackerBinaryId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getCrackerBinaryTypeId(): ?int {
    return $this->crackerBinaryTypeId;
  }
  
  function setCrackerBinaryTypeId(?int $crackerBinaryTypeId): void {
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
  }
  
  function getVersion(): ?string {
    return $this->version;
  }
  
  function setVersion(?string $version): void {
    $this->version = $version;
  }
  
  function getDownloadUrl(): ?string {
    return $this->downloadUrl;
  }
  
  function setDownloadUrl(?string $downloadUrl): void {
    $this->downloadUrl = $downloadUrl;
  }
  
  function getBinaryName(): ?string {
    return $this->binaryName;
  }
  
  function setBinaryName(?string $binaryName): void {
    $this->binaryName = $binaryName;
  }
  
  const CRACKER_BINARY_ID = "crackerBinaryId";
  const CRACKER_BINARY_TYPE_ID = "crackerBinaryTypeId";
  const VERSION = "version";
  const DOWNLOAD_URL = "downloadUrl";
  const BINARY_NAME = "binaryName";

  const PERM_CREATE = "permCrackerBinaryCreate";
  const PERM_READ = "permCrackerBinaryRead";
  const PERM_UPDATE = "permCrackerBinaryUpdate";
  const PERM_DELETE = "permCrackerBinaryDelete";
}
