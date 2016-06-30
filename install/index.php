<?php
use Bricky\Template;

//TODO: check here if there is already a load.ini set which gives a good connection to a good sql server
// -> if there is a valid connection, check if we can determine the hashtopus tables...
//   -> if it's hashtopus original, we can run update script, create a new admin user and we are done
//   -> if its already a hashtopussy installation, we just can mark it as installed and check that there is an admin user
// -> if there is no valid connection, ask for the details
//   -> if valid details are given, run setup script, create admin user and done

// -> ask the user if it's running with apache2 or other and create .htaccess files or say user he should
//    block some directories

// -> when installation is finished, tell to secure the install directory
// -> ask user for salts in the crypt class to provide and insert them

require_once(dirname(__FILE__)."/../inc/load.php");

$write_files = array(".", "../inc/crypt.class.php", "../inc/load.php", "../files", "../templates", "../inc", "../files", "../lang", "../models", "../templates");

if($INSTALL == 'DONE'){
	die("Installation is already done!");
}

$STEP = 0;
if(isset($_COOKIE['step'])){
	$STEP = $_COOKIE['step'];
}
$PREV = 0;
if(isset($_COOKIE['prev'])){
	$PREV = $_COOKIE['prev'];
}

//TODO: this is only for testing
if(isset($_GET['reset'])){
	setcookie("step", "", time() - 10);
	header("Location: index.php");
	die();
}

switch($STEP){
	case 0: //installation start
		if(!Util::checkWriteFiles($write_files)){
			setcookie("step", "50", time() + 3600);
			header("Location: index.php");
			die();
		}
		
		if(isset($_GET['type'])){
			$type = $_GET['type'];
			if($type == 'upgrade'){
				//hashtopus upgrade
				setcookie("step", "100", time() + 3600);
			}
			else{
				//clean install
				setcookie("step", "1", time() + 3600);
			}
			header("Location: index.php");
			die();
		}
		$TEMPLATE = new Template("install0");
		echo $TEMPLATE->render(array());
		break;
	case 50: //one or more files/dir is not writeable
		if(isset($_GET['check'])){
			if(Util::checkWriteFiles($write_files)){
				setcookie("step", "0", time() + 3600);
				header("Location: index.php");
				die();
			}
		}
		$TEMPLATE = new Template("install50");
		echo $TEMPLATE->render(array());
		break;
	default:
		die("Some error with steps happened, please start again!");
}


