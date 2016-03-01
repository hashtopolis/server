<?php
$dbhost="dbserver:3306";
$dbuser="dbuser";
$dbpass="dbpass";
$dbname="dbname";
$dblink = @mysqli_connect($dbhost,$dbuser,$dbpass,$dbname) or die("Error " . mysqli_connect_error($dblink));
$separator="\x01";
$sess_name="auth";

$kv=mysqli_query($dblink,"SELECT * FROM config");
$config=array();
while ($conf=mysqli_fetch_array($kv,MYSQLI_ASSOC)) {
  $config[$conf["item"]]=$conf["value"];
}

function generate_random($len) {
  $token="";
  $charset="abcdefghijlkmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ1234567890";
  for ($i=0;$i<$len;$i++) {
    $token.=$charset[rand(0,strlen($charset))];
  }
  return $token;
}

function bintohex($dato) {
  $ndato="";
  for ($i=0;$i<strlen($dato);$i++) {
    $zn=dechex(ord($dato[$i]));
    while (strlen($zn)<2) $zn="0".$zn;
    $ndato.=$zn;
  }
  return $ndato;
}

function hextobin($dato) {
  $ndato="";
  for ($i=0;$i<strlen($dato)-1;$i+=2) {
    $ndato.=chr(hexdec(substr($dato,$i,2)));
  }
  return $ndato;
}

?>