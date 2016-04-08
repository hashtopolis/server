<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("about");
$message = "";

echo $TEMPLATE->render($OBJECTS);




