<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("newsuperhashlist");
$MENU->setActive("lists_snew");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//TODO:
	}
}

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT id,name,hashtype FROM hashlists WHERE format!=3 ORDER BY hashtype ASC, id ASC");
$res = $res->fetchAll();
$lists = array();
foreach($res as $list){
	$set = new DataSet();
	$set->setValues($list);
	$lists[] = $set;
}

$OBJECTS['lists'] = $lists;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




