<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class Chunk extends AbstractModel {
  private $chunkId;
  private $taskId;
  private $skip;
  private $length;
  private $agentId;
  private $dispatchTime;
  private $progress;
  private $rprogress;
  private $state;
  private $cracked;
  private $solveTime;
  private $speed;
  
  function __construct($chunkId, $taskId, $skip, $length, $agentId, $dispatchTime, $progress, $rprogress, $state, $cracked, $solveTime, $speed) {
    $this->chunkId = $chunkId;
    $this->taskId = $taskId;
    $this->skip = $skip;
    $this->length = $length;
    $this->agentId = $agentId;
    $this->dispatchTime = $dispatchTime;
    $this->progress = $progress;
    $this->rprogress = $rprogress;
    $this->state = $state;
    $this->cracked = $cracked;
    $this->solveTime = $solveTime;
    $this->speed = $speed;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['chunkId'] = $this->chunkId;
    $dict['taskId'] = $this->taskId;
    $dict['skip'] = $this->skip;
    $dict['length'] = $this->length;
    $dict['agentId'] = $this->agentId;
    $dict['dispatchTime'] = $this->dispatchTime;
    $dict['progress'] = $this->progress;
    $dict['rprogress'] = $this->rprogress;
    $dict['state'] = $this->state;
    $dict['cracked'] = $this->cracked;
    $dict['solveTime'] = $this->solveTime;
    $dict['speed'] = $this->speed;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "chunkId";
  }
  
  function getPrimaryKeyValue() {
    return $this->chunkId;
  }
  
  function getId() {
    return $this->chunkId;
  }
  
  function setId($id) {
    $this->chunkId = $id;
  }
  
  function getTaskId(){
    return $this->taskId;
  }
  
  function setTaskId($taskId){
    $this->taskId = $taskId;
  }
  
  function getSkip(){
    return $this->skip;
  }
  
  function setSkip($skip){
    $this->skip = $skip;
  }
  
  function getLength(){
    return $this->length;
  }
  
  function setLength($length){
    $this->length = $length;
  }
  
  function getAgentId(){
    return $this->agentId;
  }
  
  function setAgentId($agentId){
    $this->agentId = $agentId;
  }
  
  function getDispatchTime(){
    return $this->dispatchTime;
  }
  
  function setDispatchTime($dispatchTime){
    $this->dispatchTime = $dispatchTime;
  }
  
  function getProgress(){
    return $this->progress;
  }
  
  function setProgress($progress){
    $this->progress = $progress;
  }
  
  function getRprogress(){
    return $this->rprogress;
  }
  
  function setRprogress($rprogress){
    $this->rprogress = $rprogress;
  }
  
  function getState(){
    return $this->state;
  }
  
  function setState($state){
    $this->state = $state;
  }
  
  function getCracked(){
    return $this->cracked;
  }
  
  function setCracked($cracked){
    $this->cracked = $cracked;
  }
  
  function getSolveTime(){
    return $this->solveTime;
  }
  
  function setSolveTime($solveTime){
    $this->solveTime = $solveTime;
  }
  
  function getSpeed(){
    return $this->speed;
  }
  
  function setSpeed($speed){
    $this->speed = $speed;
  }
}
