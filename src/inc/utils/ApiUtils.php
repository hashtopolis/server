<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\ApiKey;
use Hashtopolis\dba\models\ApiGroup;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\defines\UApi;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\Util;

class ApiUtils {
  /**
   * @param int $groupId
   * @param array $perm
   * @param string $sectionName
   * @throws HTException
   */
  public static function update($groupId, $perm, $sectionName) {
    $group = Factory::getApiGroupFactory()->get($groupId);
    if ($group == null) {
      throw new HTException("Invalid API group!");
    }
    else if ($group->getPermissions() == 'ALL') {
      throw new HTException("Administrator group cannot be changed!");
    }
    
    $newArr = json_decode($group->getPermissions(), true);
    $section = UApi::getSection($sectionName);
    $constants = $section->getConstants();
    foreach ($perm as $p) {
      $split = explode("-", $p);
      if (sizeof($split) != 2 || !in_array($split[1], array("0", "1"))) {
        continue; // ignore invalid submits
      }
      foreach ($constants as $constant) {
        if (is_array($constant)) {
          $constant = $constant[0];
        }
        if ($split[0] == $constant) {
          $newArr[$sectionName][$constant] = ($split[1] == "1") ? true : false;
        }
      }
    }
    Factory::getApiGroupFactory()->set($group, ApiGroup::PERMISSIONS, json_encode($newArr));
  }
  
  /**
   * @param int $keyId
   * @param int $userId
   * @param int $groupId
   * @param string $startValid
   * @param string $endValid
   * @throws HTException
   */
  public static function editKey($keyId, $userId, $groupId, $startValid, $endValid) {
    $key = Factory::getApiKeyFactory()->get($keyId);
    $user = Factory::getUserFactory()->get($userId);
    $group = Factory::getApiGroupFactory()->get($groupId);
    if ($key == null) {
      throw new HTException("Invalid API key!");
    }
    else if ($user == null) {
      throw new HTException("Invalid user selected!");
    }
    else if ($group == null) {
      throw new HTException("Invalid API group selected!");
    }
    else if (MASK_API_KEYS && ($key->getUserId() != $userId)) {
      throw new HTException("Can't change key owner!");
    }
    
    Factory::getApiKeyFactory()->mset($key, [
        ApiKey::USER_ID => $user->getId(),
        ApiKey::API_GROUP_ID => $group->getId(),
        ApiKey::START_VALID => strtotime($startValid),
        ApiKey::END_VALID => strtotime($endValid)
      ]
    );
  }
  
  /**
   * @param int $userId
   * @param int $groupId
   * @throws HTException
   */
  public static function createKey($userId, $groupId) {
    $user = Factory::getUserFactory()->get($userId);
    $group = Factory::getApiGroupFactory()->get($groupId);
    if ($user == null) {
      throw new HTException("Invalid user ID!");
    }
    else if ($group == null) {
      throw new HTException("Invalid API group ID!");
    }
    
    do {
      // generate a unique key
      $accessKey = Util::randomString(30);
      $qF = new QueryFilter(ApiKey::ACCESS_KEY, $accessKey, "=");
      $count = Factory::getApiKeyFactory()->countFilter([Factory::FILTER => $qF]);
    } while ($count > 0);
    
    $key = new ApiKey(null, time(), time() + 3600 * 24 * 30, $accessKey, 0, $user->getId(), $group->getId());
    Factory::getApiKeyFactory()->save($key);
  }
  
  /**
   * @param int $keyId
   * @throws HTException
   */
  public static function deleteKey($keyId) {
    $key = Factory::getApiKeyFactory()->get($keyId);
    if ($key == null) {
      throw new HTException("Invalid API key ID!");
    }
    Factory::getApiKeyFactory()->delete($key);
  }
  
  /**
   * @param string $name
   */
  public static function createGroup($name) {
    $name = htmlentities($name, ENT_QUOTES, "UTF-8");
    $group = new ApiGroup(null, '{}', $name);
    Factory::getApiGroupFactory()->save($group);
  }
  
  /**
   * @param int $groupId
   * @throws HTException
   */
  public static function deleteGroup($groupId) {
    $group = Factory::getApiGroupFactory()->get($groupId);
    if ($group == null) {
      throw new HTException("Invalid group ID!");
    }
    $qF = new QueryFilter(ApiKey::API_GROUP_ID, $group->getId(), "=");
    if (Factory::getApiKeyFactory()->countFilter([Factory::FILTER => $qF]) > 0) {
      throw new HTException("You cannot delete an API group with members!");
    }
    Factory::getApiGroupFactory()->delete($group);
  }
}