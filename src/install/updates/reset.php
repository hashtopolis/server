<?php

use DBA\Factory;

require_once(dirname(__FILE__) . "/../../inc/startup/load.php");

/**
 * Use this file if you want to reset the password for an admin account. Fill in the values and just run it once.
 */

if (!isset($argv[1])) {
  die("You need to set an action!\n");
}

switch ($argv[1]) {
  case "password":
    $newPassword = "EnterPasswordToSetHere";
    $userId = "0"; // fill in the user id of the admin account
    
    $user = Factory::getUserFactory()->get($userId);
    if ($user == null) {
      die("User not found!\n");
    }
    
    $newSalt = Util::randomString(20);
    $newHash = Encryption::passwordHash($newPassword, $newSalt);
    $user->setPasswordHash($newHash);
    $user->setPasswordSalt($newSalt);
    $user->setIsComputedPassword(0);
    Factory::getUserFactory()->update($user);
    echo "User " . $user->getUsername() . " has a new password now\n!";
    break;
  case "pepper": // use this if you have overwritten the Encryption class and the pepper values should be generated again.
    $pepper = array(Util::randomString(50), Util::randomString(50), Util::randomString(50));
    $crypt = file_get_contents(dirname(__FILE__) . "/../../inc/Encryption.class.php");
    $crypt = str_replace("__PEPPER1__", $pepper[0], str_replace("__PEPPER2__", $pepper[1], str_replace("__PEPPER3__", $pepper[2], $crypt)));
    file_put_contents(dirname(__FILE__) . "/../../inc/Encryption.class.php", $crypt);
    echo "Peppers are generated new!\n";
    break;
}

