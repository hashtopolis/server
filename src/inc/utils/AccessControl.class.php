<?php

use DBA\User;

class AccessControl {
  private $user;
  private $rightGroup;
  
  /**
   * AccessControl constructor.
   * @param $user User
   */
  public function __construct($user = null) {
    global $FACTORIES;
    
    $this->user = $user;
    if ($this->user != null) {
      $this->rightGroup = $FACTORIES::getRightGroupFactory()->get($this->user->getRightGroupId());
    }
  }
  
  /**
   * If access is not granted, permission denied page will be shown
   * @param $perm string
   */
  public function checkPermission($perm) {
    // TODO: check if the user has the requested permission
  }
  
  /**
   * @param $perm string
   * @return bool true if access is granted
   */
  public function hasPermission($perm) {
    if($this->rightGroup == null){
      return false;
    }
    else if($this->rightGroup->getPermissions() == 'ALL'){
      return true; // ALL denotes admin permissions which are independant of which access variables exactly exist
    }
    // TODO: check if the user has the requested permission
    return false;
  }
}