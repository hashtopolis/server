<?php

require_once(dirname(__FILE__) . "/inc/load.php");

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF']));
  die();
}
else if ($LOGIN->getLevel() < 50) {
  $TEMPLATE = new Template("restricted");
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("users/index");
$MENU->setActive("users_list");
$message = "";

//catch agents actions here...
if (isset($_POST['action'])) {
  switch ($_POST['action']) {
    case 'deleteuser':
      $user = $FACTORIES::getUserFactory()->get($_POST['user']);
      if ($user == null) {
        $message = "<div class='alert alert-danger'>Invalid user!</div>";
        break;
      }
      else if ($user->getId() == $LOGIN->getUserID()) {
        $message = "<div class='alert alert-danger'>You cannot delete yourself!</div>";
        break;
      }
      $FACTORIES::getagentsFactory()->getDB()->query("UPDATE agents SET userId='0' WHERE userId=" . $user->getId());
      $FACTORIES::getUserFactory()->delete($user);
      header("Location: users.php");
      die();
    case 'enable':
      $user = $FACTORIES::getUserFactory()->get($_POST['user']);
      if ($user == null) {
        $message = "<div class='alert alert-danger'>Invalid user!</div>";
        break;
      }
      $user->setIsValid(1);
      $FACTORIES::getUserFactory()->update($user);
      header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
      die();
    case 'disable':
      $user = $FACTORIES::getUserFactory()->get($_POST['user']);
      if ($user == null) {
        $message = "<div class='alert alert-danger'>Invalid user!</div>";
        break;
      }
      else if ($user->getId() == $LOGIN->getUserID()) {
        $message = "<div class='alert alert-danger'>You cannot disable yourself!</div>";
        break;
      }
      $FACTORIES::getUserFactory()->getDB()->query("UPDATE Session SET isOpen='0' WHERE userId=" . $user->getId());
      $user->setIsValid(0);
      $FACTORIES::getUserFactory()->update($user);
      header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
      die();
    case 'setrights':
      $group = $FACTORIES::getRightGroupFactory()->get($_POST['group']);
      $user = $FACTORIES::getUserFactory()->get($_POST['user']);
      if ($user == null) {
        $message = "<div class='alert alert-danger'>Invalid user!</div>";
        break;
      }
      else if ($group == null) {
        $message = "<div class='alert alert-danger'>Invalid group!</div>";
        break;
      }
      else if ($user->getId() == $LOGIN->getUserID()) {
        $message = "<div class='alert alert-danger'>You cannot change your own rights!</div>";
        break;
      }
      $user->setRightGroupId($group->getId());
      $FACTORIES::getUserFactory()->update($user);
      header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
      die();
    case 'setpass':
      $user = $FACTORIES::getUserFactory()->get($_POST['user']);
      if ($user == null) {
        $message = "<div class='alert alert-danger'>Invalid user!</div>";
        break;
      }
      else if ($user->getId() == $LOGIN->getUserID()) {
        $message = "<div class='alert alert-danger'>To change your own password go to your settings!</div>";
        break;
      }
      $newSalt = Util::randomString(20);
      $newHash = Encryption::passwordHash($user->getUsername(), $_POST['pass'], $newSalt);
      $user->setPasswordHash($newHash);
      $user->setPasswordSalt($newSalt);
      $user->setIsComputedPassword(0);
      $FACTORIES::getUserFactory()->update($user);
      header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
      die();
  }
}

if (isset($_GET['id'])) {
  $user = $FACTORIES::getUserFactory()->get($_GET['id']);
  if ($user == null) {
    $message = "<div class='alert alert-danger'>Invalid user!</div>";
    $TEMPLATE = new Template("error");
  }
  else {
    $OBJECTS['user'] = $user;
    $OBJECTS['groups'] = $FACTORIES::getRightGroupFactory()->filter(array());
    $TEMPLATE = new Template("users.detail");
  }
}
else {
  $users = array();
  $res = $FACTORIES::getUserFactory()->filter(array());
  foreach ($res as $entry) {
    $set = new DataSet();
    $set->addValue('user', $entry);
    $set->addValue('group', $FACTORIES::getRightGroupFactory()->get($entry->getRightGroupId()));
    $users[] = $set;
  }
  
  $OBJECTS['allUsers'] = $users;
  $OBJECTS['numUsers'] = sizeof($users);
}

$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




