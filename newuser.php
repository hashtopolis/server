<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

if(!$LOGIN->isLoggedin()){
	header("Location: index.php?err=4".time()."&fw=".urlencode($_SERVER['PHP_SELF']));
	die();
}
else if($LOGIN->getLevel() < 50){
	$TEMPLATE = new Template("restricted");
	die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("newuser");
$MENU->setActive("users_new");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'create':
			$username = htmlentities($_POST['username'], false, "UTF-8");
			$email = $_POST['email'];
			$group = $FACTORIES::getRightGroupFactory()->get($_POST['group']);
			if(!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) == 0){
				$message = "<div class='alert alert-danger'>Invalid email address!</div>";
				break;
			}
			else if(strlen($username) < 2){
				$message = "<div class='alert alert-danger'>Username is too short!</div>";
				break;
			}
			else if($group == null){
				$message = "<div class='alert alert-danger'>Invalid group!</div>";
				break;
			}
			$qF = new QueryFilter("username", $username, "=");
			$res = $FACTORIES::getUserFactory()->filter(array('filter' => array($qF)));
			if($res != null && sizeof($res) > 0){
				$message = "<div class='alert alert-danger'>Username is already used!</div>";
				break;
			}
			$newPass = Util::randomString(10);
			$newSalt = Util::randomString(20);
			$newHash = Encryption::passwordHash($username, $newPass, $newSalt);
			$user = new User(0, $username, $email, $newHash, $newSalt, 1, 1, 0, time(), 600, $group->getId());
			$FACTORIES::getUserFactory()->save($user);
			$tmpl = new Template("email.creation");
			$obj = array('username' => $username, 'password' => $newPass, 'url' => $_SERVER[SERVER_NAME]."/");
			Util::sendMail($email, "Account at Hashtopussy", $tmpl->render($obj));
			header("Location: users.php");
			die();
	}
}

$OBJECTS['groups'] = $FACTORIES::getRightGroupFactory()->filter(array());
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




