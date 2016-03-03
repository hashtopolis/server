<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("hashlists");
$MENU->setActive("lists_norm");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//TODO:
	}
}

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT hashlists.id,hashlists.name,hashlists.hashtype,hashlists.format,hashlists.hashcount,hashlists.cracked,hashlists.secret,hashtypes.description FROM hashlists LEFT JOIN hashtypes ON hashtypes.id=hashlists.hashtype WHERE format!=3 ORDER BY id ASC");
$res = $res->fetchAll();
$hashlists = array();
foreach($res as $list){
	$set = new DataSet();
	$set->setValues($list);
	$hashlists[] = $set;
}

$OBJECTS['hashlists'] = $hashlists;
$OBJECTS['numHashlists'] = sizeof($hashlists);
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




