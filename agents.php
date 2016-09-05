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
		case 'clearerrors':
			if($LOGIN->getLevel() < 30){
				break;
			}
			$agent = intval($_POST['agent']);
			$qF = new QueryFilter("agentId", $agent, "=");
			$FACTORIES::getAgentErrorFactory()->massDeletion(array('filter' => array($qF)));
			Util::refresh();
		case 'agentrename':
			if($LOGIN->getLevel() < 30){
				break;
			}
			$name = htmlentities($_POST['name'], false, "UTF-8");
			$agent = $FACTORIES::getAgentFactory()->get($_POST['agent']);
			if($agent && strlen($name) > 0){
				$agent->setAgentName($name);
				$FACTORIES::getAgentFactory()->update($agent);
				Util::refresh();
			}
			break;
		case 'agentowner':
			if($LOGIN->getLevel() < 30){
				break;
			}
			// change agent owner
			$agent = $FACTORIES::getAgentFactory()->get(intval($_POST['agent']));
			if(!$agent){
				$message = "<div class='alert alert-danger'>Invalid agent!</div>";
				break;
			}
			else if($_POST['owner'] == 0){
				$agent->setUserId(0);
				$FACTORIES::getAgentFactory()->update($agent);
				header("Location: ".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
				die();
			}
			$owner = $FACTORIES::getUserFactory()->get(intval($_POST["owner"]));
			if(!$owner){
				$message = "<div class='alert alert-danger'>Invalid user!</div>";
				break;
			}
			$agent->setUserId($owner->getId());
			$FACTORIES::getAgentFactory()->update($agent);
			Util::refresh();
		case 'agenttrusted':
			// switch agent trusted state
			$agent = $FACTORIES::getAgentFactory()->get(intval($_POST["agent"]));
			$trusted = intval($_POST["trusted"]);
			if(!$agent){
				$message = "<div class='alert alert-danger'>Could not change agent trust!</div>";
				break;
			}
			$agent->setIsTrusted($trusted);
			$FACTORIES::getAgentFactory()->update($agent);
			Util::refresh();
		case 'agentignore':
			// switch error ignoring for agent
			$agent = $FACTORIES::getAgentFactory()->get(intval($_POST["agent"]));
			$ignore = intval($_POST["ignore"]);
			if(!$agent){
				$message = "<div class='alert alert-danger'>Could not change error ignoring!</div>";
				break;
			}
			$agent->setIgnoreErrors($ignore);
			$FACTORIES::getAgentFactory()->update($agent);
			Util::refresh();
		case 'setparam':
			// change agent extra cmd line parameters for hashcat
			$agent = $FACTORIES::getAgentFactory()->get(intval($_POST["agent"]));
			$pars = htmlentities($_POST["cmdpars"], false, "UTF-8");
			if(!$agent){
				$message = "<div class='alert alert-danger'>Could not change agent-specific parameters!</div>";
				break;
			}
			$agent->setCmdPars($pars);
			$FACTORIES::getAgentFactory()->update($agent);
			Util::refresh();
		case 'agentwait':
			// change agent waiting time for idle
			$agent = $FACTORIES::getAgentFactory()->get(intval($_POST["agent"]));
			$wait = intval($_POST["wait"]);
			if(!$agent){
				$message = "<div class='alert alert-danger'>Could not change agent idle wait period!</div>";
				break;
			}
			$agent->setWait($wait);
			$FACTORIES::getAgentFactory()->update($agent);
			Util::refresh();
		case 'agentactive':
			$agent = $FACTORIES::getAgentFactory()->get(intval($_POST["agent"]));
			if(!$agent){
				$message = "<div class='alert alert-danger'>Could not change agent activity!</div>";
				break;
			}
			else if($agent->getIsActive() == 1){
				$agent->setIsActive(0);
			}
			else{
				$agent->setIsActive(1);
			}
			$FACTORIES::getAgentFactory()->update($agent);
			Util::refresh();
		case 'agentdelete':
			if($LOGIN->getLevel() < 30){
				break;
			}
			$agent = $FACTORIES::getAgentFactory()->get(intval($_POST['agent']));
			$FACTORIES::getAgentFactory()->getDB()->query("START TRANSACTION");
			if (Util::deleteAgent($agent)) {
				$FACTORIES::getAgentFactory()->getDB()->query("COMMIT");
			} 
			else {
				$FACTORIES::getAgentFactory()->getDB()->query("ROLLBACK");
				$message = "<div class='alert alert-danger'>Could not delete agent!</div>";
				break;
			}
			Util::refresh();
		case 'agentassign':
			$agent = $FACTORIES::getAgentFactory()->get(intval($_POST["agent"]));
			if(!$agent){
				$message = "<div class='alert alert-danger'>Invalid agent!</div>";
				break;
			}
			else if(intval($_POST['task']) == 0){
				//unassign
				$qF = new QueryFilter("agentId", $agent->getId(), "=");
				$FACTORIES::getAssignmentFactory()->massDeletion(array('filter' => array($qF)));
				Util::refresh();
			}
			
			$task = $FACTORIES::getTaskFactory()->get(intval($_POST['task']));
			if(!$task){
				$message = "<div class='alert alert-danger'>Invalid task!</div>";
				break;
			}
			$qF = new QueryFilter("agentId", $agent->getId(), "=");
			$assignments = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF)));

			//determine benchmark number
			$benchmark = 0;
			$qF1 = new ComparisonFilter("solveTime", "dispatchTime", ">");
			$qF2 = new ComparisonFilter("progress", "length", "=");
			$qF3 = new ContainFilter("state", array("4, 5"));
			$qF4 = new QueryFilter("agentId", $agent->getId(), "=");
			$qF5 = new QueryFilter("taskId", $task->getId(), "=");
			$oF = new OrderFilter("solveTime", "DESC");
			$entries = $FACTORIES::getChunkFactory()->filter(array('filter' => array($qF1, $qF2, $qF3, $qF4, $qF5), 'order' => array($oF)));
			if(sizeof($entries) > 0){
				$benchmark = $entries[0]->getLength();
			}
			unset($entries);
			
			if(sizeof($assignments) > 0){
				for($i=1;$i<sizeof($assignments);$i++){ // clean up if required
					$FACTORIES::getAssignmentFactory()->delete($assignments[$i]);
				}
				$assignment = $assignments[0];
				$assignment->setTaskId($task->getId());
				$assignment->setBenchmark($benchmark);
				$assignment->setautoAdjust($task->getAutoAdjust());
				$assignment->setSpeed(0);
				$FACTORIES::getAssignmentFactory()->update($assignment);
			}
			else{
				$assignment = new Assignment(0, $task->getId(), $agent->getId(), $benchmark, $task->getAutoAdjust(), 0);
				$FACTORIES::getAssignmentFactory()->save($assignment);
			}
			if(isset($_GET['task'])){
				header("Location: tasks.php?id=".intval($_GET['task']));
				die();
			}
			Util::refresh();
	}
}

$allTasks = $FACTORIES::getTaskFactory()->filter(array());

if(isset($_GET['id'])){
	//show agent detail
	$TEMPLATE = new Template("agents.detail");
	$agent = $FACTORIES::getAgentFactory()->get(intval($_GET['id']));
	if(!$agent){
		$message = "<div class='alert alert-danger'>Agent not found!</div>";
	}
	else{
		$users = $FACTORIES::getUserFactory()->filter(array());
		$OBJECTS['users'] = $users;
		
		//TODO: not done yet
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
		//TODO: until here
	}
}
else{
	$oF = new OrderFilter("agentId", "ASC");
	$agents = $FACTORIES::getAgentFactory()->filter(array('order' => array($oF)));
	$allAgents = array();
	foreach($agents as $agent){
		$set = new DataSet($agent->getKeyValueDict());
		$set->addValue('gpus', explode("\x01", $agent->getGpus()));
		
		$qF = new QueryFilter("agentId", $agent->getId(), "=");
		$assignments = $FACTORIES::getAssignmentFactory()->filter(array('filter' => array($qF)));
		$working = 0;
		$taskId = 0;
		if(sizeof($assignments) > 0){
			$assignment = $assignments[0];
			$qF = new QueryFilter("taskId", $assignment->getTaskId(), "=");
			$chunks = $FACTORIES::getChunkFactory()->filter();
			foreach($chunks as $chunk){
				if(max($chunk->getDispatchTime(), $chunk->getSolveTime()) > time() - $CONFIG->getVal('chunktimeout')){
					$working = 1;
					$taskId = $assignment->getTaskId();
				}
			}
		}
		$set->addValue("working", $working);
		$set->addValue("taskId", $taskId);
		$allAgents[] = $set;
	}
	$OBJECTS['numAgents'] = sizeof($allAgents);
	$OBJECTS['sets'] = $allAgents;
}

$OBJECTS['allTasks'] = $allTasks;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




