<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\RightGroup;
use Hashtopolis\inc\defines\DAccessControl;
use Hashtopolis\inc\Login;
use Hashtopolis\inc\UI;

class AccessControl {
  private ?User $user;
  private ?RightGroup $rightGroup = null;
  
  private static ?self $instance = null;
  
  /**
   * @param ?User $user
   * @param int $groupId
   * @return AccessControl
   * @throws Exception
   */
  public static function getInstance(?User $user = null, int $groupId = 0): self {
    if ($user != null || $groupId != 0) {
      self::$instance = new AccessControl($user, $groupId);
    }
    else if (self::$instance == null) {
      self::$instance = new AccessControl();
    }
    return self::$instance;
  }
  
  /**
   * @return ?User
   */
  public function getUser(): ?User {
    return $this->user;
  }
  
  /**
   * AccessControl constructor.
   * @param $user ?User
   * @param $groupId int
   * @throws Exception
   */
  private function __construct(?User $user = null, int $groupId = 0) {
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
   * @throws Exception
   */
  public function reload(): void {
    if ($this->user != null) {
      $this->rightGroup = Factory::getRightGroupFactory()->get($this->user->getRightGroupId());
    }
  }
  
  /**
   * If access is not granted, permission denied page will be shown
   * @param $perm string|string[]
   */
  public function checkPermission(string|array $perm): void {
    if (!$this->hasPermission($perm)) {
      UI::permissionError();
    }
  }
  
  /**
   * @param $singlePerm string
   * @return bool
   */
  public function givenByDependency(string $singlePerm): bool {
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
  public function hasPermission(string|array $perm): bool {
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
      if (isset($json[$p]) && $json[$p]) {
        return true;
      }
    }
    return false;
  }
}