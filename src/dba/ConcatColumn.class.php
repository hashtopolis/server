<?php

namespace DBA;

class ConcatColumn {
  private $value;
  /**
   * @var AbstractModelFactory
   */
  private $factory;

  function __construct($value, $factory)
  {
    $this->value = $value;
    $this->factory = $factory;
  }

  function getValue() {
    return $this->value;
  }

  function getFactory() {
    return $this->factory;
  }
}