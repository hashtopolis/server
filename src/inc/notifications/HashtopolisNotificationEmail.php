<?php

namespace Hashtopolis\inc\notifications;

use Exception;
use Hashtopolis\inc\Util;
use RuntimeException;

class HashtopolisNotificationEmail extends HashtopolisNotification {
  protected string     $receiver;
  public static string $name = "Email";
  
  function getTemplateName(): string {
    return "notifications/email";
  }
  
  /**
   * @throws Exception
   */
  function getObjects(): array {
    $obj = array();
    $obj['username'] = Util::getUsernameById($this->notification->getUserId());
    return $obj;
  }
  
  /**
   * @throws Exception
   */
  function sendMessage($message, $subject): void {
    $message = explode("##########", $message);
    if (Util::isMailConfigured() && !Util::sendMail($this->receiver, $subject, $message[0], $message[1])) {
      throw new RuntimeException("Unable to send notification mail with subject: " . $subject);
    }
  }
}
