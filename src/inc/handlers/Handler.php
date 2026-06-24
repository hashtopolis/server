<?php

namespace Hashtopolis\inc\handlers;

interface Handler {
  public function __construct($id);
  
  public function handle($action);
}