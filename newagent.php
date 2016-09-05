<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

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

//catch agents actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'vouchercreate':
			$key = htmlentities($_POST["newvoucher"], false, "UTF-8");
			$voucher = new RegVoucher(0, $key, time());
			$FACTORIES::getRegVoucherFactory()->save($voucher);
			Util::refresh();
		case 'voucherdelete':
			$voucher = $FACTORIES::getRegVoucherFactory()->get(intval($_POST["voucher"]));
			$FACTORIES::getRegVoucherFactory()->delete($voucher);
			Util::refresh();
	}
}

$vouchers = $FACTORIES::getRegVoucherFactory()->filter(array());

$OBJECTS['vouchers'] = $vouchers;
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);




