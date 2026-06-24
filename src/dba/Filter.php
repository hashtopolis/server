<?php

namespace Hashtopolis\dba;

abstract class Filter {
  /**
   * @param AbstractModelFactory $factory
   * @param bool $includeTable
   * @return string
   */
  abstract function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string;
  
  abstract function getValue();
  
  abstract function getHasValue();
}