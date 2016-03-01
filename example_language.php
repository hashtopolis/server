<?php
/*
* This file is part of Bricky (https://github.com/s3inlc/bricky)
* Copyright 2016 by Sein Coray
*/

include("inc/template.class.php");
include("inc/lang.class.php");

$TEMPLATE = new Bricky\Template("example_language");

$OBJECTS = array();

//load languages here (Important: Bricky needs the variable to be named $LANGUAGE)
$LANGUAGE = new Bricky\Lang();

echo $TEMPLATE->render($OBJECTS);


