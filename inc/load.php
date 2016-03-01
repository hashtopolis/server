<?php

//set to 0 after finished debugging
ini_set("display_errors", "1");

$CONN['user'] = 'godesig_coray';
$CONN['pass'] = 'bpO5RhhY3Tv8MHUI';
$CONN['server'] = 'godesig.mysql.db.internal';
$CONN['db'] = 'godesig_coray';

require_once(dirname(__FILE__)."/lang.class.php");
require_once(dirname(__FILE__)."/template.class.php");
require_once(dirname(__FILE__)."/login.class.php");
require_once(dirname(__FILE__)."/menu.class.php");
require_once(dirname(__FILE__)."/crypt.class.php");
require_once(dirname(__FILE__)."/util.class.php");
require_once(dirname(__FILE__)."/dataset.class.php");

require_once(dirname(__FILE__)."/../models/AbstractModel.class.php");
require_once(dirname(__FILE__)."/../models/AbstractModelFactory.class.php");
require_once(dirname(__FILE__)."/../models/JoinFilter.class.php");
require_once(dirname(__FILE__)."/../models/OrderFilter.class.php");
require_once(dirname(__FILE__)."/../models/QueryFilter.class.php");

require_once(dirname(__FILE__)."/../models/Bill.class.php");
require_once(dirname(__FILE__)."/../models/BillFactory.class.php");
require_once(dirname(__FILE__)."/../models/BillBoughtProduct.class.php");
require_once(dirname(__FILE__)."/../models/BillBoughtProductFactory.class.php");
require_once(dirname(__FILE__)."/../models/BoughtProduct.class.php");
require_once(dirname(__FILE__)."/../models/BoughtProductFactory.class.php");
require_once(dirname(__FILE__)."/../models/Product.class.php");
require_once(dirname(__FILE__)."/../models/ProductFactory.class.php");
require_once(dirname(__FILE__)."/../models/Session.class.php");
require_once(dirname(__FILE__)."/../models/SessionFactory.class.php");
require_once(dirname(__FILE__)."/../models/User.class.php");
require_once(dirname(__FILE__)."/../models/UserFactory.class.php");
require_once(dirname(__FILE__)."/../models/UserData.class.php");
require_once(dirname(__FILE__)."/../models/UserDataFactory.class.php");

require_once(dirname(__FILE__)."/factory.class.php");

$FACTORIES = new Factory();
$LOGIN = new Login();

$OBJECTS = array();
$OBJECTS['login'] = $LOGIN;





