<?php

namespace Hashtopolis\inc;

use Hashtopolis\inc\templating\Template;

class UI {
  private static $objects = [];
  
  public static function printError($level, $message): void {
    Template::loadInstance("errors/error");
    UI::add('message', $message);
    UI::add('level', $level);
    UI::add('pageTitle', "Error");
    echo Template::getInstance()->render(UI::getObjects());
    die();
  }
  
  public static function add($key, $value) {
    self::$objects[$key] = $value;
  }
  
  public static function get($key) {
    if (!isset(self::$objects[$key])) {
      return false;
    }
    return self::$objects[$key];
  }
  
  public static function getObjects(): array {
    return self::$objects;
  }
  
  public static function permissionError(): void {
    Template::loadInstance("errors/restricted");
    UI::add('pageTitle', "Restricted");
    echo Template::getInstance()->render(UI::getObjects());
    die();
  }
  
  public static function printFatalError($message): void {
    echo $message;
    die();
  }
  
  public static function addMessage($type, $message): void {
    self::$objects['messages'][] = new DataSet(['type' => $type, 'message' => $message]);
  }
  
  public static function getNumMessages($type = "ALL"): int {
    $count = 0;
    foreach (self::$objects['messages'] as $message) {
      /** @var $message DataSet */
      if ($message->getVal('type') == $type || $type == "ALL") {
        $count++;
      }
    }
    return $count;
  }
  
  public static function setForward($url, $delay): void {
    UI::add('autorefresh', $delay);
    UI::add('autorefreshUrl', $url);
  }
  
  const ERROR   = "danger";
  const SUCCESS = "success";
  const WARN    = "warning";
}