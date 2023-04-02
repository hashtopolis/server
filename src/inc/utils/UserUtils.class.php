<?php

use DBA\User;
use DBA\QueryFilter;
use DBA\AccessGroupUser;
use DBA\Session;
use DBA\NotificationSetting;
use DBA\Agent;
use DBA\Factory;

class UserUtils {
  /**
   * @return User[]
   */
  public static function getUsers() {
    return Factory::getUserFactory()->filter([]);
  }
  
  /**
   * @param int $userId
   * @param User $adminUser
   * @throws HTException
   */
  public static function deleteUser($userId, $adminUser) {
    $user = UserUtils::getUser($userId);
    if ($user->getId() == $adminUser->getId()) {
      throw new HTException("You cannot delete yourself!");
    }
    
    $payload = new DataSet(array(DPayloadKeys::USER => $user));
    NotificationHandler::checkNotifications(DNotificationType::USER_DELETED, $payload);
    
    $qF = new QueryFilter(NotificationSetting::OBJECT_ID, $user->getId(), "=");
    $notifications = Factory::getNotificationSettingFactory()->filter([Factory::FILTER => $qF]);
    foreach ($notifications as $notification) {
      if (DNotificationType::getObjectType($notification->getAction()) == DNotificationObjectType::USER) {
        Factory::getNotificationSettingFactory()->delete($notification);
      }
    }
    
    $qF = new QueryFilter(Agent::USER_ID, $user->getId(), "=");
    $uS = new UpdateSet(Agent::USER_ID, null);
    Factory::getAgentFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
    $qF = new QueryFilter(Session::USER_ID, $user->getId(), "=");
    Factory::getSessionFactory()->massDeletion([Factory::FILTER => $qF]);
    $qF = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), "=");
    Factory::getAccessGroupUserFactory()->massDeletion([Factory::FILTER => $qF]);
    Factory::getUserFactory()->delete($user);
  }
  
  /**
   * @param int $userId
   * @throws HTException
   */
  public static function enableUser($userId) {
    $user = UserUtils::getUser($userId);
    Factory::getUserFactory()->set($user, User::IS_VALID, 1);
  }
  
  /**
   * @param int $userId
   * @param User $adminUser
   * @throws HTException
   */
  public static function disableUser($userId, $adminUser) {
    $user = UserUtils::getUser($userId);
    if ($user->getId() == $adminUser->getId()) {
      throw new HTException("You cannot disable yourself!");
    }
    
    $qF = new QueryFilter(Session::USER_ID, $user->getId(), "=");
    $uS = new UpdateSet(Session::IS_OPEN, "0");
    Factory::getSessionFactory()->massUpdate([Factory::FILTER => $qF, Factory::UPDATE => $uS]);
    Factory::getUserFactory()->set($user, User::IS_VALID, 0);
  }

  /**
   * @param int $userId
   * @throws HTException
   */
  public static function enableLDAP($userId) {
    $user = UserUtils::getUser($userId);
    Factory::getUserFactory()->set($user, User::IS_LDAP, 1);
  }

  /**
   * @param int $userId
   * @throws HTException
   */
  public static function disableLDAP($userId) {
    $user = UserUtils::getUser($userId);
    Factory::getUserFactory()->set($user, User::IS_LDAP, 0);
  }
  

  /**
   * @param int $userId
   * @param int $groupId
   * @param User $adminUser
   * @throws HTException
   */
  public static function setRights($userId, $groupId, $adminUser) {
    $group = AccessControlUtils::getGroup($groupId);
    $user = UserUtils::getUser($userId);
    if ($user->getId() == $adminUser->getId()) {
      throw new HTException("You cannot change your own rights!");
    }
    Factory::getUserFactory()->set($user, User::RIGHT_GROUP_ID, $group->getId());
  }
  
  /**
   * @param int $userId
   * @param string $password
   * @param User $adminUser
   * @throws HTException
   */
  public static function setPassword($userId, $password, $adminUser) {
    $user = UserUtils::getUser($userId);
    if ($user->getId() == $adminUser->getId()) {
      throw new HTException("To change your own password go to your settings!");
    }
    
    $newSalt = Util::randomString(20);
    $newHash = Encryption::passwordHash($password, $newSalt);
    
    Factory::getUserFactory()->mset($user, [User::PASSWORD_HASH => $newHash, User::PASSWORD_SALT => $newSalt, User::IS_COMPUTED_PASSWORD => 0]);
  }
  
  /**
   * @param string $username
   * @param string $email
   * @param int $rightGroupId
   * @param User $adminUser
   * @throws HTException
   */
  public static function createUser($username, $email, $rightGroupId, $adminUser) {
    $username = htmlentities($username, ENT_QUOTES, "UTF-8");
    $group = AccessControlUtils::getGroup($rightGroupId);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) == 0) {
      throw new HTException("Invalid email address!");
    }
    else if (strlen($username) < 2) {
      throw new HTException("Username is too short!");
    }
    else if ($group == null) {
      throw new HTException("Invalid group!");
    }
    $qF = new QueryFilter("username", $username, "=");
    $res = Factory::getUserFactory()->filter([Factory::FILTER => $qF], true);
    if ($res != null) {
      throw new HTException("Username is already used!");
    }
    $newPass = Util::randomString(10);
    $newSalt = Util::randomString(20);
    $newHash = Encryption::passwordHash($newPass, $newSalt);
    $user = new User(null, $username, $email, $newHash, $newSalt, 1, 0,1, 0, time(), 3600, $group->getId(), 0, "", "", "", "");
    Factory::getUserFactory()->save($user);
    
    // add user to default group
    $group = AccessUtils::getOrCreateDefaultAccessGroup();
    $groupMember = new AccessGroupUser(null, $group->getId(), $user->getId());
    Factory::getAccessGroupUserFactory()->save($groupMember);
    
    $tmpl = new Template("email/creation");
    $tmplPlain = new Template("email/creation.plain");
    $obj = array('username' => $username, 'password' => $newPass, 'url' => Util::buildServerUrl() . SConfig::getInstance()->getVal(DConfig::BASE_URL));
    Util::sendMail($email, "Account at " . APP_NAME, $tmpl->render($obj), $tmplPlain->render($obj));
    
    Util::createLogEntry("User", $adminUser->getId(), DLogEntry::INFO, "New User created: " . $user->getUsername());
    $payload = new DataSet(array(DPayloadKeys::USER => $user));
    NotificationHandler::checkNotifications(DNotificationType::USER_CREATED, $payload);
  }
  
  /**
   * @param int $userId
   * @return User
   * @throws HTException
   */
  public static function getUser($userId) {
    $user = Factory::getUserFactory()->get($userId);
    if ($user == null) {
      throw new HTException("Invalid user ID!");
    }
    return $user;
  }
}