<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("agents");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'agentactive':
			$agent = $FACTORIES::getagentsFactory()->get($_POST["agent"]);
			if($agent === null){
				$message = "<div class='alert alert-danger'>Could not change agent activity!</div>";
			}
			else if($agent->getActive() == 1){
				$agent->setActive(0);
				$FACTORIES::getagentsFactory()->update($agent);
			}
			else{
				$agent->setActive(1);
				$FACTORIES::getagentsFactory()->update($agent);
			}
			if(strlen($message) == 0){
				header("Location: agents.php");
				die();
			}
			break;
	}
}

$ans = $FACTORIES::getagentsFactory()->getDB()->query("SELECT id,name FROM tasks WHERE hashlist IS NOT NULL ORDER BY id ASC");
$ans = $ans->fetchAll();
$allTasks = array();
foreach($ans as $task){
	$set = new DataSet();
	$set->setValues($task);
	$allTasks[] = $set;
}

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT agents.id,agents.uid,agents.active,agents.trusted,agents.cputype,agents.gpubrand,agents.gpudriver,agents.gpus,agents.hcversion,agents.lastact,agents.lasttime,agents.lastip,assignments.task,assignments.speed,agents.os,agents.name,IF(IFNULL(chunks.time,0)>".(time() - $CONFIG->getVal('chunktimeout')).",1,0) AS working FROM agents LEFT JOIN assignments ON agents.id=assignments.agent LEFT JOIN tasks ON assignments.task=tasks.id LEFT JOIN (SELECT agent,MAX(GREATEST(dispatchtime,solvetime)) AS time FROM chunks GROUP BY agent) chunks ON chunks.agent=agents.id ORDER BY agents.id ASC");
$res = $res->fetchAll();
$agents = array();
foreach($res as $agent){
	$set = new DataSet();
	$set->setValues($agent);
	$agents[] = $set;
}
$OBJECTS['numAgents'] = sizeof($agents);

$OBJECTS['allTasks'] = $allTasks;
$OBJECTS['sets'] = $agents;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




