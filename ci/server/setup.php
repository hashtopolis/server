<?php

/**
 * This script should setup a server with the according version.
 * Note: we assume that the CI script already set up apache2 and configured to point to the correct directory for the test
 */

if(sizeof($argv) != 2){
  die("Invalid number of arguments!\nphp -f setup.php <tag|branch>\n");
}
$version = $argv[1];

// clone hashtopolis and checkout requested version
system("rm -rf '/var/www/html/hashtopolis'");
system("cd '/var/www/html/' && git clone https://github.com/s3inlc/hashtopolis hashtopolis");
system("cd '/var/www/html/hashtopolis' && git checkout '$version'");
$envPath = "/var/www/html/hashtopolis/";

// simulate installation with creating db.php (we just leave the peppers default)
$DBCONFIG = "<?php\n\n";
$DBCONFIG .= '$CONN["user"] = "root";'. "\n";
$DBCONFIG .= '$CONN["pass"] = "";'."\n";
$DBCONFIG .= '$CONN["server"] = "localhost";'."\n";
$DBCONFIG .= '$CONN["db"] = "hashtopolis";'."\n";
$DBCONFIG .= '$CONN["port"] = "3306";'."\n";
$DBCONFIG .= '$INSTALL = true;';
file_put_contents($envPath."src/inc/db.php", $DBCONFIG);

$db = new PDO("mysql:host=localhost;port=3306", "root", "");
$db->query("CREATE DATABASE hashtopolis;");
$db->query("USE hashtopolis;");
$db->query(file_get_contents($envPath."src/install/hashtopolis.sql"));

$load = file_get_contents($envPath."src/inc/load.php");
$load = str_replace('ini_set("display_errors", "0");','ini_set("display_errors", "1");', $load);
file_put_contents($envPath."src/inc/load.php", $load);

//system("sudo chown -R www-data '".dirname(__FILE__)."/../env'");
