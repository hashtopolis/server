<?php

use DBA\User;
use DBA\QueryFilter;
use DBA\RightGroup;
use DBA\Factory;

class AccessControlUtils {
  /**
   * @param int $groupId
   * @return User[]
   */
  public static function getMembers($groupId) {
    $qF = new QueryFilter(User::RIGHT_GROUP_ID, $groupId, "=");
    return Factory::getUserFactory()->filter([Factory::FILTER => $qF]);
  }
  
  /**
   * @return RightGroup[]
   */
  public static function getGroups() {
    return Factory::getRightGroupFactory()->filter([]);
  }
  
  /**
   * @param int $groupId
   * @param array $perm
   * @return boolean
   * @throws HTException
   */
  public static function updateGroupPermissions($groupId, $perm) {
    $group = AccessControlUtils::getGroup($groupId);
    if ($group->getPermissions() == 'ALL') {
      throw new HTException("Administrator group cannot be changed!");
    }
    
    $newArr = [];
    foreach ($perm as $p) {
      $split = explode("-", $p);
      if (sizeof($split) != 2 || !in_array($split[1], array("0", "1"))) {
        continue; // ignore invalid submits
      }
      $constants = DAccessControl::getConstants();
      foreach ($constants as $constant) {
        if (is_array($constant)) {
          $constant = $constant[0];
        }
        if ($split[0] == $constant) {
          $newArr[$constant] = ($split[1] == "1") ? true : false;
        }
      }
    }
    Factory::getRightGroupFactory()->set($group, RightGroup::PERMISSIONS, json_encode($newArr));
    
    $acl = AccessControl::getInstance(null, $group->getId());
    $arr = $newArr;
    $changes = false;
    foreach ($newArr as $constant => $set) {
      if ($set == true) {
        continue;
      }
      else if ($acl->givenByDependency($constant)) {
        $arr[$constant] = true;
        $changes = true;
      }
    }
    Factory::getRightGroupFactory()->set($group, RightGroup::PERMISSIONS, json_encode($arr));
    
    return $changes;
  }
  
  /**
   * @param string $groupName
   * @return RightGroup
   * @throws HTException
   */
  public static function createGroup($groupName) {
    if (strlen($groupName) == 0 || strlen($groupName) > DLimits::ACCESS_GROUP_MAX_LENGTH) {
      throw new HTException("Permission group name is too short or too long!");
    }
    
    $qF = new QueryFilter(RightGroup::GROUP_NAME, $groupName, "=");
    $check = Factory::getRightGroupFactory()->filter([Factory::FILTER => $qF], true);
    if ($check !== null) {
      throw new HTException("There is already an permission group with the same name!");
    }
    $group = new RightGroup(null, $groupName, "[]");
    $group = Factory::getRightGroupFactory()->save($group);
    return $group;
  }
  
  /**
   * @param int $groupId
   * @throws HTException
   */
  public static function deleteGroup($groupId) {
    $group = AccessControlUtils::getGroup($groupId);
    $qF = new QueryFilter(User::RIGHT_GROUP_ID, $group->getId(), "=");
    $count = Factory::getUserFactory()->countFilter([Factory::FILTER => $qF]);
    if ($count > 0) {
      throw new HTException("You cannot delete a group which has still users belonging to it!");
    }
    
    // delete permission group
    Factory::getRightGroupFactory()->delete($group);
  }
  
  /**
   * @param int $groupId
   * @return RightGroup
   * @throws HTException
   */
  public static function getGroup($groupId) {
    $group = Factory::getRightGroupFactory()->get($groupId);
    if ($group === null) {
      throw new HTException("Invalid group!");
    }
    return $group;
  }
}