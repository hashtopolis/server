<?php

/**
 * @deprecated
 *
 * This script should setup a server with the according version.
 * Note: we assume that the CI script already set up apache2 and configured to point to the correct directory for the test
 */

if (sizeof($argv) != 2) {
  die("Invalid number of arguments!\nphp -f setup.php <tag|branch>\n");
}
$version = $argv[1];
$envPath = "/var/www/html/hashtopolis/";

// simulate installation with creating conf.php (we just put in some peppers)
$CONFIG = "<?php\n\n";
$CONFIG .= '$CONN["user"] = "root";' . "\n";
$CONFIG .= '$CONN["pass"] = "";' . "\n";
$CONFIG .= '$CONN["server"] = "localhost";' . "\n";
$CONFIG .= '$CONN["db"] = "hashtopolis";' . "\n";
$CONFIG .= '$CONN["port"] = "3306";' . "\n";
$CONFIG .= '$INSTALL = true;';
$CONFIG .= '$PEPPER = ["abcd", "bcde", "cdef", "aaaa"];' . "\n";

file_put_contents($envPath . "src/inc/conf.php", $CONFIG);
// this is to make sure that also old db configs are working
file_put_contents($envPath . "src/inc/db.php", $CONFIG);

$db = new PDO("mysql:host=localhost;port=3306", "root", "");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
  $db->query("CREATE DATABASE IF NOT EXISTS hashtopolis;");
  $db->query("USE hashtopolis;");
  $db->query(file_get_contents($envPath . "src/migrations/mysql/20251127000000_initial.sql"));
}
catch (PDOException $e) {
  fwrite(STDERR, "Failed to initialize database: " . $e->getMessage());
  exit(-1);
}

$load = file_get_contents($envPath . "src/inc/startup/load.php");
$load = str_replace('ini_set("display_errors", "0");', 'ini_set("display_errors", "1");', $load);
file_put_contents($envPath . "src/inc/startup/load.php", $load);
