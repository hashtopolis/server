<?php

namespace DBA;

class Hash extends AbstractModel {
  private $hashId;
  private $hashlistId;
  private $hash;
  private $salt;
  private $plaintext;
  private $timeCracked;
  private $chunkId;
  private $isCracked;
  private $crackPos;
  
  function __construct($hashId, $hashlistId, $hash, $salt, $plaintext, $timeCracked, $chunkId, $isCracked, $crackPos) {
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
  
  function getKeyValueDict() {
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
  
  static function getFeatures() {
    $dict = array();
    $dict['hashId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "hashId"];
    $dict['hashlistId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hashlistId"];
    $dict['hash'] = ['read_only' => False, "type" => "str(65535)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hash"];
    $dict['salt'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "salt"];
    $dict['plaintext'] = ['read_only' => False, "type" => "str(256)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "plaintext"];
    $dict['timeCracked'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "timeCracked"];
    $dict['chunkId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "chunkId"];
    $dict['isCracked'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isCracked"];
    $dict['crackPos'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "crackPos"];

    return $dict;
  }

  function getPrimaryKey() {
    return "hashId";
  }
  
  function getPrimaryKeyValue() {
    return $this->hashId;
  }
  
  function getId() {
    return $this->hashId;
  }
  
  function setId($id) {
    $this->hashId = $id;
  }
  
  /**
   * Used to serialize the data contained in the model
   * @return array
   */
  public function expose() {
    return get_object_vars($this);
  }
  
  function getHashlistId() {
    return $this->hashlistId;
  }
  
  function setHashlistId($hashlistId) {
    $this->hashlistId = $hashlistId;
  }
  
  function getHash() {
    return $this->hash;
  }
  
  function setHash($hash) {
    $this->hash = $hash;
  }
  
  function getSalt() {
    return $this->salt;
  }
  
  function setSalt($salt) {
    $this->salt = $salt;
  }
  
  function getPlaintext() {
    return $this->plaintext;
  }
  
  function setPlaintext($plaintext) {
    $this->plaintext = $plaintext;
  }
  
  function getTimeCracked() {
    return $this->timeCracked;
  }
  
  function setTimeCracked($timeCracked) {
    $this->timeCracked = $timeCracked;
  }
  
  function getChunkId() {
    return $this->chunkId;
  }
  
  function setChunkId($chunkId) {
    $this->chunkId = $chunkId;
  }
  
  function getIsCracked() {
    return $this->isCracked;
  }
  
  function setIsCracked($isCracked) {
    $this->isCracked = $isCracked;
  }
  
  function getCrackPos() {
    return $this->crackPos;
  }
  
  function setCrackPos($crackPos) {
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
