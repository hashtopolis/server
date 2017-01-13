<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class Hash extends AbstractModel {
  private $hashId;
  private $hashlistId;
  private $hash;
  private $salt;
  private $plaintext;
  private $time;
  private $chunkId;
  private $isCracked;
  
  function __construct($hashId, $hashlistId, $hash, $salt, $plaintext, $time, $chunkId, $isCracked) {
    $this->hashId = $hashId;
    $this->hashlistId = $hashlistId;
    $this->hash = $hash;
    $this->salt = $salt;
    $this->plaintext = $plaintext;
    $this->time = $time;
    $this->chunkId = $chunkId;
    $this->isCracked = $isCracked;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['hashId'] = $this->hashId;
    $dict['hashlistId'] = $this->hashlistId;
    $dict['hash'] = $this->hash;
    $dict['salt'] = $this->salt;
    $dict['plaintext'] = $this->plaintext;
    $dict['time'] = $this->time;
    $dict['chunkId'] = $this->chunkId;
    $dict['isCracked'] = $this->isCracked;
    
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
  
  function getHashlistId(){
    return $this->hashlistId;
  }
  
  function setHashlistId($hashlistId){
    $this->hashlistId = $hashlistId;
  }
  
  function getHash(){
    return $this->hash;
  }
  
  function setHash($hash){
    $this->hash = $hash;
  }
  
  function getSalt(){
    return $this->salt;
  }
  
  function setSalt($salt){
    $this->salt = $salt;
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
}
