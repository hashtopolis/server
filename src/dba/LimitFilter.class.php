<?php

namespace DBA;

class LimitFilter extends Limit {
  private $limit;
  private $offset;
  /**
   * @var AbstractModelFactory
   */
  function __construct($limit, $offset=null) {
    $this->limit = $limit;
    $this->offset = $offset;
  }
  
  function getQueryString() {
    $queryString = $this->limit;
    if ($this->offset != null) {
      $queryString = $queryString . " OFFSET " . $this->offset; 
    }
    return $queryString;
  }
}


