<?php

use DBA\AccessGroupUser;
use DBA\QueryFilter;
use DBA\RightGroup;
use DBA\User;
use DBA\Factory;
use DBA\StoredValue;

require_once(dirname(__FILE__) . "/../inc/startup/load.php");

$write_files = array(".", "../inc/Encryption.class.php", "../inc/startup/load.php", "../files", "../templates", "../inc", "../files", "../lang", "../");

if ($INSTALL) {
  die("Installation is already done!");
}

$STEP = 0;
if (isset($_COOKIE['step'])) {
  $STEP = $_COOKIE['step'];
}
$PREV = 0;
if (isset($_COOKIE['prev'])) {
  $PREV = $_COOKIE['prev'];
}

switch ($STEP) {
  case 0: //installation start
    if (!Util::checkWriteFiles($write_files)) {
      setcookie("step", "50", time() + 3600);
      setcookie("prev", "0", time() + 3600);
      header("Location: index.php");
      die();
    }
    
    UI::add('32bit', (PHP_INT_SIZE == 4) ? true : false);
    if (isset($_GET['type'])) {
      $type = $_GET['type'];
      if ($type == 'install') {
        //clean install
        setcookie("step", "51", time() + 3600);
        setcookie("prev", "1", time() + 3600);
      }
      header("Location: index.php");
      die();
    }
    Template::loadInstance("install/0");
    echo Template::getInstance()->render(UI::getObjects());
    break;
  case 1: //clean installation was selected
    if (isset($_GET['next'])) {
      $query = file_get_contents(dirname(__FILE__) . "/hashtopolis.sql");
      Factory::getAgentFactory()->getDB()->query($query);
      $baseUrl = explode("/", $_SERVER['REQUEST_URI']);
      unset($baseUrl[sizeof($baseUrl) - 1]);
      if ($baseUrl[sizeof($baseUrl) - 1] == "install") {
        unset($baseUrl[sizeof($baseUrl) - 1]);
      }
      try {
        $urlConfig = ConfigUtils::get(DConfig::BASE_URL);
      }
      catch (HTException $e) {
        die("Failure in config: " . $e->getMessage());
      }
      $urlConfig->setValue(implode("/", $baseUrl));
      Factory::getConfigFactory()->update($urlConfig);
      setcookie("step", "52", time() + 3600);
      setcookie("prev", "2", time() + 3600);
      header("Location: index.php");
      die();
    }
    Template::loadInstance("install/1");
    echo Template::getInstance()->render([]);
    break;
  case 2: //installation should be finished now and user should be able to log in
    $load = file_get_contents(dirname(__FILE__) . "/../inc/conf.php");
    $load = str_replace('$INSTALL = false;', '$INSTALL = true;', $load);
    file_put_contents(dirname(__FILE__) . "/../inc/conf.php", $load);
    if (!file_exists(dirname(__FILE__) . "/../import")) {
      mkdir(dirname(__FILE__) . "/../import");
    }
    file_put_contents(dirname(__FILE__) . "/../import/.htaccess", "Order deny,allow\nDeny from all");

    // save version and build into database
    $version = new StoredValue("version", explode("+", StartupConfig::getInstance()->getVersion())[0]);
    Factory::getStoredValueFactory()->save($version);
    $build = new StoredValue("build", StartupConfig::getInstance()->getBuild());
    Factory::getStoredValueFactory()->save($build);
    setcookie("step", "", time() - 10);
    setcookie("prev", "", time() - 10);

    // protect installation directory
    file_put_contents(dirname(__FILE__) . "/.htaccess", "Order deny,allow\nDeny from all");

    sleep(1);
    Template::loadInstance("install/2");
    echo Template::getInstance()->render([]);
    break;
  case 50: //one or more files/dir is not writeable
    if (isset($_GET['check'])) {
      if (Util::checkWriteFiles($write_files)) {
        setcookie("step", "$PREV", time() + 3600);
        header("Location: index.php");
        die();
      }
    }
    Template::loadInstance("install/50");
    echo Template::getInstance()->render([]);
    break;
  case 51: //enter database connection details
    $fail = false;
    if (isset($CONN['user']) && $CONN['user'] != "__DBUSER__") {
      //it might be already configured, so we'll continue
      setcookie("step", "$PREV", time() + 3600);
      header("Location: index.php");
      die();
    }
    if (isset($_POST['check'])) {
      //check db connection
      $CONN = array(
        'user' => $_POST['user'],
        'pass' => $_POST['pass'],
        'server' => $_POST['server'],
        'db' => $_POST['db'],
        'port' => $_POST['port'],
        'type' => 'mysql',
      );
      if (Factory::getUserFactory()->getDB(true, $CONN) === null) {
        //connection not valid
        $fail = true;
      }
      else {
        //save database details
        
        $file = file_get_contents(dirname(__FILE__) . "/../inc/conf.template.php");
        $file = str_replace("__DBUSER__", $_POST['user'], $file);
        $file = str_replace("__DBPASS__", $_POST['pass'], $file);
        $file = str_replace("__DBSERVER__", $_POST['server'], $file);
        $file = str_replace("__DBDB__", $_POST['db'], $file);
        $file = str_replace("__DBPORT__", $_POST['port'], $file);
        file_put_contents(dirname(__FILE__) . "/../inc/conf.php", $file);
        setcookie("step", "$PREV", time() + 3600);
        sleep(1); // some times there are problems when reading to fast again and the file is not written to disk then
        header("Location: index.php");
        die();
      }
    }
    Template::loadInstance("install/51");
    echo Template::getInstance()->render(['failed' => $fail]);
    break;
  case 52: //database is filled with initial data now we create the user now
    // create pepper (this is required here that when we create the user, the included file already contains the right peppers
    $pepper = array(Util::randomString(50), Util::randomString(50), Util::randomString(50));
    $key = Util::randomString(40);
    $conf = file_get_contents(dirname(__FILE__) . "/../inc/conf.php");
    $conf = str_replace("__PEPPER1__", $pepper[0], str_replace("__PEPPER2__", $pepper[1], str_replace("__PEPPER3__", $pepper[2], $conf)));
    $conf = str_replace("__CSRF__", $key, $conf);
    file_put_contents(dirname(__FILE__) . "/../inc/conf.php", $conf);
    
    $message = "";
    if (isset($_POST['create'])) {
      $username = htmlentities(@$_POST['username'], ENT_QUOTES, "UTF-8");
      $password = @$_POST['password'];
      $email = @$_POST['email'];
      $repeat = @$_POST['repeat'];
      
      //do checks
      if (strlen($username) == 0 || strlen($password) == 0 || strlen($email) == 0 || strlen($repeat) == 0) {
        $message = "<div class='alert alert-danger'>You need to fill in all fields!</div>";
      }
      else if ($password != $repeat) {
        $message = "<div class='alert alert-danger'>Your entered passwords do not match!</div>";
      }
      else {
        Factory::getAgentFactory()->getDB()->beginTransaction();
        
        $qF = new QueryFilter(RightGroup::GROUP_NAME, "Administrator", "=");
        $group = Factory::getRightGroupFactory()->filter([Factory::FILTER => $qF]);
        $group = $group[0];
        $newSalt = Util::randomString(20);
        $newHash = Encryption::passwordHash($password, $newSalt);
        $user = new User(null, $username, $email, $newHash, $newSalt, 1, 1, 0, time(), 3600, $group->getId(), 0, "", "", "", "");
        Factory::getUserFactory()->save($user);
        
        // create default group
        $group = AccessUtils::getOrCreateDefaultAccessGroup();
        $groupUser = new AccessGroupUser(null, $group->getId(), $user->getId());
        Factory::getAccessGroupUserFactory()->save($groupUser);
        
        Factory::getAgentFactory()->getDB()->commit();
        setcookie("step", "$PREV", time() + 3600);
        header("Location: index.php");
        die();
      }
    }
    Template::loadInstance("install/52");
    echo Template::getInstance()->render(['message' => $message]);
    break;
  default:
    die("Some error with steps happened, please start again!");
}


