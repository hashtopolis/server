<?php

namespace DBA;

class OrderFilter extends Order {
  private $by;
  private $type;
  
  function __construct($by, $type) {
    $this->by = $by;
    $this->type = $type;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    return $table . $this->by . " " . $this->type;
  }
}


