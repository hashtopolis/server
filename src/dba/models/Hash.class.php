<?php

namespace DBA;

class Hash extends AbstractModel {
  private ?int $hashId;
  private ?int $hashlistId;
  private ?string $hash;
  private ?string $salt;
  private ?string $plaintext;
  private ?int $timeCracked;
  private ?int $chunkId;
  private ?int $isCracked;
  private ?int $crackPos;
  
  function __construct(?int $hashId, ?int $hashlistId, ?string $hash, ?string $salt, ?string $plaintext, ?int $timeCracked, ?int $chunkId, ?int $isCracked, ?int $crackPos) {
    $this->hashId = $hashId;
    $this->hashlistId = $hashlistId;
    $this->hash = $hash;
    $this->salt = $salt;
    $this->plaintext = $plaintext;
    $this->timeCracked = $timeCracked;
    $this->chunkId = $chunkId;
    $this->isCracked = $isCracked;
    $this->crackPos = $crackPos;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['hashId'] = $this->hashId;
    $dict['hashlistId'] = $this->hashlistId;
    $dict['hash'] = $this->hash;
    $dict['salt'] = $this->salt;
    $dict['plaintext'] = $this->plaintext;
    $dict['timeCracked'] = $this->timeCracked;
    $dict['chunkId'] = $this->chunkId;
    $dict['isCracked'] = $this->isCracked;
    $dict['crackPos'] = $this->crackPos;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['hashId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "hashId", "public" => False, "dba_mapping" => False];
    $dict['hashlistId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hashlistId", "public" => False, "dba_mapping" => False];
    $dict['hash'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hash", "public" => False, "dba_mapping" => False];
    $dict['salt'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "salt", "public" => False, "dba_mapping" => False];
    $dict['plaintext'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "plaintext", "public" => False, "dba_mapping" => False];
    $dict['timeCracked'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "timeCracked", "public" => False, "dba_mapping" => False];
    $dict['chunkId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "chunkId", "public" => False, "dba_mapping" => False];
    $dict['isCracked'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isCracked", "public" => False, "dba_mapping" => False];
    $dict['crackPos'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "crackPos", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "hashId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->hashId;
  }
  
  function getId(): ?int {
    return $this->hashId;
  }
  
  function setId($id): void {
    $this->hashId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose(): array {
    return get_object_vars($this);
  }
  
  function getHashlistId(): ?int {
    return $this->hashlistId;
  }
  
  function setHashlistId(?int $hashlistId): void {
    $this->hashlistId = $hashlistId;
  }
  
  function getHash(): ?string {
    return $this->hash;
  }
  
  function setHash(?string $hash): void {
    $this->hash = $hash;
  }
  
  function getSalt(): ?string {
    return $this->salt;
  }
  
  function setSalt(?string $salt): void {
    $this->salt = $salt;
  }
  
  function getPlaintext(): ?string {
    return $this->plaintext;
  }
  
  function setPlaintext(?string $plaintext): void {
    $this->plaintext = $plaintext;
  }
  
  function getTimeCracked(): ?int {
    return $this->timeCracked;
  }
  
  function setTimeCracked(?int $timeCracked): void {
    $this->timeCracked = $timeCracked;
  }
  
  function getChunkId(): ?int {
    return $this->chunkId;
  }
  
  function setChunkId(?int $chunkId): void {
    $this->chunkId = $chunkId;
  }
  
  function getIsCracked(): ?int {
    return $this->isCracked;
  }
  
  function setIsCracked(?int $isCracked): void {
    $this->isCracked = $isCracked;
  }
  
  function getCrackPos(): ?int {
    return $this->crackPos;
  }
  
  function setCrackPos(?int $crackPos): void {
    $this->crackPos = $crackPos;
  }
  
  const HASH_ID = "hashId";
  const HASHLIST_ID = "hashlistId";
  const HASH = "hash";
  const SALT = "salt";
  const PLAINTEXT = "plaintext";
  const TIME_CRACKED = "timeCracked";
  const CHUNK_ID = "chunkId";
  const IS_CRACKED = "isCracked";
  const CRACK_POS = "crackPos";

  const PERM_CREATE = "permHashCreate";
  const PERM_READ = "permHashRead";
  const PERM_UPDATE = "permHashUpdate";
  const PERM_DELETE = "permHashDelete";
}
