<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class CrackerBinary extends AbstractModel {
  private ?int $crackerBinaryId;
  private ?int $crackerBinaryTypeId;
  private ?string $version;
  private ?string $downloadUrl;
  private ?string $binaryName;
  private ?string $filename;
  private ?int $accessGroupId;
  
  function __construct(?int $crackerBinaryId, ?int $crackerBinaryTypeId, ?string $version, ?string $downloadUrl, ?string $binaryName, ?string $filename, ?int $accessGroupId) {
    $this->crackerBinaryId = $crackerBinaryId;
    $this->crackerBinaryTypeId = $crackerBinaryTypeId;
    $this->version = $version;
    $this->downloadUrl = $downloadUrl;
    $this->binaryName = $binaryName;
    $this->filename = $filename;
    $this->accessGroupId = $accessGroupId;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['crackerBinaryId'] = $this->crackerBinaryId;
    $dict['crackerBinaryTypeId'] = $this->crackerBinaryTypeId;
    $dict['version'] = $this->version;
    $dict['downloadUrl'] = $this->downloadUrl;
    $dict['binaryName'] = $this->binaryName;
    $dict['filename'] = $this->filename;
    $dict['accessGroupId'] = $this->accessGroupId;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['crackerBinaryId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "crackerBinaryId", "public" => False, "dba_mapping" => False];
    $dict['crackerBinaryTypeId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "crackerBinaryTypeId", "public" => False, "dba_mapping" => False];
    $dict['version'] = ['read_only' => False, "type" => "str(20)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "version", "public" => False, "dba_mapping" => False, "description" => "Version of the cracker this binary holds."];
    $dict['downloadUrl'] = ['read_only' => False, "type" => "str(255)", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "downloadUrl", "public" => False, "dba_mapping" => False, "description" => "External http or https url the agent downloads the binary archive from. The server keeps a local copy for later analysis: it is fetched on creation, and changing the url fetches it again, the change being rejected when the download fails or the archive is not a valid 7z file. Mutually exclusive with sourceType, which points this url at the download endpoint of this server and then makes it unchangeable."];
    $dict['binaryName'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "binaryName", "public" => False, "dba_mapping" => False, "description" => "Name of the executable inside the archive that the agent runs."];
    $dict['filename'] = ['read_only' => True, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => True, "private" => False, "alias" => "filename", "public" => False, "dba_mapping" => False, "description" => "Name of the locally stored 7z archive, null while the binary is downloaded from downloadUrl. Cannot be set."];
    $dict['accessGroupId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "accessGroupId", "public" => False, "dba_mapping" => False, "description" => "Access group this cracker binary belongs to. Required on creation, where the requesting user must be a member, and changeable only by a user who belongs to both the current and the new group."];

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
  
  function getFilename(): ?string {
    return $this->filename;
  }
  
  function setFilename(?string $filename): void {
    $this->filename = $filename;
  }
  
  function getAccessGroupId(): ?int {
    return $this->accessGroupId;
  }
  
  function setAccessGroupId(?int $accessGroupId): void {
    $this->accessGroupId = $accessGroupId;
  }
  
  const CRACKER_BINARY_ID = "crackerBinaryId";
  const CRACKER_BINARY_TYPE_ID = "crackerBinaryTypeId";
  const VERSION = "version";
  const DOWNLOAD_URL = "downloadUrl";
  const BINARY_NAME = "binaryName";
  const FILENAME = "filename";
  const ACCESS_GROUP_ID = "accessGroupId";

  const PERM_CREATE = "permCrackerBinaryCreate";
  const PERM_READ = "permCrackerBinaryRead";
  const PERM_UPDATE = "permCrackerBinaryUpdate";
  const PERM_DELETE = "permCrackerBinaryDelete";
}
