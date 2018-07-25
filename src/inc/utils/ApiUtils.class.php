<?php
use DBA\QueryFilter;
use DBA\ApiKey;
use DBA\ApiGroup;

class ApiUtils {
  /**
   * @param int $groupId
   * @param array $perm
   * @param string $section
   * @throws HTException
   */
  public static function update($groupId, $perm, $sectionName) {
    global $FACTORIES;

    $group = $FACTORIES::getApiGroupFactory()->get($groupId);
    if($group == null){
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
    $group->setPermissions(json_encode($newArr));
    $FACTORIES::getApiGroupFactory()->update($group);
  }

  /**
   * @param int $keyId
   * @param int $userId
   * @param int $groupId
   * @param string $startValid
   * @param string $endValid
   * @throws HTException
   */
  public static function editKey($keyId, $userId, $groupId, $startValid, $endValid){
    global $FACTORIES;

    $key = $FACTORIES::getApiKeyFactory()->get($keyId);
    $user = $FACTORIES::getUserFactory()->get($userId);
    $group = $FACTORIES::getApiGroupFactory()->get($groupId);
    if($key == null){
      throw new HTException("Invalid API key!");
    }
    else if($user == null){
      throw new HTException("Invalid user selected!");
    }
    else if($group == null){
      throw new HTException("Invalid API group selected!");
    }
    $key->setUserId($user->getId());
    $key->setApiGroupId($group->getId());
    $key->setStartValid(strtotime($startValid));
    $key->setEndValid(strtotime($endValid));
    $FACTORIES::getApiKeyFactory()->update($key);
  }

  /**
   * @param int $userId
   * @param int $groupId
   * @throws HTException
   */
  public static function createKey($userId, $groupId){
    global $FACTORIES;

    $user = $FACTORIES::getUserFactory()->get($userId);
    $group = $FACTORIES::getApiGroupFactory()->get($groupId);
    if($user == null){
      throw new HTException("Invalid user ID!");
    }
    else if($group == null){
      throw new HTException("Invalid API group ID!");
    }
    do {
      // generate a unique key
      $accessKey = Util::randomString(30);
      $qF = new QueryFilter(ApiKey::ACCESS_KEY, $accessKey, "=");
      $count = $FACTORIES::getApiKeyFactory()->countFilter(array($FACTORIES::FILTER => $qF));
    } while($count > 0);
    $key = new ApiKey(0, time(), time() + 3600*30, $accessKey, 0, $user->getId(), $group->getId());
    $FACTORIES::getApiKeyFactory()->save($key);
  }

  /**
   * @param int $keyId
   * @throws HTException
   */
  public static function deleteKey($keyId){
    global $FACTORIES;

    $key = $FACTORIES::getApiKeyFactory()->get($keyId);
    if($key == null){
      throw new HTException("Invalid API key ID!");
    }
    $FACTORIES::getApiKeyFactory()->delete($key);
  }

  /**
   * @param string $name
   */
  public static function createGroup($name){
    global $FACTORIES;

    $name = htmlentities($name, ENT_QUOTES, "UTF-8");
    $group = new ApiGroup(0, '{}', $name);
    $FACTORIES::getApiGroupFactory()->save($group);
  }

  /**
   * @param int $groupId
   * @throws HTException
   */
  public static function deleteGroup($groupId){
    global $FACTORIES;

    $group = $FACTORIES::getApiGroupFactory()->get($groupId);
    if($group == null){
      throw new HTException("Invalid group ID!");
    }
    $qF = new QueryFilter(ApiKey::API_GROUP_ID, $group->getId(), "=");
    if($FACTORIES::getApiKeyFactory()->countFilter(array($FACTORIES::FILTER => $qF)) > 0){
      throw new HTException("You cannot delete an API group with members!");
    }
    $FACTORIES::getApiGroupFactory()->delete($group);
  }
}