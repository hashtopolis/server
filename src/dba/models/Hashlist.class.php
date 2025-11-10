<?php

namespace DBA;

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
  private ?int $uploadedTotalLines;
  private ?int $uploadedEmptyLines;
  private ?int $uploadedValidHashes;
  private ?int $uploadedValidHashesWithoutExpectedSalt;
  private ?int $uploadedInvalidHashes;
  
  function __construct(?int $hashlistId, ?string $hashlistName, ?int $format, ?int $hashTypeId, ?int $hashCount, ?string $saltSeparator, ?int $cracked, ?int $isSecret, ?int $hexSalt, ?int $isSalted, ?int $accessGroupId, ?string $notes, ?int $brainId, ?int $brainFeatures, ?int $isArchived, ?int $uploadedTotalLines, ?int $uploadedEmptyLines, ?int $uploadedValidHashes, ?int $uploadedValidHashesWithoutExpectedSalt, ?int $uploadedInvalidHashes) {
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
    $this->uploadedTotalLines = $uploadedTotalLines;
    $this->uploadedEmptyLines = $uploadedEmptyLines;
    $this->uploadedValidHashes = $uploadedValidHashes;
    $this->uploadedValidHashesWithoutExpectedSalt = $uploadedValidHashesWithoutExpectedSalt;
    $this->uploadedInvalidHashes = $uploadedInvalidHashes;
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
    $dict['uploadedTotalLines'] = $this->uploadedTotalLines;
    $dict['uploadedEmptyLines'] = $this->uploadedEmptyLines;
    $dict['uploadedValidHashes'] = $this->uploadedValidHashes;
    $dict['uploadedValidHashesWithoutExpectedSalt'] = $this->uploadedValidHashesWithoutExpectedSalt;
    $dict['uploadedInvalidHashes'] = $this->uploadedInvalidHashes;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['hashlistId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "hashlistId", "public" => False];
    $dict['hashlistName'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "name", "public" => False];
    $dict['format'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => [0 => "Hashlist format is PLAIN", 1 => "Hashlist format is WPA", 2 => "Hashlist format is BINARY", 3 => "Hashlist is SUPERHASHLIST", ], "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "format", "public" => False];
    $dict['hashTypeId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hashTypeId", "public" => False];
    $dict['hashCount'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hashCount", "public" => False];
    $dict['saltSeparator'] = ['read_only' => True, "type" => "str(10)", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "separator", "public" => False];
    $dict['cracked'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => True, "private" => False, "alias" => "cracked", "public" => False];
    $dict['isSecret'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isSecret", "public" => False];
    $dict['hexSalt'] = ['read_only' => True, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isHexSalt", "public" => False];
    $dict['isSalted'] = ['read_only' => True, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isSalted", "public" => False];
    $dict['accessGroupId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "accessGroupId", "public" => False];
    $dict['notes'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "notes", "public" => False];
    $dict['brainId'] = ['read_only' => True, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "useBrain", "public" => False];
    $dict['brainFeatures'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "brainFeatures", "public" => False];
    $dict['isArchived'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isArchived", "public" => False];
    $dict['uploadedTotalLines'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "uploadedTotalLines", "public" => False];
    $dict['uploadedEmptyLines'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "uploadedEmptyLines", "public" => False];
    $dict['uploadedValidHashes'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "uploadedValidHashes", "public" => False];
    $dict['uploadedValidHashesWithoutExpectedSalt'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "uploadedValidHashesWithoutExpectedSalt", "public" => False];
    $dict['uploadedInvalidHashes'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => True, "pk" => False, "protected" => False, "private" => False, "alias" => "uploadedInvalidHashes", "public" => False];

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
  
  function getUploadedTotalLines(): ?int {
    return $this->uploadedTotalLines;
  }
  
  function setUploadedTotalLines(?int $uploadedTotalLines): void {
    $this->uploadedTotalLines = $uploadedTotalLines;
  }

  function getUploadedEmptyLines(): ?int {
    return $this->uploadedEmptyLines;
  }
  
  function setUploadedEmptyLines(?int $uploadedEmptyLines): void {
    $this->uploadedEmptyLines = $uploadedEmptyLines;
  }

  function getUploadedValidHashes(): ?int {
    return $this->uploadedValidHashes;
  }
  
  function setUploadedValidHashes(?int $uploadedValidHashes): void {
    $this->uploadedValidHashes = $uploadedValidHashes;
  }

  function getUploadedValidHashesWithoutExpectedSalt(): ?int {
    return $this->uploadedValidHashesWithoutExpectedSalt;
  }
  
  function setUploadedValidHashesWithoutExpectedSalt(?int $uploadedValidHashesWithoutExpectedSalt): void {
    $this->uploadedValidHashesWithoutExpectedSalt = $uploadedValidHashesWithoutExpectedSalt;
  }

  function getUploadedInvalidHashes(): ?int {
    return $this->uploadedInvalidHashes;
  }
  
  function setUploadedInvalidHashes(?int $uploadedInvalidHashes): void {
    $this->uploadedInvalidHashes = $uploadedInvalidHashes;
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
  const UPLOADED_TOTAL_LINES = "uploadedTotalLines";
  const UPLOADED_EMPTY_LINES = "uploadedEmptyLines";
  const UPLOADED_VALID_HASHES = "uploadedValidHashes";
  const UPLOADED_VALID_HASHES_WITHOUT_EXPECTED_SALT = "uploadedValidHashesWithoutExpectedSalt";
  const UPLOADED_INVALID_HASHES = "uploadedInvalidHashes";

  const PERM_CREATE = "permHashlistCreate";
  const PERM_READ = "permHashlistRead";
  const PERM_UPDATE = "permHashlistUpdate";
  const PERM_DELETE = "permHashlistDelete";
}
