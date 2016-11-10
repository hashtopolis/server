<?php

//set to 1 for debugging
ini_set("display_errors", "1");

//is required for running well with php7
ini_set('pcre.jit', '0');

$OBJECTS = array();

$VERSION = "0.2.0 ALPHA";
$HOST = $_SERVER['HTTP_HOST'];
if(strpos($HOST, ":") !== false){
	$HOST = substr($HOST, 0, strpos($HOST, ":"));
}

$SCRIPTVERSION = "0.1.0 ALPHA";
$SCRIPTNAME = "hashtopussy.php";

$OBJECTS['version'] = $VERSION;
$OBJECTS['host'] = $HOST;

//START CONFIG
$CONN['user'] = 'root';
$CONN['pass'] = '';
$CONN['server'] = 'localhost';
$CONN['db'] = 'hashtopussy';
$CONN['installed'] = true; //set this to true if you config the mysql and setup manually
//END CONFIG

$INSTALL = "pending...";
if($CONN['installed']){
	$INSTALL = "DONE";
}

//include all .class.php files in inc dir
$dir = scandir(dirname(__FILE__));
foreach($dir as $entry){
	if(strpos($entry, ".class.php") !== false){
		require_once(dirname(__FILE__)."/".$entry);
	}
}

//include all handlers
$dir = scandir(dirname(__FILE__)."/handlers/");
foreach($dir as $entry){
    if(strpos($entry, ".class.php") !== false){
        require_once(dirname(__FILE__)."/handlers/".$entry);
    }
}

//include all model files in models dir
$dir = scandir(dirname(__FILE__)."/../models");
foreach($dir as $entry){
	if(strpos($entry, ".class.php") !== false){
		require_once(dirname(__FILE__)."/../models/".$entry);
	}
}

$FACTORIES = new Factory();

$gitcommit = "not versioned";
$out = array();
exec("git rev-parse HEAD", $out);
if(isset($out[0])){
	$gitcommit = substr($out[0], 0, 7);
}
$OBJECTS['gitcommit'] = $gitcommit;

$LOGIN = null;
$MENU = new Menu();
$OBJECTS['menu'] = $MENU;
if($INSTALL == 'DONE'){
	$LOGIN = new Login();
	$OBJECTS['login'] = $LOGIN;
	if($LOGIN->isLoggedin()){
		$OBJECTS['user'] = $LOGIN->getUser();
	}

	$res = $FACTORIES::getConfigFactory()->filter(array());
	$CONFIG = new DataSet();
	foreach($res as $entry){
		$CONFIG->addValue($entry->getItem(), $entry->getValue());
	}
	$OBJECTS['config'] = $CONFIG;
	
	//set autorefresh to false for all pages
	$OBJECTS['autorefresh'] = 0;
}


