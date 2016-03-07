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

$configuration = array();
$all = $CONFIG->getAllValues();
foreach($all as $key => $value){
	$set = new DataSet();
	$set->addValue('item', $key);
	$set->addValue('value', $value);
	$configuration[] = $set;
}

$OBJECTS['configuration'] = $configuration;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




