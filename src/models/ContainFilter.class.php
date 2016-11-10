<?php

class ContainFilter {
  private $key;
  private $values;
  
  function __construct($key1, $values) {
    $this->key = $key;
    $this->values = $values;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    return $table . $this->key1 . " IN ?";
  }
  
  function getValue() {
    return implode(",", $this->values);
  }
}

?>
