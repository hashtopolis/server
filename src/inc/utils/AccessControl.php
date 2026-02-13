<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\models\User;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\defines\DAccessControl;
use Hashtopolis\inc\Login;
use Hashtopolis\inc\UI;

class AccessControl {
  private $user;
  private $rightGroup;
  
  private static $instance = null;
  
  /**
   * @param User $user
   * @param int $groupId
   * @return AccessControl
   */
  public static function getInstance($user = null, $groupId = 0) {
    if ($user != null || $groupId != 0) {
      self::$instance = new AccessControl($user, $groupId);
    }
    else if (self::$instance == null) {
      self::$instance = new AccessControl();
    }
    return self::$instance;
  }
  
  /**
   * @return User
   */
  public function getUser() {
    return $this->user;
  }
  
  /**
   * AccessControl constructor.
   * @param $user User
   * @param $groupId int
   */
  private function __construct($user = null, $groupId = 0) {
    $this->user = $user;
    if ($this->user != null) {
      $this->rightGroup = Factory::getRightGroupFactory()->get($this->user->getRightGroupId());
    }
    else if ($groupId != 0) {
      $this->rightGroup = Factory::getRightGroupFactory()->get($groupId);
    }
  }
  
  /**
   * Force a reload of the permissions from the database
   */
  public function reload() {
    if ($this->user != null) {
      $this->rightGroup = Factory::getRightGroupFactory()->get($this->user->getRightGroupId());
    }
  }
  
  /**
   * If access is not granted, permission denied page will be shown
   * @param $perm string|string[]
   */
  public function checkPermission($perm) {
    if (!$this->hasPermission($perm)) {
      UI::permissionError();
    }
  }
  
  /**
   * @param $singlePerm string
   * @return bool
   */
  public function givenByDependency($singlePerm) {
    $constants = DAccessControl::getConstants();
    foreach ($constants as $constant) {
      if (is_array($constant) && $singlePerm == $constant[0] && $this->hasPermission($constant)) {
        return true;
      }
      else if (!is_array($constant) && $constant == $singlePerm && $this->hasPermission($constant)) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * @param $perm string|string[]
   * @return bool true if access is granted
   */
  public function hasPermission($perm) {
    if ($perm == DAccessControl::PUBLIC_ACCESS) {
      return true;
    }
    else if ($perm == DAccessControl::LOGIN_ACCESS && Login::getInstance()->isLoggedin()) {
      return true;
    }
    else if ($this->rightGroup == null) {
      return false;
    }
    else if ($this->rightGroup->getPermissions() == 'ALL') {
      return true; // ALL denotes admin permissions which are independent of which access variables exactly exist
    }
    if (!is_array($perm)) {
      $perm = array($perm);
    }
    $json = json_decode($this->rightGroup->getPermissions(), true);
    foreach ($perm as $p) {
      if (isset($json[$p]) && $json[$p] == true) {
        return true;
      }
    }
    return false;
  }
}