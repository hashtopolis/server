<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

if(!$LOGIN->isLoggedin()){
	header("Location: index.php?err=4".time()."&fw=".urlencode($_SERVER['PHP_SELF']));
	die();
}
else if($LOGIN->getLevel() < 40){
	$TEMPLATE = new Template("restricted");
	die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("hashtypes");
$MENU->setActive("config_hashtypes");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		
	}
}

$res = $DB->query("SELECT * FROM hashtypes WHERE 1 ORDER BY id");
$res = $res->fetchAll();
$hashtypes = array();
foreach($res as $type){
	$hashtypes[] = new DataSet($type);
}

$OBJECTS['hashtypes'] = $hashtypes;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




