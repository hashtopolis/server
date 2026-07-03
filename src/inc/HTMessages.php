<?php

namespace Hashtopolis\inc;

use Exception;
use Throwable;

class HTMessages extends Exception {
  private $arr = [];
  
  public function __construct($message = "", $code = 0, ?Throwable $previous = NULL) {
    $this->arr = $message;
    parent::__construct(implode("\n", $this->arr), $code, $previous);
  }
  
  public function getHTMLMessage() {
    return implode("<br>", $this->arr);
  }
}