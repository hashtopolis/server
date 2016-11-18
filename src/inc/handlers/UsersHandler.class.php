<?php
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
      //TODO: add handles for creating new user
      default:
        UI::addMessage("danger", "Invalid action!");
        break;
    }
  }
  
  private function setPassword(){
    global $FACTORIES, $LOGIN;
  
    $user = $FACTORIES::getUserFactory()->get($_POST['user']);
    if ($user == null) {
      UI::addMessage("danger", "Invalid user!");
      return;
    }
    else if ($user->getId() == $LOGIN->getUserID()) {
      UI::addMessage("danger", "To change your own password go to your settings!");
      return;
    }
    
    $newSalt = Util::randomString(20);
    $newHash = Encryption::passwordHash($user->getUsername(), $_POST['pass'], $newSalt);
    $user->setPasswordHash($newHash);
    $user->setPasswordSalt($newSalt);
    $user->setIsComputedPassword(0);
    $FACTORIES::getUserFactory()->update($user);
    UI::addMessage("success", "User password was updated successfully!");
  }
  
  private function setRights(){
    global $FACTORIES, $LOGIN;
  
    $group = $FACTORIES::getRightGroupFactory()->get($_POST['group']);
    $user = $FACTORIES::getUserFactory()->get($_POST['user']);
    if ($user == null) {
      UI::addMessage("danger", "Invalid user!");
      return;
    }
    else if ($group == null) {
      UI::addMessage("danger", "Invalid group!");
      return;
    }
    else if ($user->getId() == $LOGIN->getUserID()) {
      UI::addMessage("danger", "You cannot change your own rights!");
      return;
    }
    $user->setRightGroupId($group->getId());
    $FACTORIES::getUserFactory()->update($user);
    UI::addMessage("success", "Updated user rights successfully!");
  }
  
  private function disable(){
    global $FACTORIES, $LOGIN;
    
    $user = $FACTORIES::getUserFactory()->get($_POST['user']);
    if ($user == null) {
      UI::addMessage("danger", "Invalid user!");
      return;
    }
    else if ($user->getId() == $LOGIN->getUserID()) {
      UI::addMessage("danger", "You cannot disable yourself!");
      return;
    }
    
    $qF = new QueryFilter("userId", $user->getId(), "=");
    $uS = new UpdateSet("isOpen", "0");
    $FACTORIES::getSessionFactory()->massUpdate(array('filter' => array($qF), 'update' => array($uS)));
    $user->setIsValid(0);
    $FACTORIES::getUserFactory()->update($user);
    UI::addMessage("success", "User was disabled successfully!");
  }
  
  private function enable(){
    global $FACTORIES;
  
    $user = $FACTORIES::getUserFactory()->get($_POST['user']);
    if ($user == null) {
      UI::addMessage("danger", "Invalid user!");
      return;
    }
    
    $user->setIsValid(1);
    $FACTORIES::getUserFactory()->update($user);
    UI::addMessage("success", "User account enabled successfully!");
  }
  
  private function delete(){
    global $FACTORIES, $LOGIN;
  
    $user = $FACTORIES::getUserFactory()->get($_POST['user']);
    if ($user == null) {
      UI::addMessage("danger", "Invalid user!");
      return;
    }
    else if ($user->getId() == $LOGIN->getUserID()) {
      UI::addMessage("danger", "You cannot delete yourself!");
      return;
    }
    
    $qF = new QueryFilter("userId", $user->getId(), "=");
    $uS = new UpdateSet("userId", "0");
    $FACTORIES::getAgentFactory()->massUpdate(array('filter' => array($qF), 'update' => array($uS)));
    $FACTORIES::getUserFactory()->delete($user);
    UI::addMessage("success", "User was deleted successfully!");
  }
}