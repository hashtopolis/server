<?php

class StartupConfig {
  private static ?StartupConfig $instance = null;
  
  private array $directories   = [];
  private array $db_properties = [];
  private array $peppers       = [];
  
  /**
   * The choice here is to define the possible keys for config settings only private and only allow to
   * retrieve them via specific getter functions for each. This way we really enforce developers to only
   * use what is available and any new global config value has to be explicitly added to the StartupConfig
   * class.
   * This way we can also implement the StartupConfig class that it ALWAYS returns something for come values.
   */
  private const DIRECTORY_FILES  = "files";
  private const DIRECTORY_IMPORT = "import";
  private const DIRECTORY_LOG    = "log";
  private const DIRECTORY_CONFIG = "config";
  private const DIRECTORY_TUS    = "tus";
  
  private const DB_PROPERTY_TYPE   = "type";
  private const DB_PROPERTY_USER   = "user";
  private const DB_PROPERTY_PASS   = "pass";
  private const DB_PROPERTY_DB     = "db";
  private const DB_PROPERTY_SERVER = "server";
  private const DB_PROPERTY_PORT   = "port";
  
  /**
   * @param bool $force
   * @return StartupConfig
   */
  public static function getInstance(bool $force = false): StartupConfig {
    if (self::$instance == null || $force) {
      self::$instance = new StartupConfig();
    }
    return self::$instance;
  }
  
  /**
   * Force reloading the startup config
   */
  public static function reload(): void {
    StartupConfig::getInstance(true);
  }
  
  public function __construct() {
    // set all default values for the docker environment
    $this->directories = [
      "files" => "/usr/local/share/hashtopolis/files",
      "import" => "/usr/local/share/hashtopolis/import",
      "log" => "/usr/local/share/hashtopolis/log",
      "config" => "/usr/local/share/hashtopolis/config",
      "tus" => "/var/tmp/tus/",
    ];
    
    $this->db_properties = [
      self::DB_PROPERTY_TYPE => "",
      self::DB_PROPERTY_USER => "",
      self::DB_PROPERTY_PASS => "",
      self::DB_PROPERTY_DB => "",
      self::DB_PROPERTY_SERVER => "",
      self::DB_PROPERTY_PORT => "0",
    ];
    
    $this->peppers = ["", "", "", ""];
    
    // this is a legacy check for old setups (through manual install) where some settings were stored in the conf.php
    if (file_exists(dirname(__FILE__) . "/conf.php")) {
      $this->loadLegacyConfig();
    }
    else {
      $this->loadEnv();
    }
    
    // at this point a config.json MUST exist (either from migration from legacy setup or from docker startup
    // we still test for existence, just in case
    if (file_exists($this->getDirectoryConfig() . "/config.json")) {
      $config = json_decode(file_get_contents($this->getDirectoryConfig() . "/config.json"), true);
      if (isset($config['PEPPER']) && sizeof($config['PEPPER']) == 4) {
        $this->peppers = $config['PEPPER'];
      }
    }
  }
  
  /**
   * Loads the required config values from the environment variables
   *
   * @return void
   */
  private function loadEnv(): void {
    $this->db_properties[self::DB_PROPERTY_USER] = getenv('HASHTOPOLIS_DB_USER');
    $this->db_properties[self::DB_PROPERTY_PASS] = getenv('HASHTOPOLIS_DB_PASS');
    $this->db_properties[self::DB_PROPERTY_SERVER] = getenv('HASHTOPOLIS_DB_HOST');
    $this->db_properties[self::DB_PROPERTY_DB] = getenv('HASHTOPOLIS_DB_DATABASE');
    
    if (getenv('HASHTOPOLIS_DB_TYPE') !== false) {
      $this->db_properties[self::DB_PROPERTY_TYPE] = getenv('HASHTOPOLIS_DB_TYPE');
    }
    else {
      $this->db_properties[self::DB_PROPERTY_TYPE] = "mysql";
    }
    
    if (getenv('HASHTOPOLIS_DB_PORT') !== false) {
      $this->db_properties[self::DB_PROPERTY_PORT] = getenv('HASHTOPOLIS_DB_PORT');
    }
    else {
      switch ($this->db_properties[self::DB_PROPERTY_TYPE]) {
        case 'mysql':
          $this->db_properties[self::DB_PROPERTY_PORT] = '3306';
          break;
        case 'postgres':
          $this->db_properties[self::DB_PROPERTY_PORT] = '5432';
          break;
      }
    }
    
    // update from env if set
    if (getenv('HASHTOPOLIS_FILES_PATH') !== false) {
      $this->directories[self::DIRECTORY_FILES] = getenv('HASHTOPOLIS_FILES_PATH');
    }
    if (getenv('HASHTOPOLIS_IMPORT_PATH') !== false) {
      $this->directories[self::DIRECTORY_IMPORT] = getenv('HASHTOPOLIS_IMPORT_PATH');
    }
    if (getenv('HASHTOPOLIS_LOG_PATH') !== false) {
      $this->directories[self::DIRECTORY_LOG] = getenv('HASHTOPOLIS_LOG_PATH');
    }
    if (getenv('HASHTOPOLIS_CONFIG_PATH') !== false) {
      $this->directories[self::DIRECTORY_CONFIG] = getenv('HASHTOPOLIS_CONFIG_PATH');
    }
    if (getenv('HASHTOPOLIS_TUS_PATH') !== false) {
      $this->directories[self::DIRECTORY_TUS] = getenv('HASHTOPOLIS_TUS_PATH');
    }
  }
  
  /**
   * @return void
   * @deprecated
   *
   * Loads the required config values from an old format conf.php file which was created on setups using
   * the built-in install routine.
   *
   */
  private function loadLegacyConfig(): void {
    $CONN = [];  // make analyzer happy, the $CONN MUST be set in the old style conf.php file
    
    // this is either an existing setup, or a new setup without docker
    include(dirname(__FILE__) . "/conf.php");
    
    // check if directories is set, otherwise set the defaults for it
    if (!isset($DIRECTORIES)) {
      $this->directories = [
        "files" => dirname(__FILE__) . "/../files/",
        "import" => dirname(__FILE__) . "/../import/",
        "log" => dirname(__FILE__) . "/../log/",
        "config" => dirname(__FILE__) . "/../config/",
        "tus" => "/var/tmp/tus/",
      ];
    }
    else {
      $this->directories = $DIRECTORIES;
    }
    
    // extract old database settings format
    $this->db_properties[self::DB_PROPERTY_TYPE] = "mysql"; // old setups can only by mysql
    $this->db_properties[self::DB_PROPERTY_USER] = $CONN['user'];
    $this->db_properties[self::DB_PROPERTY_PASS] = $CONN['pass'];
    $this->db_properties[self::DB_PROPERTY_DB] = $CONN['db'];
    $this->db_properties[self::DB_PROPERTY_SERVER] = $CONN['server'];
    $this->db_properties[self::DB_PROPERTY_PORT] = $CONN['port'];
    
    // if a pepper is set from an older version, we have to save it to the new file location
    if (isset($PEPPER) && !file_exists($this->directories['config'] . "/config.json")) {
      file_put_contents($this->directories['config'] . "/config.json", json_encode(array('PEPPER' => $PEPPER)));
    }
  }
  
  public function getDirectories(): array {
    return $this->directories;
  }
  
  public function getDirectoryFiles(): string {
    return $this->directories[self::DIRECTORY_FILES];
  }
  
  public function getDirectoryImport(): string {
    return $this->directories[self::DIRECTORY_IMPORT];
  }
  
  public function getDirectoryLog(): string {
    return $this->directories[self::DIRECTORY_LOG];
  }
  
  public function getDirectoryConfig(): string {
    return $this->directories[self::DIRECTORY_CONFIG];
  }
  
  public function getDirectoryTus(): string {
    return $this->directories[self::DIRECTORY_TUS];
  }
  
  public function getDatabaseType(): string {
    return $this->db_properties[self::DB_PROPERTY_TYPE];
  }
  
  public function getDatabaseUser(): string {
    return $this->db_properties[self::DB_PROPERTY_USER];
  }
  
  public function getDatabasePassword(): string {
    return $this->db_properties[self::DB_PROPERTY_PASS];
  }
  
  public function getDatabaseDB(): string {
    return $this->db_properties[self::DB_PROPERTY_DB];
  }
  
  public function getDatabaseServer(): string {
    return $this->db_properties[self::DB_PROPERTY_SERVER];
  }
  
  public function getDatabasePort(): string {
    return $this->db_properties[self::DB_PROPERTY_PORT];
  }
  
  public function getPepper(int $index): string {
    if ($index < 0 || $index >= count($this->peppers)) {
      return "";
    }
    return $this->peppers[$index];
  }
  
  public function getVersion(): string {
    return "v1.0.0-rainbow5";
  }
  
  public function getBuild(): string {
    return "repository";
  }
  
  public function getHost(): string {
    $host = @$_SERVER['SERVER_NAME'];
    
    if ($host === null){
      $host = "";
    }
    
    return $host;
  }
}