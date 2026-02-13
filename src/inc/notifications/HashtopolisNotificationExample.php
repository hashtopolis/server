<?php

namespace Hashtopolis\inc\notifications;

class HashtopolisNotificationExample extends HashtopolisNotification {
  protected     $receiver;
  public static $name = "Example";
  
  function getTemplateName() {
    return "notifications/example";
  }
  
  function getObjects() {
    return array();
  }
  
  function sendMessage($message, $subject = "") {
    file_put_contents(dirname(__FILE__) . "/notification.log", "MSG TO " . $this->receiver . ": " . $message . "\n", FILE_APPEND);
  }
}

HashtopolisNotification::add('Example', new HashtopolisNotificationExample());

