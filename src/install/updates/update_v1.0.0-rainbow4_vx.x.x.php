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

if (!isset($PRESENT["v1.0.0-rainbow4_migration_to_migrations"])) {
  if (!Util::databaseTableExists("_sqlx_migrations")) {
    // this creates the existing state for sqlx to continue with migrations for all further updates
    Factory::getAgentFactory()->getDB()->query("CREATE TABLE `_sqlx_migrations` (`version` bigint NOT NULL, `description` text NOT NULL, `installed_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, `success` tinyint(1) NOT NULL, `checksum` blob NOT NULL, `execution_time` bigint NOT NULL, PRIMARY KEY (`version`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;");
    Factory::getAgentFactory()->getDB()->query("INSERT INTO `_sqlx_migrations` VALUES (20251127000000,'initial','2025-11-28 14:29:13',1,0xA5A8F03AAD0827C86C4A380D935BF1CCB3B5D5F174D7FC40B3D267FD0B6BB7DD4181A9C25EFC5CFCE24DF760F4C2D881,1);");
  }
  $EXECUTED["v1.0.0-rainbow4_migration_to_migrations"] = true;
}
