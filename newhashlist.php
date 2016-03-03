<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("newhashlist");
$MENU->setActive("lists_new");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//TODO:
	}
}

$OBJECTS['impfiles'] = array();
if(file_exists("import") && is_dir("import")) {
	$impdir = opendir("import");
	$impfiles = array();
	while ($f=readdir($impdir)) {
		if (($f!=".") && ($f!="..") && (!is_dir($f))) {
			$impfiles[] = $f;
		}
	}
	$OBJECTS['impfiles'] = $impfiles;
}

$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




