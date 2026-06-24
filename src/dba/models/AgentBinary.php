<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class AgentBinary extends AbstractModel {
  private ?int $agentBinaryId;
  private ?string $binaryType;
  private ?string $version;
  private ?string $operatingSystems;
  private ?string $filename;
  private ?string $updateTrack;
  private ?string $updateAvailable;
  
  function __construct(?int $agentBinaryId, ?string $binaryType, ?string $version, ?string $operatingSystems, ?string $filename, ?string $updateTrack, ?string $updateAvailable) {
    $this->agentBinaryId = $agentBinaryId;
    $this->binaryType = $binaryType;
    $this->version = $version;
    $this->operatingSystems = $operatingSystems;
    $this->filename = $filename;
    $this->updateTrack = $updateTrack;
    $this->updateAvailable = $updateAvailable;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['agentBinaryId'] = $this->agentBinaryId;
    $dict['binaryType'] = $this->binaryType;
    $dict['version'] = $this->version;
    $dict['operatingSystems'] = $this->operatingSystems;
    $dict['filename'] = $this->filename;
    $dict['updateTrack'] = $this->updateTrack;
    $dict['updateAvailable'] = $this->updateAvailable;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['agentBinaryId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "agentBinaryId", "public" => False, "dba_mapping" => False];
    $dict['binaryType'] = ['read_only' => False, "type" => "str(20)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "binaryType", "public" => False, "dba_mapping" => False];
    $dict['version'] = ['read_only' => False, "type" => "str(20)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "version", "public" => False, "dba_mapping" => False];
    $dict['operatingSystems'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "operatingSystems", "public" => False, "dba_mapping" => False];
    $dict['filename'] = ['read_only' => False, "type" => "str(50)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "filename", "public" => False, "dba_mapping" => False];
    $dict['updateTrack'] = ['read_only' => False, "type" => "str(20)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "updateTrack", "public" => False, "dba_mapping" => False];
    $dict['updateAvailable'] = ['read_only' => True, "type" => "str(20)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "updateAvailable", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "agentBinaryId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->agentBinaryId;
  }
  
  function getId(): ?int {
    return $this->agentBinaryId;
  }
  
  function setId($id): void {
    $this->agentBinaryId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getBinaryType(): ?string {
    return $this->binaryType;
  }
  
  function setBinaryType(?string $binaryType): void {
    $this->binaryType = $binaryType;
  }
  
  function getVersion(): ?string {
    return $this->version;
  }
  
  function setVersion(?string $version): void {
    $this->version = $version;
  }
  
  function getOperatingSystems(): ?string {
    return $this->operatingSystems;
  }
  
  function setOperatingSystems(?string $operatingSystems): void {
    $this->operatingSystems = $operatingSystems;
  }
  
  function getFilename(): ?string {
    return $this->filename;
  }
  
  function setFilename(?string $filename): void {
    $this->filename = $filename;
  }
  
  function getUpdateTrack(): ?string {
    return $this->updateTrack;
  }
  
  function setUpdateTrack(?string $updateTrack): void {
    $this->updateTrack = $updateTrack;
  }
  
  function getUpdateAvailable(): ?string {
    return $this->updateAvailable;
  }
  
  function setUpdateAvailable(?string $updateAvailable): void {
    $this->updateAvailable = $updateAvailable;
  }
  
  const AGENT_BINARY_ID = "agentBinaryId";
  const BINARY_TYPE = "binaryType";
  const VERSION = "version";
  const OPERATING_SYSTEMS = "operatingSystems";
  const FILENAME = "filename";
  const UPDATE_TRACK = "updateTrack";
  const UPDATE_AVAILABLE = "updateAvailable";

  const PERM_CREATE = "permAgentBinaryCreate";
  const PERM_READ = "permAgentBinaryRead";
  const PERM_UPDATE = "permAgentBinaryUpdate";
  const PERM_DELETE = "permAgentBinaryDelete";
}
