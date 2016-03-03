<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("tasks");
$MENU->setActive("tasks_list");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//TODO:
	}
}

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT tasks.id AS task,tasks.chunktime,tasks.priority,tasks.color,tasks.name,tasks.attackcmd,tasks.hashlist,tasks.progress,IFNULL(chunks.sumprog,0) AS sumprog,tasks.keyspace,IFNULL(chunks.pcount,0) AS pcount,IFNULL(chunks.ccount,0) AS ccount,IFNULL(taskfiles.secret,0) AS secret,IF(chunks.lastact>".(time()-$CONFIG->getVal('chunktimeout')).",1,0) AS active,IFNULL(assignments.acount,0) AS assigncount,taskfiles.fcount AS filescount,IFNULL(taskfiles.fsize,0) AS filesize,hashlists.name AS hname,hashlists.secret AS hsecret,IF(hashlists.cracked<hashlists.hashcount,1,0) AS hlinc FROM tasks JOIN hashlists ON hashlists.id=tasks.hashlist LEFT JOIN (SELECT task,SUM(cracked) AS pcount,COUNT(1) AS ccount,GREATEST(MAX(dispatchtime),MAX(solvetime)) AS lastact,SUM(progress) AS sumprog FROM chunks GROUP BY task) chunks ON chunks.task=tasks.id LEFT JOIN (SELECT task,COUNT(1) AS acount FROM assignments GROUP BY task) assignments ON assignments.task=tasks.id LEFT JOIN (SELECT taskfiles.task,COUNT(1) AS fcount,SUM(files.size) AS fsize,MAX(files.secret) AS secret FROM taskfiles JOIN files ON files.id=taskfiles.file GROUP BY taskfiles.task) taskfiles ON taskfiles.task=tasks.id ORDER BY active DESC, tasks.priority DESC, tasks.id ASC");
$res = $res->fetchAll();
$tasks = array();
foreach($res as $task){
	$set = new DataSet();
	$set->setValues($task);
	$tasks[] = $set;
}

$OBJECTS['tasks'] = $tasks;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




