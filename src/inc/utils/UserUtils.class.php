<?php

class UserUtils {
  /**
   * @param int $userId 
   * @throws HTException 
   * @return User
   */
  public static function getUser($userId){
    global $FACTORIES;

    $user = $FACTORIES::getUserFactory()->get($userId);
    if($user == null){
      throw new HTException("Invalid user ID!");
    }
    return $user;
  }
}