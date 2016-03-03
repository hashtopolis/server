<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("files");
$MENU->setActive("files");
$message = "";

//catch actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'addfile':
			$pocetup = 0;
			$source = $_POST["source"];
			if(!file_exists("files")) {
				$message .= "<div class='alert alert-success'>First imported file, creating files subdir...";
				if(mkdir("files")){
					$message .= "OK<br>";
				}
			}
			
			$allok = true;
			switch($source){
				case "upload":
					// from http upload
					$soubory = $_FILES["upfile"];
					$pocet = count($_FILES["upfile"]["name"]);
					for($i=0;$i<$pocet;$i++) {
						// copy all uploaded attached files to proper directory
						$realname = basename($soubory["name"][$i]);
						if ($realname==""){
							continue;
						}
			
						$nsoubor = array();
						foreach ($soubory as $klic => $soubor) {
							$nsoubor[$klic] = $soubor[$i];
						}
						$tmpfile = "files/".$realname;
						$resp = Util::uploadFile($tmpfile, $source, $nsoubor);
						$message .= $resp[1];
						if ($resp[0]) {
							$resp = Util::insertFile($tmpfile);
							$message .= $resp[1];
							if ($resp[0]) {
								$pocetup++;
							} 
							else {
								$allok = false;
							}
						} 
						else {
							$allok = false;
						}
					}
					break;
			
				case "import":
					// from import dir
					$soubory = $_POST["imfile"];
					$pocet = count($soubory);
					foreach($soubory as $soubor) {
						// copy all uploaded attached files to proper directory
						$realname = basename($soubor);
						$tmpfile = "files/".$realname;
						$resp = Util::uploadFile($tmpfile,$source,$realname);
						$message .= $resp[1];
						if ($resp[0]) {
							$resp = Util::insertFile($tmpfile);
							$message .= $resp[1];
							if ($resp[0]) {
								$pocetup++;
							} 
							else {
								$allok=false;
							}
						} 
						else {
							$allok=false;
						}
					}
					break;
			
				case "url":
					// from url
					$realname = basename($_POST["url"]);
					$tmpfile = "files/".$realname;
					$resp = Util::uploadFile($tmpfile,$source,$_POST["url"]);
					$message .= $resp[1];
					if ($resp[0]) {
						$resp = Util::insertFile($tmpfile);
						$message .= $resp[1];
						if ($resp[0]) {
							$pocetup++;
						} 
						else {
							$allok = false;
						}
					} 
					else {
						$allok = false;
					}
					break;
			}
			if ($allok){ 
				header("Location: files.php");
				die();
			}
			$message .= "</div>";
			break;
	}
}

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT files.id,files.filename,files.secret,files.size,IFNULL(taskfiles.tasks,0) AS tasks FROM files LEFT JOIN (SELECT   file,COUNT(task) AS tasks FROM taskfiles GROUP BY file) taskfiles ON taskfiles.file=files.id ORDER BY filename ASC");
$res = $res->fetchAll();
$files = array();
foreach($res as $file){
	$set = new DataSet();
	$set->setValues($file);
	$files[] = $set;
}

$OBJECTS['files'] = $files;
$OBJECTS['numFiles'] = sizeof($files);

$impfiles = array();
if(file_exists("import") && is_dir("import")) {
	$impdir = opendir("import");
	while($f = readdir($impdir)){
		if(($f!=".") && ($f!="..") && (!is_dir($f))){
			$set = new DataSet();
			$set->addValue('name', $f);
			$set->addValue('size', filesize("import/".$f));
			$impfiles[] = $set;
		}
	}
}

$OBJECTS['impfiles'] = $impfiles;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




