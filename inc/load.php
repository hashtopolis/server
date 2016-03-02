<?php

//set to 0 after finished debugging
ini_set("display_errors", "1");

$CONN['user'] = 'dbuser';
$CONN['pass'] = 'dbpass';
$CONN['server'] = 'dbhost';
$CONN['db'] = 'dbname';

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

require_once(dirname(__FILE__)."/../models/Bill.class.php");
require_once(dirname(__FILE__)."/../models/BillFactory.class.php");

require_once(dirname(__FILE__)."/factory.class.php");

$OBJECTS = array();
$FACTORIES = new Factory();

$LOGIN = null;
if($INSTALL == 'DONE'){
	$LOGIN = new Login();
	$OBJECTS['login'] = $LOGIN;
}




