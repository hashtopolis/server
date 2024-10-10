<?php

namespace DBA;

abstract class Limit {
  /**
   * @param $table string
   * @return string
   */
  abstract function getQueryString();
}