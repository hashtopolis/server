<?php

class HashtopussyNotificationEmail extends HashtopussyNotification {
  protected     $receiver;
  public static $name = "Email";
  
  function getTemplateName() {
    return "notifications/email";
  }
  
  function getObjects() {
    $obj = array();
    $obj['username'] = Util::getUsernameById($this->notification->getUserId());
    return $obj;
  }
  
  function sendMessage($message, $subject) {
    Util::sendMail($this->receiver, $subject, $message);
  }
}

$NOTIFICATIONS['Email'] = new HashtopussyNotificationEmail();

