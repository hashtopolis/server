<?php

namespace Hashtopolis\dba;

use Hashtopolis\inc\StartupConfig;

class LikeFilter extends Filter {
  private string $key;
  private string $value;
  private bool $match;
  
  private ?AbstractModelFactory $overrideFactory;
  
  function __construct(string $key, string $value, ?AbstractModelFactory $overrideFactory = null) {
    $this->key = $key;
    $this->value = $value;
    $this->overrideFactory = $overrideFactory;
    $this->match = true;
  }
  
  function setMatch($status): void {
    $this->match = $status;
  }
  
  function getQueryString(AbstractModelFactory $factory, bool $includeTable = false): string {
    if ($this->overrideFactory != null) {
      $factory = $this->overrideFactory;
    }
    $table = "";
    if ($includeTable) {
      $table = $factory->getMappedModelTable() . ".";
    }
    
    $inv = "";
    if ($this->match === false) {
      $inv = " NOT";
    }
    
    // it is not ideal to have to make a distinction between the DB types here, but currently there does not seem to be another solution to achieve real case-sensitive like filtering
    $likeStatement = " LIKE BINARY ?";
    if (StartupConfig::getInstance()->getDatabaseType() == 'postgres') {
      $likeStatement = " LIKE ? COLLATE \"C\"";
    }
    return $table . AbstractModelFactory::getMappedModelKey($factory->getNullObject(), $this->key) . $inv . $likeStatement;
  }
  
  function getValue(): string {
    return $this->value;
  }
  
  function getHasValue(): bool {
    return true;
  }
}
