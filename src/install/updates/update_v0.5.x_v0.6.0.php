<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\AgentBinary;
use DBA\QueryFilter;
use DBA\RightGroup;
use DBA\User;
use DBA\Factory;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__) . "/../../inc/db.php");
require_once(dirname(__FILE__) . "/../../dba/init.php");
require_once(dirname(__FILE__) . "/../../inc/Util.class.php");

echo "Apply updates...\n";

echo "Check agent binaries... ";
Util::checkAgentVersionLegacy("python", "0.1.4");
Util::checkAgentVersionLegacy("csharp", "0.52.2");
echo "\n";

echo "Create new permissions... ";
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `User` CHANGE `rightGroupId` `rightGroupId` INT(11) NULL");
// load all users and set permission group to null
$users = Factory::getUserFactory()->filter([]);
$uS = new UpdateSet(User::RIGHT_GROUP_ID, null);
$qF = new QueryFilter(User::USER_ID, 0, ">");
Factory::getUserFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
// apply table changes
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `RightGroup` ADD `permissions` TEXT NOT NULL");
Factory::getAgentFactory()->getDB()->query("ALTER TABLE `RightGroup` DROP `level`");
$qF = new QueryFilter(RightGroup::GROUP_NAME, 'Administrator', "=");
$adminGroup = Factory::getRightGroupFactory()->filter([Factory::FILTER => $qF], true);
// delete all right groups
$qF = new QueryFilter(RightGroup::RIGHT_GROUP_ID, 0, ">");
Factory::getRightGroupFactory()->massDeletion([Factory::FILTER => $qF]);
// add administrator group
$admins = new RightGroup(0, 'Administrators', 'ALL');
$admins = Factory::getRightGroupFactory()->save($admins);
// add other default group (with no permissions)
$others = new RightGroup(0, 'Other Users', '{}');
$others = Factory::getRightGroupFactory()->save($others);
// go through users and assign them to correct group
foreach ($users as $user) {
  if ($user->getRightGroupId() == $adminGroup->getId()) {
    // user was admin
    $user->setRightGroupId($admins->getId());
  }
  else {
    $user->setRightGroupId($others->getId());
  }
  Factory::getUserFactory()->update($user);
}
echo "OK\n";

echo "Update complete!\n";
