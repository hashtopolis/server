<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 18.11.16
 * Time: 20:22
 */
interface Handler {
  public function __construct($id);
  
  public function handle($action);
}