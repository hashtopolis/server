<?php

if (file_exists(dirname(__FILE__) . "/conf.php")) {
  // this is either an existing setup, or a new setup without docker
  include(dirname(__FILE__) . "/conf.php");
  
  // check if directories is set, otherwise set the defaults for it
  if (!isset($DIRECTORIES)) {
    $DIRECTORIES = [
      "files" => dirname(__FILE__) . "/../files/",
      "import" => dirname(__FILE__) . "/../import/",
      "log" => dirname(__FILE__) . "/../log/",
      "config" => dirname(__FILE__) . "/../config/"
    ];
  }
  
  // if a pepper is set from an older version, we have to save it to the new file location
  if (isset($PEPPER) && !file_exists($DIRECTORIES['config'] . "/config.json")) {
    file_put_contents($DIRECTORIES['config'] . "/config.json", json_encode(array('PEPPER' => $PEPPER)));
  }
} else {
  // read env variables (when running with docker-compose)
  $CONN['user'] = getenv('HASHTOPOLIS_DB_USER');
  $CONN['pass'] = getenv('HASHTOPOLIS_DB_PASS');
  $CONN['server'] = getenv('HASHTOPOLIS_DB_HOST');
  $CONN['db'] = getenv('HASHTOPOLIS_DB_DATABASE');
  $CONN['port'] = 3306;
  
  $DIRECTORIES = [
    "files" => "/usr/local/share/hashtopolis/files",
    "import" => "/usr/local/share/hashtopolis/import",
    "log" => "/usr/local/share/hashtopolis/log",
    "config" => "/usr/local/share/hashtopolis/config"
  ];
  
  // update from env if set
  if (getenv('HASHTOPOLIS_FILES_PATH') !== false) {
    $DIRECTORIES["files"] = getenv('HASHTOPOLIS_FILES_PATH');
  }
  if (getenv('HASHTOPOLIS_IMPORT_PATH') !== false) {
    $DIRECTORIES["import"] = getenv('HASHTOPOLIS_IMPORT_PATH');
  }
  if (getenv('HASHTOPOLIS_LOG_PATH') !== false) {
    $DIRECTORIES["log"] = getenv('HASHTOPOLIS_LOG_PATH');
  }
}
// load pepper
// test if config file exists
if (file_exists($DIRECTORIES['config'] . "/config.json")) {
  $CONFIG = json_decode(file_get_contents($DIRECTORIES['config'] . "/config.json"), true);
  $PEPPER = $CONFIG['PEPPER'];
} else {
  $CONFIG = [];
}
