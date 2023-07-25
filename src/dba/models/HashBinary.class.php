<?php

namespace DBA;

class HashBinary extends AbstractModel {
  private $hashBinaryId;
  private $hashlistId;
  private $essid;
  private $hash;
  private $plaintext;
  private $timeCracked;
  private $chunkId;
  private $isCracked;
  private $crackPos;
  
  function __construct($hashBinaryId, $hashlistId, $essid, $hash, $plaintext, $timeCracked, $chunkId, $isCracked, $crackPos) {
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
  
  function getKeyValueDict() {
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
  
  static function getFeatures() {
    $dict = array();
    $dict['hashBinaryId'] = ['read_only' => True, "type" => "int", "subtype" => "unset", "null" => False, "pk" => True, "protected" => True, "private" => False, "alias" => "hashBinaryId"];
    $dict['hashlistId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hashlistId"];
    $dict['essid'] = ['read_only' => False, "type" => "str(100)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "essid"];
    $dict['hash'] = ['read_only' => False, "type" => "str(4294967295)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "hash"];
    $dict['plaintext'] = ['read_only' => False, "type" => "str(1024)", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "plaintext"];
    $dict['timeCracked'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "timeCracked"];
    $dict['chunkId'] = ['read_only' => False, "type" => "int", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "chunkId"];
    $dict['isCracked'] = ['read_only' => False, "type" => "bool", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "isCracked"];
    $dict['crackPos'] = ['read_only' => False, "type" => "int64", "subtype" => "unset", "null" => False, "pk" => False, "protected" => False, "private" => False, "alias" => "crackPos"];

    return $dict;
  }

  function getPrimaryKey() {
    return "hashBinaryId";
  }
  
  function getPrimaryKeyValue() {
    return $this->hashBinaryId;
  }
  
  function getId() {
    return $this->hashBinaryId;
  }
  
  function setId($id) {
    $this->hashBinaryId = $id;
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
  
  function getEssid() {
    return $this->essid;
  }
  
  function setEssid($essid) {
    $this->essid = $essid;
  }
  
  function getHash() {
    return $this->hash;
  }
  
  function setHash($hash) {
    $this->hash = $hash;
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
  
  const HASH_BINARY_ID = "hashBinaryId";
  const HASHLIST_ID = "hashlistId";
  const ESSID = "essid";
  const HASH = "hash";
  const PLAINTEXT = "plaintext";
  const TIME_CRACKED = "timeCracked";
  const CHUNK_ID = "chunkId";
  const IS_CRACKED = "isCracked";
  const CRACK_POS = "crackPos";
}
