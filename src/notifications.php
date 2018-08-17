<?php

use DBA\Agent;
use DBA\Hashlist;
use DBA\NotificationSetting;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\User;
use DBA\Factory;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */
/** @var array $NOTIFICATIONS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

$ACCESS_CONTROL->checkPermission(DViewControl::NOTIFICATIONS_VIEW_PERM);

$TEMPLATE = new Template("notifications");
$OBJECTS['pageTitle'] = "Notifications";
$MENU->setActive("account_notifications");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $notificationHandler = new NotificationHandler();
  $notificationHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$qF = new QueryFilter(NotificationSetting::USER_ID, $LOGIN->getUserID(), "=");
$oF = new OrderFilter(NotificationSetting::ACTION, "ASC");
$notifications = Factory::getNotificationSettingFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
$OBJECTS['notifications'] = $notifications;

$allAgents = array();
$oF = new OrderFilter(Agent::AGENT_NAME, "ASC");
if ($ACCESS_CONTROL->hasPermission(DAccessControl::SERVER_CONFIG_ACCESS)) {
  $allAgents = Factory::getAgentFactory()->filter([Factory::ORDER => $oF]);
}
else {
  $qF = new QueryFilter(Agent::USER_ID, $LOGIN->getUserID(), "=");
  $allAgents = Factory::getAgentFactory()->filter([Factory::FILTER => $qF, Factory::ORDER => $oF]);
}
$OBJECTS['allAgents'] = $allAgents;

$agentNames = new DataSet();
foreach ($allAgents as $agent) {
  $agentNames->addValue($agent->getId(), $agent->getAgentName());
}
$OBJECTS['agentNames'] = $agentNames;

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
$OBJECTS['allApplies'] = $allApplies;

$allNotifications = array();
foreach ($NOTIFICATIONS as $name => $notification) {
  $allNotifications[] = $name;
}
$OBJECTS['allNotifications'] = $allNotifications;

$allowedActions = array();
$actionSettings = array();
foreach (DNotificationType::getAll() as $notificationType) {
  if ($ACCESS_CONTROL->hasPermission(DNotificationType::getRequiredPermission($notificationType))) {
    $allowedActions[] = $notificationType;
    $actionSettings[] = "\"" . $notificationType . "\":\"" . DNotificationType::getObjectType($notificationType) . "\"";
  }
}
sort($allowedActions);
$OBJECTS['allowedActions'] = $allowedActions;
$OBJECTS['actionSettings'] = "{" . implode(",", $actionSettings) . "}";;

$oF = new OrderFilter(Task::TASK_NAME, "ASC");
$OBJECTS['allTasks'] = Factory::getTaskFactory()->filter([Factory::ORDER => $oF]);
$oF = new OrderFilter(Hashlist::HASHLIST_NAME, "ASC");
$OBJECTS['allHashlists'] = Factory::getHashlistFactory()->filter([Factory::ORDER => $oF]);
if ($ACCESS_CONTROL->hasPermission(DAccessControl::USER_CONFIG_ACCESS)) {
  $oF = new OrderFilter(User::USERNAME, "ASC");
  $OBJECTS['allUsers'] = Factory::getUserFactory()->filter([Factory::ORDER => $oF]);
}

echo $TEMPLATE->render($OBJECTS);




