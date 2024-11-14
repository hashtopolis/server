<?php

/** @var $CONN array */
define("DBA_SERVER", (isset($CONN['server'])) ? $CONN['server'] : "");
define("DBA_DB", (isset($CONN['db'])) ? $CONN['db'] : "");
define("DBA_USER", (isset($CONN['user'])) ? $CONN['user'] : "");
define("DBA_PASS", (isset($CONN['pass'])) ? $CONN['pass'] : "");
define("DBA_PORT", (isset($CONN['port'])) ? $CONN['port'] : "");

require_once(dirname(__FILE__) . "/AbstractModel.class.php");
require_once(dirname(__FILE__) . "/AbstractModelFactory.class.php");
require_once(dirname(__FILE__) . "/Filter.class.php");
require_once(dirname(__FILE__) . "/Order.class.php");
require_once(dirname(__FILE__) . "/Join.class.php");
require_once(dirname(__FILE__) . "/Group.class.php");
require_once(dirname(__FILE__) . "/Limit.class.php");
require_once(dirname(__FILE__) . "/ComparisonFilter.class.php");
require_once(dirname(__FILE__) . "/ContainFilter.class.php");
require_once(dirname(__FILE__) . "/JoinFilter.class.php");
require_once(dirname(__FILE__) . "/OrderFilter.class.php");
require_once(dirname(__FILE__) . "/QueryFilter.class.php");
require_once(dirname(__FILE__) . "/GroupFilter.class.php");
require_once(dirname(__FILE__) . "/LimitFilter.class.php");
require_once(dirname(__FILE__) . "/Util.class.php");
require_once(dirname(__FILE__) . "/UpdateSet.class.php");
require_once(dirname(__FILE__) . "/MassUpdateSet.class.php");
require_once(dirname(__FILE__) . "/LikeFilter.class.php");
require_once(dirname(__FILE__) . "/LikeFilterInsensitive.class.php");
require_once(dirname(__FILE__) . "/QueryFilterNoCase.class.php");
require_once(dirname(__FILE__) . "/QueryFilterWithNull.class.php");

$entries = scandir(dirname(__FILE__) . "/models");
foreach ($entries as $entry) {
  if (strpos($entry, ".class.php") !== false) {
    require_once(dirname(__FILE__) . "/models/" . $entry);
  }
}

require_once(dirname(__FILE__) . "/Factory.class.php");
define("DBA_VERSION", "1.0.0");