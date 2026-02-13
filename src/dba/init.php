<?php

require_once(dirname(__FILE__) . "/AbstractModel.php");
require_once(dirname(__FILE__) . "/AbstractModelFactory.php");
require_once(dirname(__FILE__) . "/Aggregation.php");
require_once(dirname(__FILE__) . "/Filter.php");
require_once(dirname(__FILE__) . "/Order.php");
require_once(dirname(__FILE__) . "/CoalesceOrderFilter.php");
require_once(dirname(__FILE__) . "/Join.php");
require_once(dirname(__FILE__) . "/Group.php");
require_once(dirname(__FILE__) . "/Limit.php");
require_once(dirname(__FILE__) . "/ComparisonFilter.php");
require_once(dirname(__FILE__) . "/ContainFilter.php");
require_once(dirname(__FILE__) . "/JoinFilter.php");
require_once(dirname(__FILE__) . "/OrderFilter.php");
require_once(dirname(__FILE__) . "/PaginationFilter.php");
require_once(dirname(__FILE__) . "/QueryFilter.php");
require_once(dirname(__FILE__) . "/GroupFilter.php");
require_once(dirname(__FILE__) . "/LimitFilter.php");
require_once(dirname(__FILE__) . "/Util.php");
require_once(dirname(__FILE__) . "/UpdateSet.php");
require_once(dirname(__FILE__) . "/MassUpdateSet.php");
require_once(dirname(__FILE__) . "/LikeFilter.php");
require_once(dirname(__FILE__) . "/LikeFilterInsensitive.php");
require_once(dirname(__FILE__) . "/QueryFilterNoCase.php");
require_once(dirname(__FILE__) . "/QueryFilterWithNull.php");

$entries = scandir(dirname(__FILE__) . "/models");
foreach ($entries as $entry) {
  if (str_contains($entry, ".php")) {
    require_once(dirname(__FILE__) . "/models/" . $entry);
  }
}

require_once(dirname(__FILE__) . "/Factory.php");
define("DBA_VERSION", "1.0.0");
