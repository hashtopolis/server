<?php

use DBA\User;

class AccessControl {
  private $user;
  
  /**
   * AccessControl constructor.
   * @param $user User
   */
  public function __construct($user = null) {
    $this->user = $user;
  }
  
  /**
   * If access is not granted, permission denied page will be shown
   * @param $perm string
   */
  public function checkPermission($perm) {
  }
  
  /**
   * If access is not granted, permission denied page will be shown
   * @param $viewPermission string
   */
  public function checkViewPermission($viewPermission) {
    // TODO: check if the user has the requested viewPermission
  }
}