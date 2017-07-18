<?php

interface Handler {
  public function __construct($id);
  
  public function handle($action);
}