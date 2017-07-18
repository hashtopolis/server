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
}