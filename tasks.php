<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("tasks");
$MENU->setActive("tasks_list");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'taskprio':
			// change task priority
			$task = intval($_POST["task"]);
			$prio = intval($_POST["priority"]);
			$res = $FACTORIES::getagentsFactory()->getDB->query("SELECT 1 FROM tasks WHERE tasks.priority=$prio AND tasks.id!=$task AND tasks.priority>0 AND SIGN(IFNULL(tasks.hashlist,0))=(SELECT SIGN(IFNULL(hashlist,0)) FROM tasks WHERE id=$task) LIMIT 1");
			if ($res->rowCount() == 1) {
				// must be unique
				$message = "<div class='alert alert-danger'>Each task has to have unique priority!</div>";
			} 
			else {
				$res = $FACTORIES::getagentsFactory()->getDB()->exec("UPDATE tasks SET priority=$prio WHERE id=$task");
				if (!$res) {
					$message = "<div class='alert alert-danger'>Could not change priority!</div>";
				}
				else{
					header("Location: tasks.php");
					die();
				}
			}
			break;
	}
}

if(isset($_GET['id'])){
	$TEMPLATE = new Template("tasks.detail");
	
	// show task details
	$taskSet = new DataSet();
	$DB = $FACTORIES::getagentsFactory()->getDB();
	$task = intval($_GET["id"]);
	$filter = intval(isset($_GET["all"]) ? $_GET["all"] : "");

	$res = $DB->query("SELECT tasks.*,hashlists.name AS hname,hashlists.format,hashlists.hashtype AS htype,hashtypes.description AS htypename,ROUND(chunks.cprogress) AS cprogress,SUM(assignments.speed*IFNULL(achunks.working,0)) AS taskspeed,IF(chunks.lastact>".(time()-$CONFIG->getVal('chunktimeout')).",1,0) AS active FROM tasks LEFT JOIN hashlists ON hashlists.id=tasks.hashlist LEFT JOIN hashtypes ON hashlists.hashtype=hashtypes.id LEFT JOIN (SELECT task,SUM(progress) AS cprogress,MAX(GREATEST(dispatchtime,solvetime)) AS lastact FROM chunks GROUP BY task) chunks ON chunks.task=tasks.id LEFT JOIN assignments ON assignments.task=tasks.id LEFT JOIN (SELECT DISTINCT agent,1 AS working FROM chunks WHERE task=$task AND GREATEST(dispatchtime,solvetime)>".(time()-$CONFIG->getVal('chunktimeout')).") achunks ON achunks.agent=assignments.agent WHERE tasks.id=$task GROUP BY tasks.id");
	$taskEntry = $res->fetch();
	if($taskEntry){
		$taskSet->setValues($taskEntry);
		$taskSet->addValue("filter", $filter);
		$res = $DB->query("SELECT dispatchtime,solvetime FROM chunks WHERE task={$taskEntry['id']} AND solvetime>dispatchtime ORDER BY dispatchtime ASC");
		$intervaly = array();
		foreach($res as $entry){
			$interval = array();
			$interval["start"] = $entry["dispatchtime"];
			$interval["stop"] = $entry["solvetime"];
			$intervaly[] = $interval;
		}
		$soucet=0;
		for ($i=1;$i<=count($intervaly);$i++) {
			if (isset($intervaly[$i]) && $intervaly[$i]["start"]<=$intervaly[$i-1]["stop"]) {
				$intervaly[$i]["start"]=$intervaly[$i-1]["start"];
				if ($intervaly[$i]["stop"]<$intervaly[$i-1]["stop"]) {
					$intervaly[$i]["stop"]=$intervaly[$i-1]["stop"];
				}
			} else {
				$soucet+=($intervaly[$i-1]["stop"]-$intervaly[$i-1]["start"]);
			}
		}
		$taskSet->addValue("soucet", $soucet);
		
		$res = $DB->query("SELECT ROUND((tasks.keyspace-SUM(tchunks.length))/SUM(tchunks.length*tchunks.active/tchunks.time)) AS eta FROM (SELECT SUM(chunks.length*chunks.rprogress/10000) AS length,SUM(chunks.solvetime-chunks.dispatchtime) AS time,IF(MAX(solvetime)>=".time()."-".$CONFIG->getVal('chunktimeout').",1,0) AS active FROM chunks WHERE chunks.solvetime>chunks.dispatchtime AND chunks.task={$taskEntry['id']} GROUP BY chunks.agent) tchunks CROSS JOIN tasks WHERE tasks.id={$taskEntry['id']}");
		$entry = $res->fetch();
		$taskSet->addValue('eta', $entry['eta']);
		
		$res = $DB->query("SELECT files.id,files.filename,files.size,files.secret FROM taskfiles JOIN files ON files.id=taskfiles.file WHERE task={$taskEntry['id']} ORDER BY filename");
		$res = $res->fetchAll();
		$attachFiles = array();
		foreach($res as $file){
			$set = new DataSet();
			$set->setValues($file);
			$attachFiles[] = $set;
		}
		$OBJECTS['attachFiles'] = $attachFiles;
		
		$agents = array();
		$res = $DB->query("SELECT agents.id,agents.active,agents.trusted,agents.name,assignments.benchmark,assignments.autoadjust,IF(chunks.lastact>=".(time()-$CONFIG->getVal('chunktimeout')).",1,0) AS working,assignments.speed,IFNULL(chunks.lastact,0) AS time,IFNULL(chunks.searched,0) AS searched,chunks.spent,IFNULL(chunks.cracked,0) AS cracked FROM agents JOIN assignments ON agents.id=assignments.agent JOIN tasks ON tasks.id=assignments.task LEFT JOIN (SELECT agent,SUM(progress) AS searched,SUM(solvetime-dispatchtime) AS spent,SUM(cracked) AS cracked,MAX(GREATEST(dispatchtime,solvetime)) AS lastact FROM chunks WHERE task=$task AND solvetime>dispatchtime GROUP BY agent) chunks ON chunks.agent=agents.id WHERE assignments.task=$task GROUP BY agents.id ORDER BY agents.id");
		$res = $res->fetchAll();
		foreach($res as $agent){
			$set = new DataSet();
			$set->setValues($agent);
			$agents[] = $set;
		}
		$OBJECTS['agents'] = $agents;
		
		$assignAgents = array();
		$res = $DB->query("SELECT agents.id,agents.name FROM agents LEFT JOIN assignments ON assignments.agent=agents.id WHERE IFNULL(assignments.task,0)!=$task ORDER BY agents.id ASC");
		$res = $res->fetchAll();
		foreach($res as $agent){
			$set = new DataSet();
			$set->setValues($agent);
			$assignAgents[] = $set;
		}
		$OBJECTS['assignAgents'] = $assignAgents;
		
		$add = "";
		if($filter != '1'){
			$add = "AND progress<length ";
		}
		$chunks = array();
		$res = $DB->query("SELECT chunks.*,GREATEST(chunks.dispatchtime,chunks.solvetime)-chunks.dispatchtime AS spent,agents.name FROM chunks LEFT JOIN agents ON chunks.agent=agents.id WHERE task=$task ".$add."ORDER BY chunks.dispatchtime DESC LIMIT 100");
		$res = $res->fetchAll();
		foreach($res as $chunk){
			$set = new DataSet();
			$set->setValues($chunk);
			$active = (max($chunk['dispatchtime'],$chunk['solvetime'])>time()-$CONFIG->getVal('chunktimeout') && $chunk['progress']<$chunk['length'] && $chunk["state"]<4);
			$chunks[] = $set;
		}
		$OBJECTS['chunks'] = $chunks;
		$OBJECTS['task'] = $taskSet;
	}
}
else{
	$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT tasks.id AS task,tasks.chunktime,tasks.priority,tasks.color,tasks.name,tasks.attackcmd,tasks.hashlist,tasks.progress,IFNULL(chunks.sumprog,0) AS sumprog,tasks.keyspace,IFNULL(chunks.pcount,0) AS pcount,IFNULL(chunks.ccount,0) AS ccount,IFNULL(taskfiles.secret,0) AS secret,IF(chunks.lastact>".(time()-$CONFIG->getVal('chunktimeout')).",1,0) AS active,IFNULL(assignments.acount,0) AS assigncount,taskfiles.fcount AS filescount,IFNULL(taskfiles.fsize,0) AS filesize,hashlists.name AS hname,hashlists.secret AS hsecret,IF(hashlists.cracked<hashlists.hashcount,1,0) AS hlinc FROM tasks JOIN hashlists ON hashlists.id=tasks.hashlist LEFT JOIN (SELECT task,SUM(cracked) AS pcount,COUNT(1) AS ccount,GREATEST(MAX(dispatchtime),MAX(solvetime)) AS lastact,SUM(progress) AS sumprog FROM chunks GROUP BY task) chunks ON chunks.task=tasks.id LEFT JOIN (SELECT task,COUNT(1) AS acount FROM assignments GROUP BY task) assignments ON assignments.task=tasks.id LEFT JOIN (SELECT taskfiles.task,COUNT(1) AS fcount,SUM(files.size) AS fsize,MAX(files.secret) AS secret FROM taskfiles JOIN files ON files.id=taskfiles.file GROUP BY taskfiles.task) taskfiles ON taskfiles.task=tasks.id ORDER BY active DESC, tasks.priority DESC, tasks.id ASC");
	$res = $res->fetchAll();
	$tasks = array();
	foreach($res as $task){
		$set = new DataSet();
		$set->setValues($task);
		$tasks[] = $set;
	}
	
	$OBJECTS['numTasks'] = sizeof($tasks);
	$OBJECTS['tasks'] = $tasks;
}
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




