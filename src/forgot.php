<?php

use DBA\QueryFilter;
use DBA\User;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

$TEMPLATE = new Template("forgot");
$message = "";

if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  switch ($_POST['action']) {
    // TODO: put this into handler
    case 'resetpassword':
      $username = htmlentities(@$_POST['username'], ENT_QUOTES, "UTF-8");
      $email = @$_POST['email'];
      $qF = new QueryFilter(User::USERNAME, $username, "=");
      $res = $FACTORIES::getUserFactory()->filter(array($FACTORIES::FILTER => array($qF)));
      if ($res == null || sizeof($res) == 0) {
        $message = "<div class='alert alert-danger'>No such user!</div>";
        break;
      }
      $user = $res[0];
      if ($user->getEmail() != $email) {
        $message = "<div class='alert alert-danger'>No such user!</div>";
        break;
      }
      $newSalt = Util::randomString(20);
      $newPass = Util::randomString(10);
      $newHash = Encryption::passwordHash($newPass, $newSalt);
      $user->setPasswordHash($newHash);
      $user->setPasswordSalt($newSalt);
      $user->setIsComputedPassword(1);
      $FACTORIES::getUserFactory()->update($user);
      $tmpl = new Template("email/forgot");
      $obj = array('username' => $user->getUsername(), 'password' => $newPass);
      Util::sendMail($user->getEmail(), "Password reset", $tmpl->render($obj));
      $message = "<div class='alert alert-success'>Password resetted! You should receive an email soon.</div>";
  }
}

$OBJECTS['pageTitle'] = "Forgot Password";
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




