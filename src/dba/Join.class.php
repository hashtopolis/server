<?php

namespace DBA;

abstract class Join {
  /**
   * @return AbstractModelFactory
   */
  abstract function getOtherFactory();
  
  /**
   * @return string
   */
  abstract function getMatch1();
  
  /**
   * @return string
   */
  abstract function getMatch2();
  
  /**
   * @return string
   */
  abstract function getJoinType();

  /**
   * @return QueryFilter[] array of queryfilters that have to be perfromed on the join
   */
  abstract function getQueryFilters();
}