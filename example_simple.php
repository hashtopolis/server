<?php
/*
* This file is part of Bricky (https://github.com/s3inlc/bricky)
* Copyright 2016 by Sein Coray
*/

include("inc/template.class.php");
include("inc/lang.class.php");

$TEMPLATE = new Bricky\Template("example_simple");

$OBJECTS = array();
$OBJECTS['time'] = date("d.m.Y - H:i:s");

echo $TEMPLATE->render($OBJECTS);



