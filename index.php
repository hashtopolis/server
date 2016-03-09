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
	$err = $_GET['err'];
	$time = substr($err, 1);
	if(time() - $time < 10){
		switch($err[0]){
			case '1':
				$message = "<div class='alert alert-danger'>Invalid form submission!</div>";
				break;
			case '2':
				$message = "<div class='alert alert-danger'>You need to fill in both fields!</div>";
				break;
			case '3':
				$message = "<div class='alert alert-danger'>Wrong username/password!</div>";
				break;
		}
	}
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




