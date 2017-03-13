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
  private $hashId;
  private $solveTime;
  private $hashlistId;
  
  function __construct($zapId, $hashId, $solveTime, $hashlistId) {
    $this->zapId = $zapId;
    $this->hashId = $hashId;
    $this->solveTime = $solveTime;
    $this->hashlistId = $hashlistId;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['zapId'] = $this->zapId;
    $dict['hashId'] = $this->hashId;
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
  
  function getHashId(){
    return $this->hashId;
  }
  
  function setHashId($hashId){
    $this->hashId = $hashId;
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

  const ZAP_ID = "zapId";
  const HASH_ID = "hashId";
  const SOLVE_TIME = "solveTime";
  const HASHLIST_ID = "hashlistId";
}
