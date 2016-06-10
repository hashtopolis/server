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

$TEMPLATE = new Template("agents");
$MENU->setActive("agents_list");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'agentrename':
			if($LOGIN->getLevel() < 30){
				break;
			}
			$name = htmlentities($_POST['name'], false, "UTF-8");
			$agent = intval($_POST['agent']);
			$res = $DB->query("SELECT * FROM agents WHERE id=$agent");
			$agent = $res->fetch();
			if($agent && strlen($name) > 0){
				$DB->query("UPDATE agents SET name=".$DB->quote($name)." WHERE id=".$agent['id']);
				header("Location: ".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
				die();
			}
			break;
		case 'agentowner':
			if($LOGIN->getLevel() < 30){
				break;
			}
			// change agent owner
			$agid = intval($_POST["agent"]);
			$owner = intval($_POST["owner"]);
			$valid = true;
			if($owner > 0){
				$user = $FACTORIES::getUserFactory()->get($owner);
				if($user == null){
					$valid = false;
				}
			}
			else{
				$owner = 0;
			}
			if($valid){
				$res = $DB->query("UPDATE agents SET userId=$owner WHERE id=$agid");
				if (!$res) {
					$message = "<div class='alert alert-danger'>Could not change owner!</div>";
				}
				else{
					header("Location: ".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
					die();
				}
			}
			else{
				$message = "<div class='alert alert-danger'>Invalid user!</div>";
			}
			break;
		case 'agenttrusted':
			// switch agent trusted state
			$agid = intval($_POST["agent"]);
			$trusted = intval($_POST["trusted"]);
			$res = $DB->query("UPDATE agents SET trusted=$trusted WHERE id=$agid");
			if (!$res) {
				$message = "<div class='alert alert-danger'>Could not change agent trust!</div>";
			}
			else{
				header("Location: ".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
				die();
			}
			break;
		case 'agentignore':
			// switch error ignoring for agent
			$agid = intval($_POST["agent"]);
			$ignore = intval($_POST["ignore"]);
			$res = $DB->query("UPDATE agents SET ignoreerrors=$ignore WHERE id=$agid");
			if (!$res) {
				$message = "<div class='alert alert-danger'>Could not change error ignoring!</div>";
			}
			else{
				header("Location: ".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
				die();
			}
			break;
		case 'setparam':
			// change agent extra cmd line parameters for hashcat
			$agid = intval($_POST["agent"]);
			$pars = $DB->quote(htmlentities($_POST["cmdpars"], false, "UTF-8"));
			$res = $DB->query("UPDATE agents SET cmdpars=$pars WHERE id=$agid");
			if (!$res) {
				$message = "<div class='alert alert-danger'>Could not change agent-specific parameters!</div>";
			}
			else{
				header("Location: ".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
				die();
			}
			break;
		case 'agentwait':
			// change agent waiting time for idle
			$agid = intval($_POST["agent"]);
			$wait = intval($_POST["wait"]);
			$res = $DB->query("UPDATE agents SET wait=$wait WHERE id=$agid");
			if (!$res) {
				$message = "<div class='alert alert-danger'>Could not change agent idle wait period!</div>";
			}
			else{
				header("Location: ".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
				die();
			}
			break;
		case 'setplatform':
			// change agent platform (none/nvidia/amd)
			$agid = intval($_POST["agent"]);
			$pf = intval($_POST["platform"]);
			$res = $DB->query("UPDATE agents SET gpubrand=$pf,gpudriver=0 WHERE id=$agid");
			if (!$res) {
				$message = "<div class='alert alert-danger'>Could not change platform!</div>";
			}
			else{
				header("Location: ".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
				die();
			}
			break;
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
				header("Location: ".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
				die();
			}
			break;
		case 'agentdelete':
			if($LOGIN->getLevel() < 30){
				break;
			}
			$agent = $FACTORIES::getagentsFactory()->get($_POST['agent']);
			$DB->query("START TRANSACTION");
			if (Util::deleteAgent($agent)) {
				$DB->query("COMMIT");
				header("Location: ".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
				die();
			} 
			else {
				$DB->query("ROLLBACK");
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
				$DB->query("DELETE FROM assignments WHERE agent=".$agent->getId());
			}
			else {
				$task = intval($_POST['task']);
				$ans = $DB->query("SELECT task FROM assignments WHERE agent=".$agent->getId());
				// keep the clever pre-bench query in variable
				$asskv = "IFNULL((SELECT length FROM chunks WHERE solvetime>dispatchtime AND progress=length AND state IN (4,5) AND agent=".$agent->getId()." AND task=$task ORDER BY solvetime DESC LIMIT 1),0)";
				$line = $ans->fetch();
				if($line){
					// agent was assigned to something, change the assignment
					$ans = $DB->query("UPDATE assignments JOIN tasks ON tasks.id=$task SET assignments.task=tasks.id,assignments.benchmark=$asskv,assignments.autoadjust=tasks.autoadjust,assignments.speed=0 WHERE assignments.agent=".$agent->getId());
				} 
				else {
					// agent was not assigned, we need a new assignment
					$ans = $DB->query("INSERT INTO assignments (task,agent,benchmark,autoadjust) SELECT id,".$agent->getId().",$asskv,autoadjust FROM tasks WHERE id=$task");
				}
			}
			if(!$ans){
				$message = "<div class='alert alert-danger'>Failed to apply task change!</div>";
			}
			else if(strlen($message) == 0) {
				if(isset($_GET['task'])){
					header("Location: tasks.php?id=".$_GET['task']);
					die();
				}
				header("Location: ".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
				die();
			}
			break;
	}
}

$ans = $DB->query("SELECT id,name FROM tasks WHERE hashlist IS NOT NULL ORDER BY id ASC");
$ans = $ans->fetchAll();
$allTasks = array();
foreach($ans as $task){
	$set = new DataSet();
	$set->setValues($task);
	$allTasks[] = $set;
}

if(isset($_GET['id'])){
	//show agent detail
	$TEMPLATE = new Template("agents.detail");
	$agent = $FACTORIES::getagentsFactory()->get($_GET['id']);
	if($agent === null){
		$message = "<div class='alert alert-danger'>Agent not found!</div>";
	}
	else{
		$users = $FACTORIES::getUserFactory()->filter(array());
		$OBJECTS['users'] = $users;
		
		$res = $DB->query("SELECT agents.*,assignments.task,SUM(GREATEST(chunks.solvetime,chunks.dispatchtime)-chunks.dispatchtime) AS spent FROM agents LEFT JOIN assignments ON assignments.agent=agents.id LEFT JOIN chunks ON chunks.agent=agents.id WHERE agents.id=".$agent->getId());
		$agentSet = new DataSet();
		$agentSet->setValues($res->fetch());
		
		$res = $DB->query("SELECT errors.*,chunks.id FROM errors LEFT JOIN chunks ON (errors.time BETWEEN chunks.dispatchtime AND chunks.solvetime) AND chunks.agent=errors.agent WHERE errors.agent=".$agent->getId()." ORDER BY time DESC");
		$res = $res->fetchAll();
		$errors = array();
		foreach($res as $error){
			$set = new DataSet();
			$set->setValues($error);
			$errors[] = $set;
		}
		
		$res = $DB->query("SELECT chunks.*,GREATEST(chunks.dispatchtime,chunks.solvetime)-chunks.dispatchtime AS spent,tasks.name AS taskname FROM chunks JOIN tasks ON chunks.task=tasks.id WHERE agent=".$agent->getId()." ORDER BY chunks.dispatchtime DESC,chunks.skip DESC LIMIT 100");
		$res = $res->fetchAll();
		$chunks = array();
		foreach($res as $chunk){
			$set = new DataSet();
			$set->setValues($chunk);
			$chunks[] = $set;
		}
		
		$platforms = array();
		$plt = Util::getStaticArray('-1', 'platforms');
		foreach($plt as $key => $platform){
			$set = new DataSet();
			$set->addValue('key', $key);
			$set->addValue('val', $platform);
			$platforms[] = $set;
		}
		$OBJECTS['platforms'] = $platforms;
		$OBJECTS['agent'] = $agentSet;
		$OBJECTS['errors'] = $errors;
		$OBJECTS['chunks'] = $chunks;
	}
}
else{
	//$res = $DB->query("SELECT agents.id,agents.uid,agents.active,agents.trusted,agents.cputype,agents.userId,agents.gpubrand,agents.gpudriver,agents.gpus,agents.hcversion,agents.lastact,agents.lasttime,agents.lastip,assignments.task,assignments.speed,agents.os,agents.name,IF(IFNULL(chunks.time,0)>".(time() - $CONFIG->getVal('chunktimeout')).",1,0) AS working FROM agents LEFT JOIN assignments ON agents.id=assignments.agent LEFT JOIN tasks ON assignments.task=tasks.id LEFT JOIN (SELECT agent,MAX(GREATEST(dispatchtime,solvetime)) AS time FROM chunks GROUP BY agent) chunks ON chunks.agent=agents.id ORDER BY agents.id ASC");
	
	//IF(IFNULL(chunks.time,0)>".(time() - $CONFIG->getVal('chunktimeout')).",1,0) AS working FROM agents LEFT JOIN assignments ON agents.id=assignments.agent LEFT JOIN tasks ON assignments.task=tasks.id LEFT JOIN (SELECT agent,MAX(GREATEST(dispatchtime,solvetime)) AS time FROM chunks GROUP BY agent)
	
	$res = $DB->query("SELECT * FROM agents ORDER BY id ASC");
	$res = $res->fetchAll();
	$agents = array();
	foreach($res as $agent){
		$ans = $DB->query("SELECT IF(count(*)>0,1,0) as working FROM chunks INNER JOIN assignments ON assignments.task=chunks.task INNER JOIN agents ON agents.id=assignments.agent WHERE agents.id=".$agent['id']." AND MAX(dispatchtime, solvetime)>".(time() - $CONFIG->getVal('chunktimeout')));
		$line = $ans->fetch();
		$agent['working'] = $line['working'];
		$set = new DataSet($agent);
		$set->addValue('gpus', explode("\x01", $agent['gpus']));
		$agents[] = $set;
	}
	$OBJECTS['numAgents'] = sizeof($agents);
	
	$OBJECTS['sets'] = $agents;
}

$OBJECTS['allTasks'] = $allTasks;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




