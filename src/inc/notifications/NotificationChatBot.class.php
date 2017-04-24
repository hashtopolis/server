<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 09.03.17
 * Time: 14:00
 */
class HashtopussyNotificationChatBot extends HashtopussyNotification {
  protected     $receiver;
  public static $name = "ChatBot";
  
  function getTemplateName() {
    return "notifications/chatbot";
  }
  
  function getObjects() {
    return array();
  }
  
  function sendMessage($message) {
    $username = "Hashtopussy";
    $data = "payload=" . json_encode(array(
          "username" => $username,
          "text" => $message
        )
      );
    
    $ch = curl_init($this->receiver);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
  }
}

$NOTIFICATIONS['ChatBot'] = new HashtopussyNotificationChatBot();




