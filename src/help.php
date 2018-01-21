<?php

require_once(dirname(__FILE__) . "/inc/load.php");

$TEMPLATE = new Template("static/help");

echo $TEMPLATE->render($OBJECTS);




