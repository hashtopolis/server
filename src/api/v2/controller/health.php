<?php

namespace APIv2;

require_once __DIR__ . '/../dto/healthDTO.php';

class HealthController {

  /**
   * Get the current health status of the Hashtopolis v2 service.
   * 
   * @url GET
   * @access public
   * 
   * @return GetHealth 
   */
  public function getHealth() {
    return GetHealth::create('UP');
  }
}