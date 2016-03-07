<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("config");
$MENU->setActive("config");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//TODO:
	}
}

$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




