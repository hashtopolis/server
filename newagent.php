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
			$voucher = $FACTORIES::getagentsFactory()->getDB()->quote($_POST["newvoucher"]);
			$FACTORIES::getagentsFactory()->getDB()->query("INSERT INTO regvouchers (voucher,time) VALUES ($voucher, ".time().")");
			header("Location: newagent.php");
			die();
			break;
		case 'voucherdelete':
			$voucher = $FACTORIES::getagentsFactory()->getDB()->quote($_POST['voucher']);
			$FACTORIES::getagentsFactory()->getDB()->query("DELETE FROM regvouchers WHERE voucher=$voucher");
			header("Location: newagent.php");
			die();
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




