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

$TEMPLATE = new Template("users");
$MENU->setActive("users_list");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//TODO:
	}
}

$users = array();
$res = $FACTORIES::getUserFactory()->filter(array());
foreach($res as $entry){
	$set = new DataSet();
	$set->addValue('user', $entry);
	$set->addValue('group', $FACTORIES::getRightGroupFactory()->get($entry->getRightGroupId()));
	$users[] = $set;
}

$OBJECTS['users'] = $users;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




