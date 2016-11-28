<?php

class ContainFilter {
  private $key;
  private $values;
  
  function __construct($key, $values) {
    $this->key = $key;
    $this->values = $values;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    $placeholders = array();
    for($x=0;$x<sizeof($this->values);$x++){
      $placeholders[] = "?";
    }
    return $table . $this->key . " IN (".implode(",", $placeholders).")";
  }
  
  function getValue() {
    return $this->values;
  }
}

?>
