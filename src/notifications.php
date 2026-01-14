<?php

use DBA\Agent;
use DBA\ContainFilter;
use DBA\Hashlist;
use DBA\NotificationSetting;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\User;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/startup/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::NOTIFICATIONS_VIEW_PERM);

Template::loadInstance("notifications");
UI::add('pageTitle', "Notifications");
Menu::get()->setActive("account_notifications");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $notificationHandler = new NotificationHandler();
  $notificationHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$qF = new QueryFilter(NotificationSetting::USER_ID, Login::getInstance()->getUserID(), "=");
$oF = new OrderFilter(NotificationSetting::ACTION, "ASC");
$notifications = Factory::getNotificationSettingFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
UI::add('notifications', $notifications);

$allAgents = array();
$oF = new OrderFilter(Agent::AGENT_NAME, "ASC");
if (AccessControl::getInstance()->hasPermission(DAccessControl::SERVER_CONFIG_ACCESS)) {
  $allAgents = Factory::getAgentFactory()->filter([Factory::ORDER => $oF]);
}
else {
  $qF = new QueryFilter(Agent::USER_ID, Login::getInstance()->getUserID(), "=");
  $allAgents = Factory::getAgentFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
}
UI::add('allAgents', $allAgents);

$agentNames = new DataSet();
foreach ($allAgents as $agent) {
  $agentNames->addValue($agent->getId(), $agent->getAgentName());
}
UI::add('agentNames', $agentNames);

$allApplies = new DataSet();
foreach ($notifications as $notification) {
  $notificationObject = DNotificationType::getObjectType($notification->getAction());
  $appliedTo = "N/A";
  if ($notification->getObjectId() != null) {
    switch ($notificationObject) {
      case DNotificationObjectType::TASK:
        $task = Factory::getTaskFactory()->get($notification->getObjectId());
        $appliedTo = "<a href='tasks.php?id=" . $task->getId() . "'>Task: " . $task->getTaskName() . " (" . $task->getId() . ")</a>";
        break;
      case DNotificationObjectType::HASHLIST:
        $hashlist = Factory::getHashlistFactory()->get($notification->getObjectId());
        $appliedTo = "<a href='hashlists.php?id=" . $hashlist->getId() . "'>Hashlist: " . $hashlist->getHashlistName() . " (" . $hashlist->getId() . ")</a>";
        break;
      case DNotificationObjectType::USER:
        $user = Factory::getUserFactory()->get($notification->getObjectId());
        $appliedTo = "User: " . $user->getUsername() . "(" . $user->getId() . ")";
        break;
      case DNotificationObjectType::AGENT:
        $agent = Factory::getAgentFactory()->get($notification->getObjectId());
        $appliedTo = "<a href='agents.php?id=" . $agent->getId() . "'>Agent: " . $agent->getAgentName() . " (" . $agent->getId() . ")</a>";
        break;
    }
  }
  $allApplies->addValue($notification->getId(), $appliedTo);
}
UI::add('allApplies', $allApplies);

$allNotifications = array();
foreach (HashtopolisNotification::getInstances() as $name => $notification) {
  $allNotifications[] = $name;
}
UI::add('allNotifications', $allNotifications);

$allowedActions = array();
$actionSettings = array();
foreach (DNotificationType::getAll() as $notificationType) {
  if (AccessControl::getInstance()->hasPermission(DNotificationType::getRequiredPermission($notificationType))) {
    $allowedActions[] = $notificationType;
    $actionSettings[] = "\"" . $notificationType . "\":\"" . DNotificationType::getObjectType($notificationType) . "\"";
  }
}
sort($allowedActions);
UI::add('allowedActions', $allowedActions);
UI::add('actionSettings', "{" . implode(",", $actionSettings) . "}");

$oF = new OrderFilter(Task::TASK_NAME, "ASC");
UI::add('allTasks', Factory::getTaskFactory()->filter([Factory::ORDER => $oF]));
$oF = new OrderFilter(Hashlist::HASHLIST_NAME, "ASC");
$qF1 = new ContainFilter(Hashlist::ACCESS_GROUP_ID, Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser(Login::getInstance()->getUser())));
$qF2 = new QueryFilter(Hashlist::IS_ARCHIVED, "0", "=");
UI::add('allHashlists', Factory::getHashlistFactory()->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]));
if (AccessControl::getInstance()->hasPermission(DAccessControl::USER_CONFIG_ACCESS)) {
  $oF = new OrderFilter(User::USERNAME, "ASC");
  UI::add('allUsers', Factory::getUserFactory()->filter([Factory::ORDER => $oF]));
}

echo Template::getInstance()->render(UI::getObjects());




