<?php

//set to 0 after finished debugging
ini_set("display_errors", "0");
//is required for running well with php7
ini_set('pcre.jit', '0');

$OBJECTS = array();

$VERSION = "0.1.0 BETA";
$HOST = $_SERVER['HTTP_HOST'];
if(strpos($HOST, ":") !== false){
	$HOST = substr($HOST, 0, strpos($HOST, ":"));
}

$OBJECTS['version'] = $VERSION;
$OBJECTS['host'] = $HOST;

//START CONFIG
$CONN['user'] = '__DBUSER__';
$CONN['pass'] = '__DBPASS__';
$CONN['server'] = '__DBSERVER__';
$CONN['db'] = '__DBDB__';
$CONN['installed'] = false; //set this to true if you config the mysql and setup manually
//END CONFIG

$INSTALL = "pending...";
if($CONN['installed']){
	$INSTALL = "DONE";
}

require_once(dirname(__FILE__)."/crypt.class.php");
require_once(dirname(__FILE__)."/dataset.class.php");
require_once(dirname(__FILE__)."/lang.class.php");
require_once(dirname(__FILE__)."/login.class.php");
require_once(dirname(__FILE__)."/menu.class.php");
require_once(dirname(__FILE__)."/template.class.php");
require_once(dirname(__FILE__)."/util.class.php");

require_once(dirname(__FILE__)."/../models/AbstractModel.class.php");
require_once(dirname(__FILE__)."/../models/AbstractModelFactory.class.php");
require_once(dirname(__FILE__)."/../models/JoinFilter.class.php");
require_once(dirname(__FILE__)."/../models/OrderFilter.class.php");
require_once(dirname(__FILE__)."/../models/QueryFilter.class.php");

require_once(dirname(__FILE__)."/../models/agents.class.php");
require_once(dirname(__FILE__)."/../models/agentsFactory.class.php");
require_once(dirname(__FILE__)."/../models/User.class.php");
require_once(dirname(__FILE__)."/../models/UserFactory.class.php");
require_once(dirname(__FILE__)."/../models/Session.class.php");
require_once(dirname(__FILE__)."/../models/SessionFactory.class.php");
require_once(dirname(__FILE__)."/../models/RightGroup.class.php");
require_once(dirname(__FILE__)."/../models/RightGroupFactory.class.php");

require_once(dirname(__FILE__)."/factory.class.php");

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

	$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT * FROM config");
	$CONFIG = new DataSet();
	foreach($res as $entry){
		$CONFIG->addValue($entry['item'], $entry['value']);
	}
	$OBJECTS['config'] = $CONFIG;
	
	//set autorefresh to false for all pages
	$OBJECTS['autorefresh'] = 0;
	
	$DB = $FACTORIES::getagentsFactory()->getDB();
}


