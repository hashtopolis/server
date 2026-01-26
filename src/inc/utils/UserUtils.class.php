<?php

use DBA\UpdateSet;
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
  
  public static function userForgotPassword($username, $email) {
    $username = htmlentities($username, ENT_QUOTES, "UTF-8");
    $qF = new QueryFilter(User::USERNAME, $username, "=");
    $res = Factory::getUserFactory()->filter([Factory::FILTER => $qF]);
    if ($res == null || sizeof($res) == 0) {
      throw new HTException("No such user!");
    }
    $user = $res[0];
    if ($user->getEmail() != $email) {
      throw new HTException("No such user!");
    }
    $newSalt = Util::randomString(20);
    $newPass = Util::randomString(10);
    $newHash = Encryption::passwordHash($newPass, $newSalt);
    
    $tmpl = new Template("email/forgot");
    $tmplPlain = new Template("email/forgot.plain");
    $obj = array('username' => $user->getUsername(), 'password' => $newPass);
    if (Util::sendMail($user->getEmail(), "Password reset", $tmpl->render($obj), $tmplPlain->render($obj))) {
      Factory::getUserFactory()->mset($user, [User::PASSWORD_HASH => $newHash, User::PASSWORD_SALT => $newSalt, User::IS_COMPUTED_PASSWORD => 1]);
    }
    else {
      throw new HTException("Password reset failed because of an error when sending the email! Please check if PHP is able to send emails.");
    }
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
   * Changes the password for a given user after validating the old password and new password requirements.
   *
   * @param User   $user            The user object whose password is to be changed.
   * @param string $oldPassword     The user's current password (plain text).
   * @param string $newPassword     The new password to set (plain text).
   * @param string $confirmPassword Confirmation of the new password (plain text).
   *
   * @throws HTException If the old password is incorrect, the new password is too short,
   *                     the new passwords do not match, or the new password is the same as the old one.
   *
   * This method performs the following steps:
   * 1. Verifies the old password using the user's stored salt and hash.
   * 2. Checks that the new password meets minimum length requirements.
   * 3. Ensures the new password and confirmation match.
   * 4. Ensures the new password is different from the old password.
   * 5. Generates a new salt and hash for the new password.
   * 6. Updates the user's password hash, salt, and resets the computed password flag.
   */
  public static function changePassword($user, $oldPassword, $newPassword, $confirmPassword) {
    if (!Encryption::passwordVerify($oldPassword, $user->getPasswordSalt(), $user->getPasswordHash())) {
      throw new HttpError("Your old password is wrong!");
    }
    else if (strlen($newPassword) < 4) {
      throw new HttpError("Your password is too short!");
    }
    else if ($newPassword != $confirmPassword) {
      throw new HttpError("Your new passwords do not match!");
    }
    else if ($newPassword == $oldPassword) {
      throw new HttpError("Your new password is the same as the old one!");
    }
    $newSalt = Util::randomString(20);
    $newHash = Encryption::passwordHash($newPassword, $newSalt);
    
    Factory::getUserFactory()->mset($user, [User::PASSWORD_HASH => $newHash, User::PASSWORD_SALT => $newSalt, User::IS_COMPUTED_PASSWORD => 0]);
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
    else if (strlen($password) == 0) {
      throw new HTException("Password cannot be of zero length!");
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
   * @param bool $isValid
   * @return User
   * @throws HTException
   * @throws HttpConflict
   * @throws HttpError
   */
  public static function createUser(string $username, string $email, int $rightGroupId, User $adminUser, bool $isValid = true, int $session_lifetime=3600): User {
    $username = htmlentities($username, ENT_QUOTES, "UTF-8");
    $group = AccessControlUtils::getGroup($rightGroupId);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) == 0) {
      throw new HttpError("Invalid email address!");
    }
    else if (strlen($username) < 2) {
      throw new HttpError("Username is too short!");
    }
    else if ($group == null) {
      throw new HttpError("Invalid group!");
    }
    $qF = new QueryFilter("username", $username, "=");
    $res = Factory::getUserFactory()->filter([Factory::FILTER => $qF], true);
    if ($res != null) {
      throw new HttpConflict("Username is already used!");
    }
    $newPass = Util::randomString(10);
    $newSalt = Util::randomString(20);
    $newHash = Encryption::passwordHash($newPass, $newSalt);
    $user = new User(null, $username, $email, $newHash, $newSalt, $isValid ? 1: 0, 1, 0, time(), $session_lifetime, $group->getId(), 0, "", "", "", "");
    Factory::getUserFactory()->save($user);
    
    // add user to default group
    $group = AccessUtils::getOrCreateDefaultAccessGroup();
    $groupMember = new AccessGroupUser(null, $group->getId(), $user->getId());
    Factory::getAccessGroupUserFactory()->save($groupMember);
    
    // send email with generated password to user email
    $tmpl = new Template("email/creation");
    $tmplPlain = new Template("email/creation.plain");
    $obj = array('username' => $username, 'password' => $newPass, 'url' => Util::buildServerUrl() . SConfig::getInstance()->getVal(DConfig::BASE_URL));
    Util::sendMail($email, "Account at " . APP_NAME, $tmpl->render($obj), $tmplPlain->render($obj));
    
    // create log entry and check if notification sending is needed
    Util::createLogEntry("User", $adminUser->getId(), DLogEntry::INFO, "New User created: " . $user->getUsername());
    $payload = new DataSet(array(DPayloadKeys::USER => $user));
    NotificationHandler::checkNotifications(DNotificationType::USER_CREATED, $payload);
    
    return $user;
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
