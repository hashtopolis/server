<?php /** @noinspection SqlNoDataSourceInspection */

use DBA\Factory;

if (!isset($PRESENT["v0.14.x_pagination"])) {
  Factory::getAgentFactory()->getDB()->query("INSERT INTO `Config` (`configId`, `configSectionId`, `item`, `value`) VALUES
    (78, 3, 'defaultPageSize', '10000');"); 
  Factory::getAgentFactory()->getDB()->query("INSERT INTO `Config` (`configId`, `configSectionId`, `item`, `value`) VALUES
    (79, 3, 'maxPageSize', '50000');"); 
    $EXECUTED["v0.14.x_pagination"];
}