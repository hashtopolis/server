<?php

namespace Hashtopolis\dba;

abstract class Join {
  abstract function getOtherFactory(): AbstractModelFactory;
  
  abstract function getMatch1(): string;
  
  abstract function getMatch2(): string;
  
  /**
   * @return string
   */
  abstract function getJoinType(): string;

  /**
   * @return QueryFilter[] array of QueryFilters that have to be performed on the join
   */
  abstract function getQueryFilters(): array;
}