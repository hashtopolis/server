<?php

use DBA\User;
use DBA\Factory;

class AccessControl {
  private $user;
  private $rightGroup;

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
  public function __construct($user = null, $groupId = 0) {
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
    /** @var $LOGIN Login */
    global $LOGIN;

    if ($perm == DAccessControl::PUBLIC_ACCESS) {
      return true;
    }
    else if ($perm == DAccessControl::LOGIN_ACCESS && $LOGIN->isLoggedin()) {
      return true;
    }
    else if ($this->rightGroup == null) {
      return false;
    }
    else if ($this->rightGroup->getPermissions() == 'ALL') {
      return true; // ALL denotes admin permissions which are independant of which access variables exactly exist
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