<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

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