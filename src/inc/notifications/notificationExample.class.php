<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 09.03.17
 * Time: 14:00
 */
class HashtopussyNotificationExample extends HashtopussyNotification {
  private       $receiver;
  public static $name = "Example";
  
  function getTemplateName() {
    return "notifications/example";
  }
  
  function getObjects() {
    return array();
  }
  
  function sendMessage($message) {
    file_put_contents(dirname(__FILE__) . "/notification.log", "MSG TO " . $this->receiver . ": " . $message, FILE_APPEND);
  }
}

$NOTIFICATIONS['Example'] = new HashtopussyNotificationExample();