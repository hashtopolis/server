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
    Factory::getAgentFactory()->getDB()->query("INSERT INTO `_sqlx_migrations` VALUES (20251127000000,'initial','2025-11-28 14:29:13',1,0x87B4F9CE14A0C5A131A84D96044E89BAD641D0043E19141341C2DE2D307A25B748EF4F356CF4E0ACE439F84EC6C8F77A,1);");
  }
  $EXECUTED["v1.0.0-rainbow4_migration_to_migrations"] = true;
}
