<?php

namespace DBA;

class ComparisonFilter extends Filter {
  private string $key1;
  private string $key2;
  private string $operator;
  
  /**
   * @var AbstractModelFactory|null
   */
  private $overrideFactory;
  
  function __construct(string $key1, string $key2, string $operator, ?AbstractModelFactory $overrideFactory = null) {
    $this->key1 = $key1;
    $this->key2 = $key2;
    $this->operator = $operator;
    $this->overrideFactory = $overrideFactory;
  }
  
  /**
   * @param AbstractModelFactory $factory
   * @param bool $includeTable
   * @return string
   */
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    if ($this->overrideFactory != null) {
      $factory = $this->overrideFactory;
    }
    $table = "";
    if ($includeTable) {
      $table = $factory->getMappedModelTable() . ".";
    }
    
    return $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->key1) . $this->operator . $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->key2);
  }
  
  function getValue() {
    return null;
  }
  
  function getHasValue(): bool {
    return false;
  }
}

