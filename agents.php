<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("agents");
$MENU->setActive("agents_list");
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
		case 'agentdelete':
			$agent = $FACTORIES::getagentsFactory()->get($_POST['agent']);
			$FACTORIES::getagentsFactory()->getDB()->query("START TRANSACTION");
			if (Util::deleteAgent($agent)) {
				$FACTORIES::getagentsFactory()->getDB()->query("COMMIT");
				header("Location: agents.php");
				die();
			} 
			else {
				$FACTORIES::getagentsFactory()->getDB()->query("ROLLBACK");
				$message = "<div class='alert alert-danger'>Could not delete agent!</div>";
			}
			break;
		case 'agentassign':
			$agent = $FACTORIES::getagentsFactory()->get($_POST["agent"]);
			$ans = true;
			if($agent === null){
				$message = "<div class='alert alert-danger'>Invalid agent id!</div>";
			}
			else if(intval($_POST['task']) == 0){
				//unassign
				$FACTORIES::getagentsFactory()->getDB()->query("DELETE FROM assignments WHERE agent=".$agent->getId());
			}
			else {
				$task = intval($_POST['task']);
				$ans = $FACTORIES::getagentsFactory()->getDB()->query("SELECT task FROM assignments WHERE agent=".$agent->getId());
				// keep the clever pre-bench query in variable
				$asskv = "IFNULL((SELECT length FROM chunks WHERE solvetime>dispatchtime AND progress=length AND state IN (4,5) AND agent=".$agent->getId()." AND task=$task ORDER BY solvetime DESC LIMIT 1),0)";
				$line = $ans->fetch();
				if($line){
					// agent was assigned to something, change the assignment
					$ans = $FACTORIES::getagentsFactory()->getDB()->query("UPDATE assignments JOIN tasks ON tasks.id=$task SET assignments.task=tasks.id,assignments.benchmark=$asskv,assignments.autoadjust=tasks.autoadjust,assignments.speed=0 WHERE assignments.agent=".$agent->getId());
				} 
				else {
					// agent was not assigned, we need a new assignment
					$ans = $FACTORIES::getagentsFactory()->getDB()->query("INSERT INTO assignments (task,agent,benchmark,autoadjust) SELECT id,".$agent->getId().",$asskv,autoadjust FROM tasks WHERE id=$task");
				}
			}
			if(!$ans){
				$message = "<div class='alert alert-danger'>Failed to apply task change!</div>";
			}
			else if(strlen($message) == 0) {
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
	$set->addValue('gpus', explode("\x01", $agent['gpus']));
	$agents[] = $set;
}
$OBJECTS['numAgents'] = sizeof($agents);

$OBJECTS['allTasks'] = $allTasks;
$OBJECTS['sets'] = $agents;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




