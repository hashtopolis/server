<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class HashBinary extends AbstractModel {
  private $hashBinaryId;
  private $hashlistId;
  private $essid;
  private $hash;
  private $plaintext;
  private $time;
  private $chunkId;
  private $isCracked;
  
  function __construct($hashBinaryId, $hashlistId, $essid, $hash, $plaintext, $time, $chunkId, $isCracked) {
    $this->hashBinaryId = $hashBinaryId;
    $this->hashlistId = $hashlistId;
    $this->essid = $essid;
    $this->hash = $hash;
    $this->plaintext = $plaintext;
    $this->time = $time;
    $this->chunkId = $chunkId;
    $this->isCracked = $isCracked;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['hashBinaryId'] = $this->hashBinaryId;
    $dict['hashlistId'] = $this->hashlistId;
    $dict['essid'] = $this->essid;
    $dict['hash'] = $this->hash;
    $dict['plaintext'] = $this->plaintext;
    $dict['time'] = $this->time;
    $dict['chunkId'] = $this->chunkId;
    $dict['isCracked'] = $this->isCracked;
    
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
  
  function getHashlistId(){
    return $this->hashlistId;
  }
  
  function setHashlistId($hashlistId){
    $this->hashlistId = $hashlistId;
  }
  
  function getEssid(){
    return $this->essid;
  }
  
  function setEssid($essid){
    $this->essid = $essid;
  }
  
  function getHash(){
    return $this->hash;
  }
  
  function setHash($hash){
    $this->hash = $hash;
  }
  
  function getPlaintext(){
    return $this->plaintext;
  }
  
  function setPlaintext($plaintext){
    $this->plaintext = $plaintext;
  }
  
  function getTime(){
    return $this->time;
  }
  
  function setTime($time){
    $this->time = $time;
  }
  
  function getChunkId(){
    return $this->chunkId;
  }
  
  function setChunkId($chunkId){
    $this->chunkId = $chunkId;
  }
  
  function getIsCracked(){
    return $this->isCracked;
  }
  
  function setIsCracked($isCracked){
    $this->isCracked = $isCracked;
  }

  public const HASH_BINARY_ID = "hashBinaryId";
  public const HASHLIST_ID = "hashlistId";
  public const ESSID = "essid";
  public const HASH = "hash";
  public const PLAINTEXT = "plaintext";
  public const TIME = "time";
  public const CHUNK_ID = "chunkId";
  public const IS_CRACKED = "isCracked";
}
