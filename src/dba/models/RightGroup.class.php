<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class RightGroup extends AbstractModel {
  private $rightGroupId;
  private $groupName;
  private $level;
  
  function __construct($rightGroupId, $groupName, $level) {
    $this->rightGroupId = $rightGroupId;
    $this->groupName = $groupName;
    $this->level = $level;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['rightGroupId'] = $this->rightGroupId;
    $dict['groupName'] = $this->groupName;
    $dict['level'] = $this->level;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "rightGroupId";
  }
  
  function getPrimaryKeyValue() {
    return $this->rightGroupId;
  }
  
  function getId() {
    return $this->rightGroupId;
  }
  
  function setId($id) {
    $this->rightGroupId = $id;
  }
  
  function getGroupName(){
    return $this->groupName;
  }
  
  function setGroupName($groupName){
    $this->groupName = $groupName;
  }
  
  function getLevel(){
    return $this->level;
  }
  
  function setLevel($level){
    $this->level = $level;
  }

  const RIGHT_GROUP_ID = "rightGroupId";
  const GROUP_NAME = "groupName";
  const LEVEL = "level";
}
