<?php

namespace Hashtopolis\dba;

class ConcatColumn {
  private string $value;
  private AbstractModelFactory $factory;

  function __construct($value, $factory)
  {
    $this->value = $value;
    $this->factory = $factory;
  }

  function getValue(): string {
    return $this->value;
  }

  function getFactory(): AbstractModelFactory {
    return $this->factory;
  }
}