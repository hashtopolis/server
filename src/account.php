<?php

require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}

$TEMPLATE = new Template("account");
$MENU->setActive("account");
$message = "";

//catch agents actions here...
if (isset($_POST['action'])) {
  switch ($_POST['action']) {
    case 'setemail':
      $email = $_POST['email'];
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert alert-danger'>Invalid email address!</div>";
        break;
      }
      $user = $LOGIN->getUser();
      $user->setEmail($email);
      $FACTORIES::getUserFactory()->update($user);
      header("Location: account.php");
      die();
    case 'updatelifetime':
      $lifetime = intval($_POST['lifetime']);
      if ($lifetime < 60 || $lifetime > 24 * 3600) {
        $message = "<div class='alert alert-danger'>Lifetime must be larger than 1 minute and smaller than 2 days!</div>";
        break;
      }
      $user = $LOGIN->getUser();
      $user->setSessionLifetime($lifetime);
      $FACTORIES::getUserFactory()->update($user);
      header("Location: account.php");
      die();
    case 'changepass':
      $oldpass = $_POST['oldpass'];
      $newpass = $_POST['newpass'];
      $reppass = $_POST['reppass'];
      $user = $LOGIN->getUser();
      if (!Encryption::passwordVerify($user->getUsername(), $oldpass, $user->getPasswordSalt(), $user->getPasswordHash())) {
        $message = "<div class='alert alert-danger'>Your old password is wrong!</div>";
        break;
      }
      else if (strlen($newpass) < 4) {
        $message = "<div class='alert alert-danger'>Your password is too short!</div>";
        break;
      }
      else if ($newpass != $reppass) {
        $message = "<div class='alert alert-danger'>Your new passwords do not match!</div>";
        break;
      }
      $newSalt = Util::randomString(20);
      $newHash = Encryption::passwordHash($user->getUsername(), $newpass, $newSalt);
      $user->setPasswordHash($newHash);
      $user->setPasswordSalt($newSalt);
      $user->setIsComputedPassword(0);
      $FACTORIES::getUserFactory()->update($user);
      header("Location: account.php");
      die();
  }
}

$group = $FACTORIES::getRightGroupFactory()->get($LOGIN->getUser()->getRightGroupId());

$OBJECTS['group'] = $group;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




