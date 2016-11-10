<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 10.11.16
 * Time: 14:46
 */
class UI {
  public static function printError($level, $message) {
    $OBJECTS = array();
    $TEMPLATE = new Template("error");
    $OBJECTS['message'] = $message;
    $OBJECTS['level'] = $level;
    echo $TEMPLATE->render($OBJECTS);
    die();
  }
}