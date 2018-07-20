<?php
use DBA\User;
use DBA\QueryFilter;
use DBA\AccessGroupUser;
use DBA\Session;
use DBA\NotificationSetting;
use DBA\Agent;

class UserUtils {
  /**
   * @return User[]
   */
  public static function getUsers(){
    global $FACTORIES;

    return $FACTORIES::getUserFactory()->filter([]);
  }

  /**
   * @param int $userId
   * @param User $adminUser
   * @throws HTException
   */
  public static function deleteUser($userId, $adminUser) {
    global $FACTORIES;

    $user = UserUtils::getUser($userId);
    if ($user->getId() == $adminUser->getId()) {
      throw new HTException("You cannot delete yourself!");
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
    $qF = new QueryFilter(AccessGroupUser::USER_ID, $user->getId(), "=");
    $FACTORIES::getAccessGroupUserFactory()->massDeletion(array($FACTORIES::FILTER => array($qF)));
    $FACTORIES::getUserFactory()->delete($user);
  }

  /**
   * @param int $userId
   * @throws HTException
   */
  public static function enableUser($userId) {
    global $FACTORIES;

    $user = UserUtils::getUser($userId);
    $user->setIsValid(1);
    $FACTORIES::getUserFactory()->update($user);
  }

  /**
   * @param int $userId
   * @param User $adminUser
   * @throws HTException
   */
  public static function disableUser($userId, $adminUser) {
    global $FACTORIES;

    $user = UserUtils::getUser($userId);
    if ($user->getId() == $adminUser->getId()) {
      throw new HTException("You cannot disable yourself!");
    }

    $qF = new QueryFilter(Session::USER_ID, $user->getId(), "=");
    $uS = new UpdateSet(Session::IS_OPEN, "0");
    $FACTORIES::getSessionFactory()->massUpdate(array($FACTORIES::FILTER => array($qF), $FACTORIES::UPDATE => array($uS)));
    $user->setIsValid(0);
    $FACTORIES::getUserFactory()->update($user);
  }

  /**
   * @param int $userId
   * @param int $groupId
   * @param User $adminUser
   * @throws HTException
   */
  public static function setRights($userId, $groupId, $adminUser) {
    global $FACTORIES;

    $group = AccessControlUtils::getGroup($groupId);
    $user = UserUtils::getUser($userId);
    if ($user->getId() == $adminUser->getId()) {
      throw new HTException("You cannot change your own rights!");
    }
    $user->setRightGroupId($group->getId());
    $FACTORIES::getUserFactory()->update($user);
  }

  /**
   * @param int $userId
   * @param string $password
   * @param User $adminUser
   * @throws HTException
   */
  public static function setPassword($userId, $password, $adminUser) {
    global $FACTORIES;

    $user = UserUtils::getUser($userId);
    if ($user->getId() == $adminUser->getId()) {
      throw new HTException("To change your own password go to your settings!");
    }

    $newSalt = Util::randomString(20);
    $newHash = Encryption::passwordHash($password, $newSalt);
    $user->setPasswordHash($newHash);
    $user->setPasswordSalt($newSalt);
    $user->setIsComputedPassword(0);
    $FACTORIES::getUserFactory()->update($user);
  }

  /**
   * @param string $username
   * @param string $email
   * @param int $rightGroupId
   * @param User $adminUser
   * @throws HTException
   */
  public static function createUser($username, $email, $rightGroupId, $adminUser) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;

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
    $res = $FACTORIES::getUserFactory()->filter(array($FACTORIES::FILTER => array($qF)), true);
    if ($res != null) {
      throw new HTException("Username is already used!");
    }
    $newPass = Util::randomString(10);
    $newSalt = Util::randomString(20);
    $newHash = Encryption::passwordHash($newPass, $newSalt);
    $user = new User(0, $username, $email, $newHash, $newSalt, 1, 1, 0, time(), 3600, $group->getId(), 0, "", "", "", "");
    $FACTORIES::getUserFactory()->save($user);

    // add user to default group
    $group = AccessUtils::getOrCreateDefaultAccessGroup();
    $groupMember = new AccessGroupUser(0, $group->getId(), $user->getId());
    $FACTORIES::getAccessGroupUserFactory()->save($groupMember);

    $tmpl = new Template("email/creation");
    $tmplPlain = new Template("email/creation.plain");
    $obj = array('username' => $username, 'password' => $newPass, 'url' => Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL));
    Util::sendMail($email, "Account at " . APP_NAME, $tmpl->render($obj), $tmplPlain->render($obj));

    Util::createLogEntry("User", $adminUser->getId(), DLogEntry::INFO, "New User created: " . $user->getUsername());
    $payload = new DataSet(array(DPayloadKeys::USER => $user));
    NotificationHandler::checkNotifications(DNotificationType::USER_CREATED, $payload);
  }

  /**
   * @param int $userId
   * @throws HTException
   * @return User
   */
  public static function getUser($userId){
    global $FACTORIES;

    $user = $FACTORIES::getUserFactory()->get($userId);
    if($user == null){
      throw new HTException("Invalid user ID!");
    }
    return $user;
  }
}