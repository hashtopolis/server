<?php

namespace Hashtopolis\inc;
/**
 * This class is a small workaround to make it possible for the menu to request which page
 * is active through a object passed to the template system.
 */
class Menu {
  private $active;
  
  private static $instance = null;
  
  public static function get($name = "") {
    if (self::$instance == null) {
      self::$instance = new Menu($name);
    }
    return self::$instance;
  }
  
  /**
   * Construct a new Menu class.
   *
   * @param string $name name of the identifier in the menu to set active
   */
  private function __construct($name = "") {
    $this->active = $name;
  }
  
  /**
   * Set which menu point should be active
   *
   * @param string $name identifier used on checking for active
   */
  public function setActive($name) {
    $this->active = $name;
  }
  
  /**
   * Request for a identifier if it is active or no.
   *
   * @param string $name identifier to check
   * @param string $classonly when it's used in a dropdown provide the name of the dropdown object here
   * @return string with " class='active'" if is active or "" if not
   */
  public function isActive($name, $classonly = "") {
    if ($classonly == 'classonly') {
      $split = explode("_", $this->active);
      if ($split[0] == $name) {
        return " active";
      }
      return "";
    }
    if ($name == $this->active) {
      return " active";
    }
    return "";
  }
}








