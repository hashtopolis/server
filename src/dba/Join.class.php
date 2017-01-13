<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 03.01.17
 * Time: 14:49
 */

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
}