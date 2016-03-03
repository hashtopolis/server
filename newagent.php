<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

$TEMPLATE = new Template("agents.new");
$MENU->setActive("agents_new");
$message = "";

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'vouchercreate':
			//"INSERT INTO regvouchers (voucher,time) VALUES ('".mysqli_real_escape_string($dblink,$_POST["newvoucher"])."',$cas)");
			break;
		case 'voucherdelete':
			break;
	}
}

$res = $FACTORIES::getagentsFactory()->getDB()->query("SELECT voucher,time FROM regvouchers");
$res = $res->fetchAll();
$vouchers = array();
foreach($res as $entry){
	$set = new DataSet();
	$set->setValues($entry);
	$vouchers[] = $set;
}

$OBJECTS['vouchers'] = $vouchers;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




