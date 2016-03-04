<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("newtask");
$MENU->setActive("tasks_new");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'newtaskp':
			// new task creator
			$DB = $FACTORIES::getagentsFactory()->getDB();
			$name = $DB->quote($_POST["name"]);
			$cmdline = $DB->quote($_POST["cmdline"]);
			$autoadj = intval($_POST["autoadjust"]);
			$chunk = intval($_POST["chunk"]);
			$status = intval($_POST["status"]);
			$color = $_POST["color"];
			$message = "<div class='alert alert-neutral'>";
			$forward = "";
			if (preg_match("/[0-9A-Za-z]{6}/",$color)==1) {
				$color = "'$color'";
			} 
			else {
				$color = "NULL";
			}
			if (strpos($cmdline,$hashlistAlias)===false) {
				$message .= "Command line must contain hashlist ($hashlistAlias).";
			} 
			else {
				if ($_POST["hashlist"] == "preconf") {
					// it will be a preconfigured task
					$hashlist = "NULL";
					if ($name=="''"){
						$name = "PC_".date("Ymd_Hi");
					}
					$forward = "pretasks.php";
				} 
				else {
					$thashlist = intval($_POST["hashlist"]);
					if ($thashlist > 0) {
						$hashlist = $thashlist;
					}
					if ($name == "''"){
						$name = "HL".$hashlist."_".date("Ymd_Hi");
					}
					$forward = "tasks.php";
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
							$attachok=true;
							if (isset($_POST["adfile"])) {
								foreach($_POST["adfile"] as $fid) {
									if ($fid > 0) {
										$message .= "Attaching file $fid...";
										if ($DB->exec("INSERT INTO taskfiles (task,file) VALUES ($id, $fid)")) {
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
								$message = "Task created successfuly!";
								if($forward){
									header("Location: $forward");
									die();
								}
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
			break;
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




