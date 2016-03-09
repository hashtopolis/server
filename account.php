<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

if(!$LOGIN->isLoggedin()){
	header("Location: index.php?err=4".time()."&fw=".urlencode($_SERVER['PHP_SELF']));
	die();
}

$TEMPLATE = new Template("account");
$MENU->setActive("account");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'setemail':
			$email = $_POST['email'];
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
				$message = "<div class='alert alert-danger'>Invalid email address!</div>";
				break;
			}
			$user = $LOGIN->getUser();
			$user->setEmail($email);
			$FACTORIES::getUserFactory()->update($user);
			header("Location: account.php");
			die();
	}
}

$group = $FACTORIES::getRightGroupFactory()->get($LOGIN->getUser()->getRightGroupId());

$OBJECTS['group'] = $group;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




