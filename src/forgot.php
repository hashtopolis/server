<?php

require_once(dirname(__FILE__) . "/inc/load.php");

$TEMPLATE = new Template("forgot");
$message = "";

if (isset($_POST['action'])) {
  switch ($_POST['action']) {
    case 'resetpassword':
      $username = htmlentities(@$_POST['username'], false, "UTF-8");
      $email = @$_POST['email'];
      $qF = new QueryFilter("username", $username, "=");
      $res = $FACTORIES::getUserFactory()->filter(array('filter' => array($qF)));
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
      $newHash = Encryption::passwordHash($user->getUsername(), $newPass, $newSalt);
      $user->setPasswordHash($newHash);
      $user->setPasswordSalt($newSalt);
      $user->setIsComputedPassword(1);
      $FACTORIES::getUserFactory()->update($user);
      $tmpl = new Template("email.forgot");
      $obj = array('username' => $user->getUsername(), 'password' => $newPass);
      Util::sendMail($user->getEmail(), "Password reset", $tmpl->render($obj));
      $message = "<div class='alert alert-success'>Password resetted! You should receive an email soon.</div>";
  }
}

$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




