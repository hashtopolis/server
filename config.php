<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("config");
$MENU->setActive("config");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'update':
			$DB = $FACTORIES::getagentsFactory()->getDB();
			foreach ($_POST as $item => $val) {
				if (substr($item, 0, 7) == "config_") {
					$item = $DB->quote(substr($item, 7));
					$val = $DB->quote($val);
					$DB->exec("INSERT INTO config (item, value) VALUES ($item, $val) ON DUPLICATE KEY UPDATE value=$val");
				}
			}
			$message = "<div class='alert alert-success'>Configuration updated successfully!</div>";
			break;
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




