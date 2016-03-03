<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("files");
$MENU->setActive("files");
$message = "";

//catch actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//TODO:
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




