<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 09.03.17
 * Time: 14:00
 */
class HashtopussyNotificationExample extends HashtopussyNotification {
  
  function getTemplateName() {
    return "notifications/example";
  }
  
  function getObjects() {
    return array();
  }
  
  function sendMessage($message) {
    file_put_contents(dirname(__FILE__) . "/notification.log", $message, FILE_APPEND);
  }
}