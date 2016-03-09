<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

if($INSTALL != 'DONE'){
	header("Location: install/");
	die("Forward to <a href='install/'>Install</a>");
}

$TEMPLATE = new Template("index");
$message = "";

if(isset($_GET['err'])){
	//TODO: handle error messages
}
else if(isset($_GET['logout'])){
	$logout = $_GET['logout'];
	$time = substr($logout, 1);
	if(time() - $time < 10){
		$message = "<div class='alert alert-success'>You logged out successfully!</div>";
	}
}

$OBJECTS['message'] = $message;
$OBJECTS['fw'] = @$_GET['fw'];

echo $TEMPLATE->render($OBJECTS);




