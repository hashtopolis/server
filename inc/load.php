<?php

//set to 1 for debugging
ini_set("display_errors", "1");
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

require_once(dirname(__FILE__)."/../models/Agent.class.php");
require_once(dirname(__FILE__)."/../models/AgentFactory.class.php");
require_once(dirname(__FILE__)."/../models/Assignment.class.php");
require_once(dirname(__FILE__)."/../models/AssignmentFactory.class.php");
require_once(dirname(__FILE__)."/../models/Chunk.class.php");
require_once(dirname(__FILE__)."/../models/ChunkFactory.class.php");
require_once(dirname(__FILE__)."/../models/Config.class.php");
require_once(dirname(__FILE__)."/../models/ConfigFactory.class.php");
require_once(dirname(__FILE__)."/../models/AgentError.class.php");
require_once(dirname(__FILE__)."/../models/AgentErrorFactory.class.php");
require_once(dirname(__FILE__)."/../models/File.class.php");
require_once(dirname(__FILE__)."/../models/FileFactory.class.php");
require_once(dirname(__FILE__)."/../models/Hash.class.php");
require_once(dirname(__FILE__)."/../models/HashFactory.class.php");
require_once(dirname(__FILE__)."/../models/HashBinary.class.php");
require_once(dirname(__FILE__)."/../models/HashBinaryFactory.class.php");
require_once(dirname(__FILE__)."/../models/HashcatRelease.class.php");
require_once(dirname(__FILE__)."/../models/HashcatReleaseFactory.class.php");
require_once(dirname(__FILE__)."/../models/Hashlist.class.php");
require_once(dirname(__FILE__)."/../models/HashlistFactory.class.php");
require_once(dirname(__FILE__)."/../models/HashlistAgent.class.php");
require_once(dirname(__FILE__)."/../models/HashlistAgentFactory.class.php");
require_once(dirname(__FILE__)."/../models/HashType.class.php");
require_once(dirname(__FILE__)."/../models/HashTypeFactory.class.php");
require_once(dirname(__FILE__)."/../models/RegVoucher.class.php");
require_once(dirname(__FILE__)."/../models/RegVoucherFactory.class.php");
require_once(dirname(__FILE__)."/../models/SuperHashlist.class.php");
require_once(dirname(__FILE__)."/../models/SuperHashlistFactory.class.php");
require_once(dirname(__FILE__)."/../models/SuperHashlistHashlist.class.php");
require_once(dirname(__FILE__)."/../models/SuperHashlistHashlistFactory.class.php");
require_once(dirname(__FILE__)."/../models/Supertask.class.php");
require_once(dirname(__FILE__)."/../models/SupertaskFactory.class.php");
require_once(dirname(__FILE__)."/../models/SupertaskTask.class.php");
require_once(dirname(__FILE__)."/../models/SupertaskTaskFactory.class.php");
require_once(dirname(__FILE__)."/../models/Task.class.php");
require_once(dirname(__FILE__)."/../models/TaskFactory.class.php");
require_once(dirname(__FILE__)."/../models/TaskFile.class.php");
require_once(dirname(__FILE__)."/../models/TaskFileFactory.class.php");
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

	$res = $FACTORIES::getConfigFactory()->filter(array());
	$CONFIG = new DataSet();
	foreach($res as $entry){
		$CONFIG->addValue($entry->getItem(), $entry->getValue());
	}
	$OBJECTS['config'] = $CONFIG;
	
	//set autorefresh to false for all pages
	$OBJECTS['autorefresh'] = 0;
	
	$DB = $FACTORIES::getAgentFactory()->getDB();
}


