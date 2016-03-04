<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("newtask");
$MENU->setActive("tasks_new");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//TODO:
	}
}

$orig = 0;
$copy = new DataSet();
$copy->addValue("name", "");
$copy->addValue("cmd", "");
$copy->addValue("chunksize", $CONFIG->getVal("chunktime"));
$copy->addValue("status", $CONFIG->getVal("statustimer"));
$copy->addValue("adjust", 0);
$copy->addValue("color", "");
$copy->addValue("hlist", "");
if (isset($_GET["id"])) {
	//copied from a task
	$orig = intval($_GET["id"]);
	if ($orig > 0) {
		$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT name,attackcmd,chunktime,statustimer,autoadjust,hashlist,color FROM tasks WHERE id=$orig");
		if ($task = $res->fetch()) {
			$copy->addValue('name', $task["name"]." (copy)");
			$copy->addValue('cmd', $task["attackcmd"]);
			$copy->addValue('chunksize', $task["chunktime"]);
			$copy->addValue('status', $task["statustimer"]);
			$copy->addValue('adjust', $task["autoadjust"]);
			$copy->addValue('hlist', $task["hashlist"]);
			$copy->addValue('color', $task["color"]);
			if ($copy->getVal('hlist') == ""){
				$hlist = "preconf";
			}
		} else {
			$orig = 0;
		}
	}
}

$OBJECTS['copy'] = $copy;

$lists = array();
$set = new DataSet();
$set->addValue('id', "");
$set->addValue("name", "(please select)");
$lists[] = $set;
$set = new DataSet();
$set->addValue('id', "preconf");
$set->addValue("name", "(pre-configured task)");
$lists[] = $set;
$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT id,name FROM hashlists ORDER BY id ASC");
$res = $res->fetchAll();
foreach($res as $list){
	$set = new DataSet();
	$set->setValues($list);
	$lists[] = $set;
}

$OBJECTS['lists'] = $lists;

$files = array();
$res = null;
if($orig > 0){
	$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT files.*,SIGN(IFNULL(taskfiles.task,0)) AS che FROM files LEFT JOIN taskfiles ON taskfiles.file=files.id AND taskfiles.task=$orig ORDER BY filename ASC");
}
else{
	$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT files.*,0 AS che FROM files ORDER BY filename ASC");
}
if($res != null){
	$res = $res->fetchAll();
	foreach($res as $file){
		$set = new DataSet();
		$set->setValues($file);
		$files[] = $set;
	}
}

$OBJECTS['files'] = $files;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




