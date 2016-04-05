<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

if(!$LOGIN->isLoggedin()){
	header("Location: index.php?err=4".time()."&fw=".urlencode($_SERVER['PHP_SELF']));
	die();
}
else if($LOGIN->getLevel() < 40){
	$TEMPLATE = new Template("restricted");
	die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("hashtypes");
$MENU->setActive("config_hashtypes");
$message = "";

//catch actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'delete':
			$id = $_POST['type'];
			$res = $DB->query("SELECT * FROM hashtypes WHERE id=".$DB->quote($id));
			$type = $res->fetch();
			if(!$type){
				$message = "<div class='alert alert-danger'>This hashtype does not exist!</div>";
				break;
			}
			$res = $DB->query("SELECT * FROM hashlists WHERE hashtype=".$DB->quote($type['id']));
			if($res->rowCount() > 0){
				$message = "<div class='alert alert-danger'>You cannot delete this hashtype! There are hashlists present which are of this type.</div>";
				break;
			}
			$DB->query("DELETE FROM hashtypes WHERE id=".$type['id']);
			$message = "<div class='alert alert-success'>Hashtype was successfully deleted!</div>";
			break;
		case 'add':
			$id = intval($_POST['id']);
			$desc = htmlentities($_POST['description']);
			$res = $DB->query("SELECT * FROM hashtypes WHERE id=".$DB->quote($id));
			$test = $res->fetch();
			if($test){
				$message = "<div class='alert alert-danger'>This hashtype already exists!</div>";
				break;
			}
			else if(strlen($desc) == 0 || $id < 0){
				$message = "<div class='alert alert-danger'>Invalid inputs!</div>";
				break;
			}
			$DB->query("INSERT INTO hashtypes (id, description) VALUES (".$DB->quote($id).", ".$DB->quote($desc).")");
			$message = "<div class='alert alert-success'>New hashtype was added!</div>";
			break;
	}
}

$res = $DB->query("SELECT * FROM hashtypes WHERE 1 ORDER BY id");
$res = $res->fetchAll();
$hashtypes = array();
foreach($res as $type){
	$hashtypes[] = new DataSet($type);
}

$OBJECTS['hashtypes'] = $hashtypes;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




