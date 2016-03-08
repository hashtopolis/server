<?php

//set to 0 after finished debugging
ini_set("display_errors", "1");

$OBJECTS = array();

$VERSION = "0.1.".substr(md5("3.14"), 0, 6);
$HOST = $_SERVER['HTTP_HOST'];
if(strpos($HOST, ":") !== false){
	$HOST = substr($HOST, 0, strpos($HOST, ":"));
}

$OBJECTS['version'] = $VERSION;
$OBJECTS['host'] = $HOST;

include(dirname(__FILE__)."/load.ini");

$INSTALL = "pending...";

//manually force the system to think it is installed
//this should be removed in release!!!
$INSTALL = 'DONE';

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

$LOGIN = null;
$MENU = new Menu();
$OBJECTS['menu'] = $MENU;
if($INSTALL == 'DONE'){
	$LOGIN = new Login();
	$OBJECTS['login'] = $LOGIN;
}

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT * FROM config");
$CONFIG = new DataSet();
foreach($res as $entry){
	$CONFIG->addValue($entry['item'], $entry['value']);
}
$OBJECTS['config'] = $CONFIG;

if($LOGIN->isLoggedin()){
	$OBJECTS['user'] = $LOGIN->getUser();
}



