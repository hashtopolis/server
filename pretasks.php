<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

if(!$LOGIN->isLoggedin()){
	header("Location: index.php?err=4".time()."&fw=".urlencode($_SERVER['PHP_SELF']));
	die();
}
else if($LOGIN->getLevel() < 20){
	$TEMPLATE = new Template("restricted");
	die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("pretasks");
$MENU->setActive("tasks_pre");
$message = "";

//catch actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//currently no actions
	}
}

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT tasks.id,tasks.name,tasks.color,tasks.attackcmd,tasks.priority,taskfiles.fcount AS filescount,IFNULL(taskfiles.fsize,0) AS filesize,IFNULL(taskfiles.secret,0) AS secret FROM tasks LEFT JOIN (SELECT taskfiles.task,COUNT(1) AS fcount,SUM(files.size) AS fsize,MAX(files.secret) AS secret FROM taskfiles JOIN files ON files.id=taskfiles.file GROUP BY taskfiles.task) taskfiles ON taskfiles.task=tasks.id WHERE tasks.hashlist IS NULL ORDER by tasks.priority DESC, tasks.id ASC");
$res = $res->fetchAll();
$tasks = array();
foreach($res as $task){
	$set = new DataSet();
	$set->setValues($task);
	$tasks[] = $set;
}

$OBJECTS['tasks'] = $tasks;
$OBJECTS['numPretasks'] = sizeof($tasks);
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




