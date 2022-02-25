<?php

use DBA\AccessGroupUser;
use DBA\QueryFilter;
use DBA\RightGroup;
use DBA\User;
use DBA\Factory;
use DBA\StoredValue;


//require_once(dirname(__FILE__) . "/../inc/db.php");

require_once(dirname(__FILE__) . "/../src/inc/load.php");

    $username = "root";
    $password = "hashtopolis";
    $email = "root@localhost";

    $pepper = array(Util::randomString(50), Util::randomString(50), Util::randomString(50));
    $key = Util::randomString(40);
    $conf = file_get_contents(dirname(__FILE__) . "/../inc/conf.php");
    $conf = str_replace("__PEPPER1__", $pepper[0], str_replace("__PEPPER2__", $pepper[1], str_replace("__PEPPER3__", $pepper[2], $conf)));
    $conf = str_replace("__CSRF__", $key, $conf);
    file_put_contents(dirname(__FILE__) . "/../inc/conf.php", $conf);
    
        Factory::getAgentFactory()->getDB()->beginTransaction();
        
        $qF = new QueryFilter(RightGroup::GROUP_NAME, "Administrator", "=");
        $group = Factory::getRightGroupFactory()->filter([Factory::FILTER => $qF]);
        $group = $group[0];
        $newSalt = Util::randomString(20);
        $CIPHER = $pepper[1] . $password . $newSalt;
	$options = array('cost' => 12);
	$newHash = password_hash($CIPHER, PASSWORD_BCRYPT, $options);

        $user = new User(null, $username, $email, $newHash, $newSalt, 1, 1, 0, time(), 3600, $group->getId(), 0, "", "", "", "");
        Factory::getUserFactory()->save($user);
        
        // create default group
        $group = AccessUtils::getOrCreateDefaultAccessGroup();
        $groupUser = new AccessGroupUser(null, $group->getId(), $user->getId());
        Factory::getAccessGroupUserFactory()->save($groupUser);
        
        Factory::getAgentFactory()->getDB()->commit();



