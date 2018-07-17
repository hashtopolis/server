<?php

class HTMessages extends Exception {
  private $arr = [];

  public function __construct($message = "", $code = 0, Throwable $previous = NULL){
    $this->arr = $message;
  }

  public function getMessage(){
    return implode("\n", $this->arr);
  }

  public function getHTMLMessage(){
    return implode("<br>", $this->arr);
  }
}