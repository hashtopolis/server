<?php

require_once(dirname(__FILE__) . "/AbstractModel.class.php");
require_once(dirname(__FILE__) . "/AbstractModelFactory.class.php");
require_once(dirname(__FILE__) . "/Aggregation.class.php");
require_once(dirname(__FILE__) . "/Filter.class.php");
require_once(dirname(__FILE__) . "/Order.class.php");
require_once(dirname(__FILE__) . "/ConcatColumn.class.php");
require_once(dirname(__FILE__) . "/ConcatLikeFilterInsensitive.class.php");
require_once(dirname(__FILE__) . "/ConcatOrderFilter.class.php");
require_once(dirname(__FILE__) . "/Join.class.php");
require_once(dirname(__FILE__) . "/Group.class.php");
require_once(dirname(__FILE__) . "/Limit.class.php");
require_once(dirname(__FILE__) . "/ComparisonFilter.class.php");
require_once(dirname(__FILE__) . "/ContainFilter.class.php");
require_once(dirname(__FILE__) . "/JoinFilter.class.php");
require_once(dirname(__FILE__) . "/OrderFilter.class.php");
require_once(dirname(__FILE__) . "/PaginationFilter.class.php");
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
  if (str_contains($entry, ".class.php")) {
    require_once(dirname(__FILE__) . "/models/" . $entry);
  }
}

require_once(dirname(__FILE__) . "/Factory.class.php");
define("DBA_VERSION", "1.0.0");
