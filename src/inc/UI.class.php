<?php

class UI {
  public static function printError($level, $message) {
    global $OBJECTS;
    
    $TEMPLATE = new Template("errors/error");
    $OBJECTS['message'] = $message;
    $OBJECTS['level'] = $level;
    $OBJECTS['pageTitle'] = "Error";
    echo $TEMPLATE->render($OBJECTS);
    die();
  }
  
  public static function permissionError() {
    global $OBJECTS;
    
    $TEMPLATE = new Template("errors/restricted");
    $OBJECTS['pageTitle'] = "Restricted";
    echo $TEMPLATE->render($OBJECTS);
    die();
  }
  
  public static function printFatalError($message) {
    echo $message;
    die();
  }
  
  public static function addMessage($type, $message) {
    global $OBJECTS;
    
    $OBJECTS['messages'][] = new DataSet(array('type' => $type, 'message' => $message));
  }
  
  public static function getNumMessages($type = "ALL") {
    global $OBJECTS;
    
    $count = 0;
    foreach ($OBJECTS['messages'] as $message) {
      /** @var $message DataSet */
      if ($message->getVal('type') == $type || $type == "ALL") {
        $count++;
      }
    }
    return $count;
  }
  
  public static function setForward($url, $delay) {
    global $OBJECTS;
    
    $OBJECTS['autorefresh'] = $delay;
    $OBJECTS['autorefreshUrl'] = $url;
  }
  
  const ERROR   = "danger";
  const SUCCESS = "success";
  const WARN    = "warning";
}