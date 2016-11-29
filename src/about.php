<?php

require_once(dirname(__FILE__) . "/inc/load.php");

$TEMPLATE = new Template("static/about");

echo $TEMPLATE->render($OBJECTS);




