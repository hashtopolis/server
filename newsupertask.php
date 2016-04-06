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

$TEMPLATE = new Template("newsupertask");
$MENU->setActive("tasks_super");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'newsupertask':
			$orig = intval($_POST['supertask']);
			$hashlist = intval($_POST['hashlist']);
			$res = $DB->query("SELECT * FROM Supertask WHERE supertaskId=".$DB->quote($orig));
			$supertask = $res->fetch();
			if(!$supertask){
				$message = "<div class='alert alert-danger'>Invalid Supertask!</div>";
				break;
			}
			$res = $DB->query("SELECT * FROM hashlists WHERE id=".$DB->quote($hashlist));
			$hashlist = $res->fetch();
			if(!$hashlist){
				$message = "<div class='alert alert-danger'>Invalid Hashlist!</div>";
				break;
			}
			
			$res = $DB->query("SELECT tasks.* FROM SupertaskTask INNER JOIN tasks ON tasks.id=SupertaskTask.taskId WHERE supertaskId=".$supertask['supertaskId']);
			$res = $res->fetchAll();
			foreach($res as $task){
				$DB = $FACTORIES::getagentsFactory()->getDB();
				$name = $DB->quote(htmlentities($task['name'], false, "UTF-8"));
				$cmdline = $DB->quote($task["attackcmd"]);
				$autoadj = intval($task["autoadjust"]);
				$chunk = intval($task["chunktime"]);
				$status = intval($task["statustimer"]);
				$color = $task["color"];
				$message = "<div class='alert alert-neutral'>";
				$forward = "";
				if (preg_match("/[0-9A-Za-z]{6}/",$color)==1) {
					$color = "'$color'";
				}
				else {
					$color = "NULL";
				}
				if (strpos($cmdline, $CONFIG->getVal('hashlistAlias')) === false) {
					$message .= "Command line must contain hashlist (".$CONFIG->getVal('hashlistAlias').").";
				}
				else {
					$thashlist = intval($_POST["hashlist"]);
					if ($thashlist > 0) {
						$hashlist = $thashlist;
					}
					if ($name == "''"){
						$name = "HL".$hashlist."_".date("Ymd_Hi");
					}
					if ($hashlist != "") {
						if ($status>0 && $chunk>0 && $chunk>$status) {
							$DB->exec("SET autocommit = 0");
							$DB->exec("START TRANSACTION");
							$message .= "Creating task in the DB...";
							$res = $DB->exec("INSERT INTO tasks (name, attackcmd, hashlist, chunktime, statustimer, autoadjust, color) VALUES ($name, $cmdline, $hashlist, $chunk, $status, $autoadj, $color)");
							if ($res) {
								// insert succeeded
								$id = $DB->lastInsertId();
								$message .= "OK (id: $id)<br>";
								// attach files
								$attachok = true;
								$ans = $DB->query("SELECT * FROM taskfiles WHERE task=".$task['id']);
								$ans = $ans->fetchAll();
								
								if (sizeof($ans) > 0) {
									foreach($ans as $fid) {
										if ($fid['file'] > 0) {
											$message .= "Attaching file {$fid['file']}...";
											if ($DB->exec("INSERT INTO taskfiles (task,file) VALUES ($id, {$fid['file']})")) {
												$message .= "OK";
											}
											else {
												$message .= "ERROR!";
												$attachok = false;
											}
											$message .= "<br>";
										}
									}
								}
								if ($attachok == true) {
									$DB->exec("COMMIT");
									$message .= "Task created successfuly!";
									/*if($forward){
										header("Location: $forward");
										die();
									}*/
								}
								else {
									$DB->exec("ROLLBACK");
								}
							}
							else {
								$message .= "ERROR: ".$DB->errorInfo()[2];
							}
						}
						else {
							$message .= "Chunk time must be higher than status timer.";
						}
					}
					else {
						$message .= "Every task requires a hashlist, even if it should contain only one hash.";
					}
				}
				$message .= "</div>";
			}
			//header("Location: tasks.php");
			//die();
			break;
	}
}

$orig = 0;
if (isset($_GET["id"])) {
	//copied from a task
	$orig = intval($_GET["id"]);
}

$OBJECTS['orig'] = $orig;

$lists = array();
$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT id,name FROM hashlists ORDER BY id ASC");
$res = $res->fetchAll();
foreach($res as $list){
	$set = new DataSet();
	$set->setValues($list);
	$lists[] = $set;
}

$OBJECTS['lists'] = $lists;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




