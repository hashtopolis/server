<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModel;

class HashBinary extends AbstractModel {
  private ?int $hashBinaryId;
  private ?int $hashlistId;
  private ?string $essid;
  private ?string $hash;
  private ?string $plaintext;
  private ?int $timeCracked;
  private ?int $chunkId;
  private ?int $isCracked;
  private ?int $crackPos;
  
  function __construct(?int $hashBinaryId, ?int $hashlistId, ?string $essid, ?string $hash, ?string $plaintext, ?int $timeCracked, ?int $chunkId, ?int $isCracked, ?int $crackPos) {
    $this->hashBinaryId = $hashBinaryId;
    $this->hashlistId = $hashlistId;
    $this->essid = $essid;
    $this->hash = $hash;
    $this->plaintext = $plaintext;
    $this->timeCracked = $timeCracked;
    $this->chunkId = $chunkId;
    $this->isCracked = $isCracked;
    $this->crackPos = $crackPos;
  }
  
  function getKeyValueDict(): array {
    $dict = array();
    $dict['hashBinaryId'] = $this->hashBinaryId;
    $dict['hashlistId'] = $this->hashlistId;
    $dict['essid'] = $this->essid;
    $dict['hash'] = $this->hash;
    $dict['plaintext'] = $this->plaintext;
    $dict['timeCracked'] = $this->timeCracked;
    $dict['chunkId'] = $this->chunkId;
    $dict['isCracked'] = $this->isCracked;
    $dict['crackPos'] = $this->crackPos;
    
    return $dict;
  }
  
  static function getFeatures(): array {
    $dict = array();
    $dict['hashBinaryId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "hashBinaryId", "public" => False, "dba_mapping" => False];
    $dict['hashlistId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hashlistId", "public" => False, "dba_mapping" => False];
    $dict['essid'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "essid", "public" => False, "dba_mapping" => False];
    $dict['hash'] = ['read_only' => False, "type" => "str(4294967295)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hash", "public" => False, "dba_mapping" => False];
    $dict['plaintext'] = ['read_only' => False, "type" => "str(1024)", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "plaintext", "public" => False, "dba_mapping" => False];
    $dict['timeCracked'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "timeCracked", "public" => False, "dba_mapping" => False];
    $dict['chunkId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "chunkId", "public" => False, "dba_mapping" => False];
    $dict['isCracked'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isCracked", "public" => False, "dba_mapping" => False];
    $dict['crackPos'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "choices" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "crackPos", "public" => False, "dba_mapping" => False];

    return $dict;
  }

  function getPrimaryKey(): string {
    return "hashBinaryId";
  }
  
  function getPrimaryKeyValue(): ?int {
    return $this->hashBinaryId;
  }
  
  function getId(): ?int {
    return $this->hashBinaryId;
  }
  
  function setId($id): void {
    $this->hashBinaryId = $id;
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
  
  function getEssid(): ?string {
    return $this->essid;
  }
  
  function setEssid(?string $essid): void {
    $this->essid = $essid;
  }
  
  function getHash(): ?string {
    return $this->hash;
  }
  
  function setHash(?string $hash): void {
    $this->hash = $hash;
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
  
  const HASH_BINARY_ID = "hashBinaryId";
  const HASHLIST_ID = "hashlistId";
  const ESSID = "essid";
  const HASH = "hash";
  const PLAINTEXT = "plaintext";
  const TIME_CRACKED = "timeCracked";
  const CHUNK_ID = "chunkId";
  const IS_CRACKED = "isCracked";
  const CRACK_POS = "crackPos";

  const PERM_CREATE = "permHashBinaryCreate";
  const PERM_READ = "permHashBinaryRead";
  const PERM_UPDATE = "permHashBinaryUpdate";
  const PERM_DELETE = "permHashBinaryDelete";
}
