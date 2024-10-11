<?php

namespace DBA;

class LimitFilter extends Limit {
  private $limit;
  private $offset;
  
  function __construct($limit, $offset=null) {
   // Enforce that limit is an integer
   if (!is_numeric($limit) || intval($limit) < 0) {
    throw new \InvalidArgumentException("Limit must be a non-negative integer.");
  }
  
  // Enforce that offset, if provided, is an integer
  if ($offset !== null && (!is_numeric($offset) || intval($offset) < 0)) {
    throw new \InvalidArgumentException("Offset must be a non-negative integer.");
  }
  
  // Cast the inputs to ensure they are integers
  $this->limit = intval($limit);
  $this->offset = $offset !== null ? intval($offset) : null;
}
  
  function getQueryString() {
    $queryString = $this->limit;
    if ($this->offset != null) {
      $queryString = $queryString . " OFFSET " . $this->offset; 
    }
    return $queryString;
  }
}


