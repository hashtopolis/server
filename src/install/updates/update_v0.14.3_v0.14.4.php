<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Config;
use DBA\Factory;
use DBA\QueryFilter;

if (!isset($PRESENT["v0.14.3_agentBinaries"])) {
  Util::checkAgentVersion("python", "0.7.3", true);
  $EXECUTED["v0.14.3_agentBinaries"] = true;
}
