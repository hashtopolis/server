<?php
/*
* This file is part of Bricky (https://github.com/s3inlc/bricky)
* Copyright 2016 by Sein Coray
*/

include("inc/template.class.php");
include("inc/lang.class.php");

$TEMPLATE = new Bricky\Template("example_loop");

$OBJECTS = array();
$OBJECTS['hours'] = date("H");
$OBJECTS['minutes'] = date("i");

echo $TEMPLATE->render($OBJECTS);


