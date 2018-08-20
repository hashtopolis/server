<?php

class UI {
  private static $objects = [];

  public static function printError($level, $message) {
    $TEMPLATE = new Template("errors/error");
    UI::add('message', $message);
    UI::add('level', $level);
    UI::add('pageTitle', "Error");
    echo $TEMPLATE->render(UI::getObjects());
    die();
  }

  public static function add($key, $value){
    self::$objects[$key] = $value;
  }

  public static function get($key){
    if(!isset(self::$objects[$key])){
      return false;
    }
    return self::$objects[$key];
  }

  public static function getObjects(){
    return self::$objects;
  }
  
  public static function permissionError() {
    $TEMPLATE = new Template("errors/restricted");
    UI::add('pageTitle', "Restricted");
    echo $TEMPLATE->render(UI::getObjects());
    die();
  }
  
  public static function printFatalError($message) {
    echo $message;
    die();
  }
  
  public static function addMessage($type, $message) {
    UI::get('messages')[] = new DataSet(['type' => $type, 'message' => $message]);
  }
  
  public static function getNumMessages($type = "ALL") {
    $count = 0;
    foreach (UI::get('messages') as $message) {
      /** @var $message DataSet */
      if ($message->getVal('type') == $type || $type == "ALL") {
        $count++;
      }
    }
    return $count;
  }
  
  public static function setForward($url, $delay) {
    UI::add('autorefresh', $delay);
    UI::add('autorefreshUrl', $url);
  }
  
  const ERROR   = "danger";
  const SUCCESS = "success";
  const WARN    = "warning";
}