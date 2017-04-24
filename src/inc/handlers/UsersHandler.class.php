<?php
use DBA\Agent;
use DBA\NotificationSetting;
use DBA\QueryFilter;
use DBA\Session;
use DBA\User;

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 18.11.16
 * Time: 20:21
 */
class UsersHandler implements Handler {
  public function __construct($userId = null) {
    //nothing to do
  }
  
  public function handle($action) {
    switch ($action) {
      case 'deleteuser':
        $this->delete();
        break;
      case 'enable':
        $this->enable();
        break;
      case 'disable':
        $this->disable();
        break;
      case 'setrights':
        $this->setRights();
        break;
      case 'setpass':
        $this->setPassword();
        break;
      case 'create':
        $this->create();
        break;
      default:
        UI::addMessage(UI::ERROR, "Invalid action!");
        break;
    }
  }
  
  private function create() {
    /** @var $LOGIN Login */
    global $FACTORIES, $LOGIN;
    
    $username = htmlentities($_POST['username'], false, "UTF-8");
    $email = $_POST['email'];
    $group = $FACTORIES::getRightGroupFactory()->get($_POST['group']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) == 0) {
      UI::addMessage(UI::ERROR, "Invalid email address!");
      return;
    }
    else if (strlen($username) < 2) {
      UI::addMessage(UI::ERROR, "Username is too short!");
      return;
    }
    else if ($group == null) {
      UI::addMessage(UI::ERROR, "Invalid group!");
      return;
    }
    $qF = new QueryFilter("username", $username, "=");
    $res = $FACTORIES::getUserFactory()->filter(array($FACTORIES::FILTER => array($qF)));
    if ($res != null && sizeof($res) > 0) {
      UI::addMessage(UI::ERROR, "Username is already used!");
      return;
    }
    $newPass = Util::randomString(10);
    $newSalt = Util::randomString(20);
    $newHash = Encryption::passwordHash($newPass, $newSalt);
    $user = new User(0, $username, $email, $newHash, $newSalt, 1, 1, 0, time(), 600, $group->getId());
    $FACTORIES::getUserFactory()->save($user);
    //$tmpl = new Template("email.creation");
    //$obj = array('username' => $username, 'password' => $newPass, 'url' => $_SERVER[SERVER_NAME] . "/");
    //Util::sendMail($email, "Account at Hashtopussy", $tmpl->render($obj));
    //TODO: send proper email for created user
    
    Util::createLogEntry("User", $LOGIN->getUserID(), DLogEntry::INFO, "New User created: " . $user->getUsername());
    $payload = new DataSet(array(DPayloadKeys::USER => $user));
    NotificationHandler::checkNotifications(DNotificationType::USER_CREATED, $payload);
    
    header("Location: users.php");
    die();
  }
  
  private function setPassword() {
    /** @var Login $LOGIN */
    global $FACTORIES, $LOGIN;
    
    $user = $FACTORIES::getUserFactory()->get($_POST['user']);
    if ($user == null) {
      UI::addMessage(UI::ERROR, "Invalid user!");
      return;
    }
    else if ($user->getId() == $LOGIN->getUserID()) {
      UI::addMessage(UI::ERROR, "To change your own password go to your settings!");
      return;
    }
    
    $newSalt = Util::randomString(20);
    $newHash = Encryption::passwordHash($_POST['pass'], $newSalt);
    $user->setPasswordHash($newHash);
    $user->setPasswordSalt($newSalt);
    $user->setIsComputedPassword(0);
    $FACTORIES::getUserFactory()->update($user);
    UI::addMessage(UI::SUCCESS, "User password was updated successfully!");
  }
  
  private function setRights() {
    /** @var Login $LOGIN */
    global $FACTORIES, $LOGIN;
    
    $group = $FACTORIES::getRightGroupFactory()->get($_POST['group']);
    $user = $FACTORIES::getUserFactory()->get($_POST['user']);
    if ($user == null) {
      UI::addMessage(UI::ERROR, "Invalid user!");
      return;
    }
    else if ($group == null) {
      UI::addMessage(UI::ERROR, "Invalid group!");
      return;
    }
    else if ($user->getId() == $LOGIN->getUserID()) {
      UI::addMessage(UI::ERROR, "You cannot change your own rights!");
      return;
    }
    $user->setRightGroupId($group->getId());
    $FACTORIES::getUserFactory()->update($user);
    UI::addMessage(UI::SUCCESS, "Updated user rights successfully!");
  }
  
  private function disable() {
    /** @var Login $LOGIN */
    global $FACTORIES, $LOGIN;
    
    $user = $FACTORIES::getUserFactory()->get($_POST['user']);
    if ($user == null) {
      UI::addMessage(UI::ERROR, "Invalid user!");
      return;
    }
    else if ($user->getId() == $LOGIN->getUserID()) {
      UI::addMessage(UI::ERROR, "You cannot disable yourself!");
      return;
    }
    
    $qF = new QueryFilter(Session::USER_ID, $user->getId(), "=");
    $uS = new UpdateSet(Session::IS_OPEN, "0");
    $FACTORIES::getSessionFactory()->massUpdate(array($FACTORIES::FILTER => array($qF), $FACTORIES::UPDATE => array($uS)));
    $user->setIsValid(0);
    $FACTORIES::getUserFactory()->update($user);
    UI::addMessage(UI::SUCCESS, "User was disabled successfully!");
  }
  
  private function enable() {
    global $FACTORIES;
    
    $user = $FACTORIES::getUserFactory()->get($_POST['user']);
    if ($user == null) {
      UI::addMessage(UI::ERROR, "Invalid user!");
      return;
    }
    
    $user->setIsValid(1);
    $FACTORIES::getUserFactory()->update($user);
    UI::addMessage(UI::SUCCESS, "User account enabled successfully!");
  }
  
  private function delete() {
    /** @var Login $LOGIN */
    global $FACTORIES, $LOGIN;
    
    $user = $FACTORIES::getUserFactory()->get($_POST['user']);
    if ($user == null) {
      UI::addMessage(UI::ERROR, "Invalid user!");
      return;
    }
    else if ($user->getId() == $LOGIN->getUserID()) {
      UI::addMessage(UI::ERROR, "You cannot delete yourself!");
      return;
    }
    
    $payload = new DataSet(array(DPayloadKeys::USER => $user));
    NotificationHandler::checkNotifications(DNotificationType::USER_DELETED, $payload);
    
    $qF = new QueryFilter(NotificationSetting::OBJECT_ID, $user->getId(), "=");
    $notifications = $FACTORIES::getNotificationSettingFactory()->filter(array($FACTORIES::FILTER => $qF));
    foreach ($notifications as $notification) {
      if (DNotificationType::getObjectType($notification->getAction()) == DNotificationObjectType::USER) {
        $FACTORIES::getNotificationSettingFactory()->delete($notification);
      }
    }
    
    $qF = new QueryFilter(Agent::USER_ID, $user->getId(), "=");
    $uS = new UpdateSet(Agent::USER_ID, null);
    $FACTORIES::getAgentFactory()->massUpdate(array($FACTORIES::FILTER => array($qF), $FACTORIES::UPDATE => array($uS)));
    $qF = new QueryFilter(Session::USER_ID, $user->getId(), "=");
    $FACTORIES::getSessionFactory()->massDeletion(array($FACTORIES::FILTER => array($qF)));
    $FACTORIES::getUserFactory()->delete($user);
    
    header("Location: users.php");
    die();
  }
}