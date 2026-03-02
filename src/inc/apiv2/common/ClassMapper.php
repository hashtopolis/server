<?php

namespace Hashtopolis\inc\apiv2\common;

/* Quick to create auto-generated lookup table between DBA Objects and APIv2 classes */
class ClassMapper {
  private array $store = array();
  
  public function add($key, $value): void {
    $this->store[$key] = $value;
  }
  
  public function get($key): string {
    return $this->store[$key];
  }
}