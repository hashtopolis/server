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
system("cd '".dirname(__FILE__)."/../' && git clone https://github.com/s3inlc/hashtopolis env");
system("cd '".dirname(__FILE__)."/../env' && git checkout '$version'");
$envPath = dirname(__FILE__)."/../env/";

// simulate installation with creating db.php (we just leave the peppers default)
// TODO: fill in pass and maybe correct user
$DBCONFIG = '$CONN["user"] = "root";\n$CONN["pass"] = "root";\n$CONN["server"] = "localhost";\n$CONN["db"] = "hashtopolis";\n$CONN["port"] = "3306";\n$INSTALL = true;';
file_put_contents($envPath."src/inc/db.php", $DBCONFIG);

system("chown -R www-data '".dirname(__FILE__)."/../env'");
