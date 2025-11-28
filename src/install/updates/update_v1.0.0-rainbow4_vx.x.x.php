<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Config;
use DBA\Factory;
use DBA\HashType;
use DBA\QueryFilter;


if (DBA_TYPE == 'postgres' || Util::databaseTableExists("_sqlx_migrations")) {
  // this system is already using migrations, so it should NEVER do any of the updates
  return;
}

if (!isset($PRESENT["v1.0.0-rainbow4_prefix_user_and_end"])) {
  if (Util::databaseColumnExists("HealthCheckAgent", "end")) {
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `HealthCheckAgent` RENAME COLUMN `end` to `htp_end`;");
  }
  if (Util::databaseTableExists("User")) {
    Factory::getAgentFactory()->getDB()->query("RENAME TABLE `User` TO `htp_User`;");
  }
  $EXECUTED["v1.0.0-rainbow4_prefix_user_and_end"] = true;
}

