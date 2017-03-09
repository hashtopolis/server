<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 09.03.17
 * Time: 14:00
 */
class HashtopussyNotificationMattermost extends HashtopussyNotification {
  protected     $receiver;
  public static $name = "Mattermost";
  
  function getTemplateName() {
    return "notifications/mattermost";
  }
  
  function getObjects() {
    return array();
  }
  
  function sendMessage($message) {
    $username = "BOT";
    $data = "payload=" . json_encode(array(
        "username"  =>  $username,
        "text"      =>  $message
      ));
  
    $ch = curl_init($this->receiver);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
  
    return $result;
  }
}

$NOTIFICATIONS['Mattermost'] = new HashtopussyNotificationMattermost();




