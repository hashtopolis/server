<?php

namespace Hashtopolis\inc\notifications;

class HashtopolisNotificationExample extends HashtopolisNotification {
  protected string     $receiver;
  public static string $name = "Example";
  
  function getTemplateName(): string {
    return "notifications/example";
  }
  
  function getObjects(): array {
    return array();
  }
  
  function sendMessage($message, $subject = ""): void {
    file_put_contents(dirname(__FILE__) . "/notification.log", "MSG TO " . $this->receiver . ": " . $message . "\n", FILE_APPEND);
  }
}

