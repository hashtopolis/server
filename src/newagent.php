<?php
use Bricky\Template;
require_once(dirname(__FILE__) . "/inc/load.php");

if(!$LOGIN->isLoggedin()){
	header("Location: index.php?err=4".time()."&fw=".urlencode($_SERVER['PHP_SELF']));
	die();
}
else if($LOGIN->getLevel() < 30){
	$TEMPLATE = new Template("restricted");
	die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("agents.new");
$MENU->setActive("agents_new");
$message = "";

//catch actions here...
if(isset($_POST['action'])){
    $agentHandler = new AgentHandler();
    $agentHandler->handle($_POST['action']);
    Util::refresh();
}

$vouchers = $FACTORIES::getRegVoucherFactory()->filter(array());

$OBJECTS['vouchers'] = $vouchers;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




