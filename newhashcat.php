<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("newhashcat");
$MENU->setActive("hashcat_new");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//TODO:
	}
}

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT * FROM hashcatreleases ORDER BY time DESC LIMIT 1");
$res = $res->fetch();
$new = new DataSet();
if($res){
	$new->setValues($res);
}

$OBJECTS['new'] = $new;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




