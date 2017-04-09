<?php

use DBA\Agent;
use DBA\Hashlist;
use DBA\NotificationSetting;
use DBA\OrderFilter;
use DBA\QueryFilter;
use DBA\Task;
use DBA\User;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */
/** @var array $NOTIFICATIONS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::USER) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("notifications");
$MENU->setActive("account_notifications");

//catch actions here...
if (isset($_POST['action'])) {
  $notificationHandler = new NotificationHandler();
  $notificationHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$qF = new QueryFilter(NotificationSetting::USER_ID, $LOGIN->getUserID(), "=");
$oF = new OrderFilter(NotificationSetting::ACTION, "ASC");
$notifications = $FACTORIES::getNotificationSettingFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF));
$OBJECTS['notifications'] = $notifications;

$allAgents = array();
$oF = new OrderFilter(Agent::AGENT_NAME, "ASC");
if ($LOGIN->getLevel() >= DAccessLevel::SUPERUSER) {
  $allAgents = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::ORDER => $oF));
}
else {
  $qF = new QueryFilter(Agent::USER_ID, $LOGIN->getUserID(), "=");
  $allAgents = $FACTORIES::getAgentFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF));
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
        $task = $FACTORIES::getTaskFactory()->get($notification->getObjectId());
        $appliedTo = "<a href='tasks.php?id=" . $task->getId() . "'>Task: " . $task->getTaskName() . " (" . $task->getId() . ")</a>";
        break;
      case DNotificationObjectType::HASHLIST:
        $hashlist = $FACTORIES::getHashlistFactory()->get($notification->getObjectId());
        $appliedTo = "<a href='hashlists.php?id=" . $hashlist->getId() . "'>Hashlist: " . $hashlist->getHashlistName() . " (" . $hashlist->getId() . ")</a>";
        break;
      case DNotificationObjectType::USER:
        $user = $FACTORIES::getUserFactory()->get($notification->getObjectId());
        $appliedTo = "User: " . $user->getUsername() . "(" . $user->getId() . ")";
        break;
      case DNotificationObjectType::AGENT:
        $agent = $FACTORIES::getAgentFactory()->get($notification->getObjectId());
        $appliedTo = "<a href='agents.php?id=" . $agent->getId() . "'>Hashlist: " . $agent->getAgentName() . " (" . $agent->getId() . ")</a>";
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
  if (DNotificationType::getRequiredLevel($notificationType) <= $LOGIN->getLevel()) {
    $allowedActions[] = $notificationType;
    $actionSettings[] = "\"" . $notificationType . "\":\"" . DNotificationType::getObjectType($notificationType) . "\"";
  }
}
sort($allowedActions);
$OBJECTS['allowedActions'] = $allowedActions;
$OBJECTS['actionSettings'] = "{" . implode(",", $actionSettings) . "}";;

$qF = new QueryFilter(Task::HASHLIST_ID, null, "<>");
$oF = new OrderFilter(Task::TASK_NAME, "ASC");
$OBJECTS['allTasks'] = $FACTORIES::getTaskFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF));
$oF = new OrderFilter(Hashlist::HASHLIST_NAME, "ASC");
$OBJECTS['allHashlists'] = $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::ORDER => $oF));
if ($LOGIN->getLevel() >= DAccessLevel::ADMINISTRATOR) {
  $oF = new OrderFilter(User::USERNAME, "ASC");
  $OBJECTS['allUsers'] = $FACTORIES::getUserFactory()->filter(array($FACTORIES::ORDER => $oF));
}

echo $TEMPLATE->render($OBJECTS);




