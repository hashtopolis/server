<?php
require_once("../inc/load.php");

if(!isset($_POST['email']) || !isset($_POST['password'])){
	header("Location: index.php?err=1".time());
	die();
}

$username = $_POST['username'];
$password = $_POST['password'];

if(strlen($username) == 0 || strlen($password) == 0){
	header("Location: index.php?err=2".time());
	die();
}

$LOGIN->login($username, $password);

if($LOGIN->isLoggedin()){
	header("Location: index.php");
	die();
}

header("Location: index.php?err=3".time());




