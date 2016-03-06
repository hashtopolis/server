<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("hashcat");
$MENU->setActive("hashcat_list");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'releasedelete':
			// delete hashcat release
			$DB = $FACTORIES::getagentsFactory()->getDB();
			$release = $DB->quote($_POST["release"]);
			$DB->exec("START TRANSACTION");
			$res = $DB->query("SELECT * FROM agents WHERE hcversion=$release");
			if ($res->rowCount() > 0) {
				$message = "<div class='alert alert-danger'>There are registered agents running this Hashcat version.</div>";
			} 
			else {
				$res = $DB->query("DELETE FROM hashcatreleases WHERE version=$release");
				if ($res) {
					$DB->exec("COMMIT");
					header("Location: hashcat.php");
					die();
				} 
				else {
					$DB->exec("ROLLBACK");
					$message = "<div class='alert alert-danger'>Could not delete Hashcat release!</div>";
				}
			}
			break;
	}
}

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT * FROM hashcatreleases ORDER BY time DESC");
$res = $res->fetchAll();
$releases = array();
foreach($res as $release){
	$set = new DataSet();
	$set->setValues($release);
	$releases[] = $set;
}

$OBJECTS['releases'] = $releases;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




