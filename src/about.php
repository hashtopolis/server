<?php

require_once(dirname(__FILE__) . "/inc/load.php");

$TEMPLATE = new Template("static/about");
$message = "";

echo $TEMPLATE->render($OBJECTS);




