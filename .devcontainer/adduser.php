<?php

// Based on https://github.com/kpeiruza/docker-hashtopolis-server

use DBA\AccessGroupUser;
use DBA\QueryFilter;
use DBA\RightGroup;
use DBA\User;
use DBA\Factory;

require_once(dirname(__FILE__) . "/../src/inc/load.php");

$username = "root";
$password = "hashtopolis";
$email = "root@localhost";

$PEPPER = array(Util::randomString(50), Util::randomString(50), Util::randomString(50));
$key = Util::randomString(40);
$conf = file_get_contents(dirname(__FILE__) . "/../src/inc/conf.php");
$conf = str_replace("__PEPPER1__", $PEPPER[0], str_replace("__PEPPER2__", $PEPPER[1], str_replace("__PEPPER3__", $PEPPER[2], $conf)));
$conf = str_replace("__CSRF__", $key, $conf);
file_put_contents(dirname(__FILE__) . "/../src/inc/conf.php", $conf);

require(dirname(__FILE__) . "/../src/inc/conf.php");
Factory::getAgentFactory()->getDB()->beginTransaction();

$qF = new QueryFilter(RightGroup::GROUP_NAME, "Administrator", "=");
$group = Factory::getRightGroupFactory()->filter([Factory::FILTER => $qF]);
$group = $group[0];
$newSalt = Util::randomString(20);
$CIPHER = $PEPPER[1] . $password . $newSalt;
$options = array('cost' => 12);
$newHash = password_hash($CIPHER, PASSWORD_BCRYPT, $options);

$user = new User(null, $username, $email, $newHash, $newSalt, 1, 1, 0, time(), 3600, $group->getId(), 0, "", "", "", "");
Factory::getUserFactory()->save($user);

// create default group
$group = AccessUtils::getOrCreateDefaultAccessGroup();
$groupUser = new AccessGroupUser(null, $group->getId(), $user->getId());
Factory::getAccessGroupUserFactory()->save($groupUser);

Factory::getAgentFactory()->getDB()->commit();

// Create voucher for dev agent
AgentUtils::createVoucher("devvoucher");
ConfigUtils::updateConfig(array("config_voucherDeletion" => "1"));
