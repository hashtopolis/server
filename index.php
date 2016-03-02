<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

if($INSTALL != 'DONE'){
	header("Location: install/");
	die("Forward to <a href='install/'>Install</a>");
}

$TEMPLATE = new Template("index");

echo $TEMPLATE->render($OBJECTS);




