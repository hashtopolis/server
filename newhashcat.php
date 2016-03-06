<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("newhashcat");
$MENU->setActive("hashcat_new");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'newhashcatp':
			// new hashcat release creator
			$DB = $FACTORIES::getagentsFactory()->getDB();
			$message = "<div class='alert alert-neutral'>";
			
			$version = $DB->quote($_POST["version"]);
			$url["1"] = $DB->quote($_POST["url_nvidia"]);
			$url["2"] = $DB->quote($_POST["url_amd"]);
			$common_files = $DB->quote($_POST["common_files"]);
			$files["1"]["32"] = $DB->quote($_POST["32_nvidia"]);
			$files["1"]["64"] = $DB->quote($_POST["64_nvidia"]);
			$files["2"]["32"] = $DB->quote($_POST["32_amd"]);
			$files["2"]["64"] = $DB->quote($_POST["64_amd"]);
			$minver["1"] = floatval($_POST["minver_nvidia"]);
			$minver["2"] = floatval($_POST["minver_amd"]);
			$rootdir["1"] = $DB->quote($_POST["rootdir_nvidia"]);
			$rootdir["2"] = $DB->quote($_POST["rootdir_amd"]);
			if ($version == "") {
				$message .= "You must specify the version";
			} 
			else {
				$message .= "Creating release in the DB...";
				$res = $DB->query("INSERT INTO hashcatreleases (version,time,url_nvidia,url_amd,common_files,32_nvidia,64_nvidia,32_amd,64_amd,rootdir_nvidia,rootdir_amd,minver_nvidia,minver_amd) VALUES ($version,".time().",".$url["1"].",".$url["2"].",$common_files,".$files["1"]["32"].",".$files["1"]["64"].",".$files["2"]["32"].",".$files["2"]["64"].",".$rootdir["1"].",".$rootdir["2"].",".$minver["1"].",".$minver["2"].")");
				if ($res) {
					// insert succeeded
					$message .= "OK";
					header("Location: hashcat.php");
					die();
				} 
				else {
					$message .= "ERROR: ".mysqli_error($dblink);
				}
				$message .= "<br>";
			}
			$message .= "</div>";
			break;
	}
}

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT * FROM hashcatreleases ORDER BY time DESC LIMIT 1");
$res = $res->fetch();
$new = new DataSet();
if($res){
	$new->setValues($res);
}

$OBJECTS['new'] = $new;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




