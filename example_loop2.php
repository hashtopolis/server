<?php
/*
* This file is part of Bricky (https://github.com/s3inlc/bricky)
* Copyright 2016 by Sein Coray
*/

include("inc/template.class.php");
include("inc/lang.class.php");

$TEMPLATE = new Bricky\Template("example_loop2");

$colors = array("#FFA07A", "#FF7F50", "#FF6347", "#FF4500", "#FF8C00", "#FFA500", "#7FFF00", "#20B2AA", "#B0E0E6");

$OBJECTS = array();
$OBJECTS['colors'] = $colors;

echo $TEMPLATE->render($OBJECTS);


