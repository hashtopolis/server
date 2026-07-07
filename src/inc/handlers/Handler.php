<?php

namespace Hashtopolis\inc\handlers;

interface Handler {
  public function __construct($id);
  
  /**
   * @param string $action
   * @return void
   */
  public function handle(string $action): void;
}