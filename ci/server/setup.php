<?php

/**
 * This script should setup a server with the according version.
 * Note: we assume that the CI script already set up apache2 and configured to point to the correct directory for the test
 */

if(sizeof($argv) != 2){
  die("Invalid number of arguments!\nphp -f setup.php <tag|branch>\n");
}
$version = $argv[1];
$envPath = "/var/www/html/hashtopolis/";

// simulate installation with creating db.php (we just leave the peppers default)
$CONFIG = "<?php\n\n";
$CONFIG .= '$CONN["user"] = "root";'. "\n";
$CONFIG .= '$CONN["pass"] = "";'."\n";
$CONFIG .= '$CONN["server"] = "localhost";'."\n";
$CONFIG .= '$CONN["db"] = "hashtopolis";'."\n";
$CONFIG .= '$CONN["port"] = "3306";'."\n";
$CONFIG .= '$INSTALL = true;';
if($version == 'v0.8.0'){
	file_put_contents($envPath."src/inc/db.php", $CONFIG);
}
else{
	$CONFIG .= '$PEPPER = ["1", "2", "3", "4"];'."\n";
	file_put_contents($envPath."src/inc/conf.php", $CONFIG);
}

$db = new PDO("mysql:host=localhost;port=3306", "root", "");
$db->query("CREATE DATABASE hashtopolis;");
$db->query("USE hashtopolis;");
$db->query(file_get_contents($envPath."src/install/hashtopolis.sql"));

$load = file_get_contents($envPath."src/inc/load.php");
$load = str_replace('ini_set("display_errors", "0");','ini_set("display_errors", "1");', $load);
file_put_contents($envPath."src/inc/load.php", $load);
