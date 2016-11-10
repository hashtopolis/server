<?php
use Bricky\Template;
require_once(dirname(__FILE__) . "/inc/load.php");

if(!$LOGIN->isLoggedin()){
	header("Location: index.php?err=4".time()."&fw=".urlencode($_SERVER['PHP_SELF']));
	die();
}
else if($LOGIN->getLevel() < 5){
	$TEMPLATE = new Template("restricted");
	die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("supertasks");
$MENU->setActive("tasks_super");
$message = "";

//catch actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'taskdelete':
			$supertask = intval($_POST['supertask']);
			$res = $DB->query("SELECT * FROM Supertask WHERE supertaskId=$supertask");
			$supertask = $res->fetch();
			if(!$supertask){
				$message = "<div class='alert alert-danger'>Invalid Supertask!</div>";
				break;
			}
			$DB->query("START TRANSACTION");
			$DB->query("DELETE FROM SupertaskTask WHERE supertaskId=".$supertask['supertaskId']);
			$DB->query("DELETE FROM Supertask WHERE supertaskId=".$supertask['supertaskId']);
			$DB->query("COMMIT");
			header("Location: supertasks.php");
			die();
			break;
	}
}

if(isset($_GET['id'])){
	$TEMPLATE = new Template("supertasks.detail");
	$res = $DB->query("SELECT * FROM Supertask WHERE supertaskId=".$DB->quote($_GET['id']));
	$supertask = $res->fetch();
	if($supertask){
		$res = $DB->query("SELECT * FROM tasks WHERE tasks.id IN (SELECT taskId FROM SupertaskTask WHERE supertaskId=".$supertask['supertaskId'].")");
		$res = $res->fetchAll();
		$tasks = array();
		foreach($res as $task){
			$tasks[] = new DataSet($task);
		}
		$OBJECTS['tasks'] = $tasks;
	}
	$OBJECTS['supertask'] = new DataSet($supertask);
}
else{
	$supertasks = array();
	$res = $DB->query("SELECT * FROM Supertask ORDER BY Supertask.supertaskId");
	$res = $res->fetchAll();
	foreach($res as $supertask){
		$set = new DataSet();
		$set->setValues($supertask);
		$ans = $DB->query("SELECT * FROM tasks WHERE tasks.id IN (SELECT taskId FROM SupertaskTask WHERE supertaskId=".$supertask['supertaskId'].")");
		$tasks = array();
		foreach($ans as $task){
			$subset = new DataSet();
			$subset->setValues($task);
			$tasks[] = $subset;
		}
		$set->addValue("tasks", $tasks);
		$supertasks[] = $set;
	}
	
	$OBJECTS['supertasks'] = $supertasks;
	$OBJECTS['numSupertasks'] = sizeof($supertasks);
}
	
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




