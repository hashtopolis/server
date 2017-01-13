<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class Zap extends AbstractModel {
  private $zapId;
  private $hash;
  private $solveTime;
  private $hashlistId;
  
  function __construct($zapId, $hash, $solveTime, $hashlistId) {
    $this->zapId = $zapId;
    $this->hash = $hash;
    $this->solveTime = $solveTime;
    $this->hashlistId = $hashlistId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['zapId'] = $this->zapId;
    $dict['hash'] = $this->hash;
    $dict['solveTime'] = $this->solveTime;
    $dict['hashlistId'] = $this->hashlistId;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "zapId";
  }
  
  function getPrimaryKeyValue() {
    return $this->zapId;
  }
  
  function getId() {
    return $this->zapId;
  }
  
  function setId($id) {
    $this->zapId = $id;
  }
  
  function getHash(){
    return $this->hash;
  }
  
  function setHash($hash){
    $this->hash = $hash;
  }
  
  function getSolveTime(){
    return $this->solveTime;
  }
  
  function setSolveTime($solveTime){
    $this->solveTime = $solveTime;
  }
  
  function getHashlistId(){
    return $this->hashlistId;
  }
  
  function setHashlistId($hashlistId){
    $this->hashlistId = $hashlistId;
  }
}
