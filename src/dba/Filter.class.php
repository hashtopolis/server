<?php

namespace DBA;

abstract class Filter {
  /**
   * @param $table string
   * @return string
   */
  abstract function getQueryString($table = "");
  
  abstract function getValue();
  
  abstract function getHasValue();
}