<?php
require_once("../inc/load.php");

if(!$LOGIN->isLoggedin()){
	header("Location: index.php");
	die();
}

$LOGIN->logout();

header("Location: index.php?logout=1".time());




