<?php
use DBA\NotificationSetting;

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 10.11.16
 * Time: 14:38
 */


class NotificationHandler implements Handler {
  
  public function __construct($id = null) {
    // nothing required here
  }
  
  public function handle($action) {
    switch ($action) {
      case 'createNotification':
        $this->create();
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function create(){
    /** @var Login $LOGIN */
    global $FACTORIES, $NOTIFICATIONS, $LOGIN;
    
    $actionType = $_POST['actionType'];
    $notification = $_POST['notification'];
    $receiver = $_POST['receiver'];
    
    if(!in_array($notification, $NOTIFICATIONS)){
      UI::addMessage(UI::ERROR, "This notification is not available!");
      return;
    }
    else if(!in_array($actionType, DNotificationType::getAll())){
      UI::addMessage(UI::ERROR, "This actionType is not available!");
      return;
    }
    else if(strlen($receiver) == 0){
      UI::addMessage(UI::ERROR, "You need to fill in a receiver!");
      return;
    }
    else if(DNotificationType::getRequiredLevel($actionType) > $LOGIN->getLevel()){
      UI::addMessage(UI::ERROR, "You are not allowed to use this action type!");
      return;
    }
    $objectId = null;
    switch(DNotificationType::getObjectType($actionType)){
      case DNotificationObjectType::USER:
        if($LOGIN->getLevel() < DAccessLevel::ADMINISTRATOR){
          UI::addMessage(UI::ERROR, "You are not allowed to use user action types!");
          return;
        }
        if($_POST['users'] == "ALL"){
          break;
        }
        $user = $FACTORIES::getUserFactory()->get($_POST['users']);
        if($user == null){
          UI::addMessage(UI::ERROR, "Invalid user selected!");
          return;
        }
        $objectId = $user->getId();
        break;
      case DNotificationObjectType::AGENT:
        if($_POST['agents'] == "ALL"){
          break;
        }
        $agent = $FACTORIES::getAgentFactory()->get($_POST['agents']);
        if($agent == null){
          UI::addMessage(UI::ERROR, "Invalid agent selected!");
          return;
        }
        $objectId = $agent->getId();
        break;
      case DNotificationObjectType::HASHLIST:
        if($_POST['hashlists'] == "ALL"){
          break;
        }
        $hashlist = $FACTORIES::getHashlistFactory()->get($_POST['hashlists']);
        if($hashlist == null){
          UI::addMessage(UI::ERROR, "Invalid hashlist selected!");
          return;
        }
        $objectId = $hashlist->getId();
        break;
      case DNotificationObjectType::TASK:
        if($_POST['tasks'] == "ALL"){
          break;
        }
        $task = $FACTORIES::getAgentFactory()->get($_POST['tasks']);
        if($task == null){
          UI::addMessage(UI::ERROR, "Invalid task selected!");
          return;
        }
        $objectId = $task->getId();
        break;
    }
    
    $notificationSetting = new NotificationSetting(0, $actionType, $objectId, $notification, $LOGIN->getUserID(), $receiver, 1);
    $FACTORIES::getNotificationSettingFactory()->save($notificationSetting);
  }
}