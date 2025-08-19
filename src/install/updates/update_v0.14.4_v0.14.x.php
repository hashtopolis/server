<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Factory;

if (!isset($PRESENT["v0.14.x_update_agent_binary"])) {
  if (Util::databaseColumnExists("AgentBinary", "type")) {
    Factory::getAgentFactory()->getDB()->query("ALTER TABLE `AgentBinary` RENAME COLUMN `type` to `binaryType`;");
    $EXECUTED["v0.14.x_update_agent_binary"] = true;
  }
}

?>