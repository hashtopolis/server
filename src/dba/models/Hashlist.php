<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class Hashlist extends AbstractModel {
  private ?int $hashlistId;
  private ?string $hashlistName;
  private ?int $format;
  private ?int $hashTypeId;
  private ?int $hashCount;
  private ?string $saltSeparator;
  private ?int $cracked;
  private ?int $isSecret;
  private ?int $hexSalt;
  private ?int $isSalted;
  private ?int $accessGroupId;
  private ?string $notes;
  private ?int $brainId;
  private ?int $brainFeatures;
  private ?int $isArchived;
  
  function __construct(?int $hashlistId, ?string $hashlistName, ?int $format, ?int $hashTypeId, ?int $hashCount, ?string $saltSeparator, ?int $cracked, ?int $isSecret, ?int $hexSalt, ?int $isSalted, ?int $accessGroupId, ?string $notes, ?int $brainId, ?int $brainFeatures, ?int $isArchived) {
    $this->hashlistId = $hashlistId;
    $this->hashlistName = $hashlistName;
    $this->format = $format;
    $this->hashTypeId = $hashTypeId;
    $this->hashCount = $hashCount;
    $this->saltSeparator = $saltSeparator;
    $this->cracked = $cracked;
    $this->isSecret = $isSecret;
    $this->hexSalt = $hexSalt;
    $this->isSalted = $isSalted;
    $this->accessGroupId = $accessGroupId;
    $this->notes = $notes;
    $this->brainId = $brainId;
    $this->brainFeatures = $brainFeatures;
    $this->isArchived = $isArchived;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['hashlistId'] = $this->hashlistId;
    $dict['hashlistName'] = $this->hashlistName;
    $dict['format'] = $this->format;
    $dict['hashTypeId'] = $this->hashTypeId;
    $dict['hashCount'] = $this->hashCount;
    $dict['saltSeparator'] = $this->saltSeparator;
    $dict['cracked'] = $this->cracked;
    $dict['isSecret'] = $this->isSecret;
    $dict['hexSalt'] = $this->hexSalt;
    $dict['isSalted'] = $this->isSalted;
    $dict['accessGroupId'] = $this->accessGroupId;
    $dict['notes'] = $this->notes;
    $dict['brainId'] = $this->brainId;
    $dict['brainFeatures'] = $this->brainFeatures;
    $dict['isArchived'] = $this->isArchived;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['hashlistId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "hashlistId", "public" => False, "dba_mapping" => False];
    $dict['hashlistName'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "name", "public" => False, "dba_mapping" => False];
    $dict['format'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => [0 => "Hashlist format is PLAIN", 1 => "Hashlist format is WPA", 2 => "Hashlist format is BINARY", 3 => "Hashlist is SUPERHASHLIST", ], "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "format", "public" => False, "dba_mapping" => False];
    $dict['hashTypeId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hashTypeId", "public" => False, "dba_mapping" => False];
    $dict['hashCount'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hashCount", "public" => False, "dba_mapping" => False];
    $dict['saltSeparator'] = ['read_only' => True, "type" => "str(10)", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "separator", "public" => False, "dba_mapping" => False];
    $dict['cracked'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "cracked", "public" => False, "dba_mapping" => False];
    $dict['isSecret'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isSecret", "public" => False, "dba_mapping" => False];
    $dict['hexSalt'] = ['read_only' => True, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isHexSalt", "public" => False, "dba_mapping" => False];
    $dict['isSalted'] = ['read_only' => True, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isSalted", "public" => False, "dba_mapping" => False];
    $dict['accessGroupId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "accessGroupId", "public" => False, "dba_mapping" => False];
    $dict['notes'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "notes", "public" => False, "dba_mapping" => False];
    $dict['brainId'] = ['read_only' => True, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "useBrain", "public" => False, "dba_mapping" => False];
    $dict['brainFeatures'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "brainFeatures", "public" => False, "dba_mapping" => False];
    $dict['isArchived'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isArchived", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "hashlistId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->hashlistId;
  }
  
  function getId(): ?int {
    return $this->hashlistId;
  }
  
  function setId($id): void {
    $this->hashlistId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getHashlistName(): ?string {
    return $this->hashlistName;
  }
  
  function setHashlistName(?string $hashlistName): void {
    $this->hashlistName = $hashlistName;
  }
  
  function getFormat(): ?int {
    return $this->format;
  }
  
  function setFormat(?int $format): void {
    $this->format = $format;
  }
  
  function getHashTypeId(): ?int {
    return $this->hashTypeId;
  }
  
  function setHashTypeId(?int $hashTypeId): void {
    $this->hashTypeId = $hashTypeId;
  }
  
  function getHashCount(): ?int {
    return $this->hashCount;
  }
  
  function setHashCount(?int $hashCount): void {
    $this->hashCount = $hashCount;
  }
  
  function getSaltSeparator(): ?string {
    return $this->saltSeparator;
  }
  
  function setSaltSeparator(?string $saltSeparator): void {
    $this->saltSeparator = $saltSeparator;
  }
  
  function getCracked(): ?int {
    return $this->cracked;
  }
  
  function setCracked(?int $cracked): void {
    $this->cracked = $cracked;
  }
  
  function getIsSecret(): ?int {
    return $this->isSecret;
  }
  
  function setIsSecret(?int $isSecret): void {
    $this->isSecret = $isSecret;
  }
  
  function getHexSalt(): ?int {
    return $this->hexSalt;
  }
  
  function setHexSalt(?int $hexSalt): void {
    $this->hexSalt = $hexSalt;
  }
  
  function getIsSalted(): ?int {
    return $this->isSalted;
  }
  
  function setIsSalted(?int $isSalted): void {
    $this->isSalted = $isSalted;
  }
  
  function getAccessGroupId(): ?int {
    return $this->accessGroupId;
  }
  
  function setAccessGroupId(?int $accessGroupId): void {
    $this->accessGroupId = $accessGroupId;
  }
  
  function getNotes(): ?string {
    return $this->notes;
  }
  
  function setNotes(?string $notes): void {
    $this->notes = $notes;
  }
  
  function getBrainId(): ?int {
    return $this->brainId;
  }
  
  function setBrainId(?int $brainId): void {
    $this->brainId = $brainId;
  }
  
  function getBrainFeatures(): ?int {
    return $this->brainFeatures;
  }
  
  function setBrainFeatures(?int $brainFeatures): void {
    $this->brainFeatures = $brainFeatures;
  }
  
  function getIsArchived(): ?int {
    return $this->isArchived;
  }
  
  function setIsArchived(?int $isArchived): void {
    $this->isArchived = $isArchived;
  }
  
  const HASHLIST_ID = "hashlistId";
  const HASHLIST_NAME = "hashlistName";
  const FORMAT = "format";
  const HASH_TYPE_ID = "hashTypeId";
  const HASH_COUNT = "hashCount";
  const SALT_SEPARATOR = "saltSeparator";
  const CRACKED = "cracked";
  const IS_SECRET = "isSecret";
  const HEX_SALT = "hexSalt";
  const IS_SALTED = "isSalted";
  const ACCESS_GROUP_ID = "accessGroupId";
  const NOTES = "notes";
  const BRAIN_ID = "brainId";
  const BRAIN_FEATURES = "brainFeatures";
  const IS_ARCHIVED = "isArchived";

  const PERM_CREATE = "permHashlistCreate";
  const PERM_READ = "permHashlistRead";
  const PERM_UPDATE = "permHashlistUpdate";
  const PERM_DELETE = "permHashlistDelete";
}
