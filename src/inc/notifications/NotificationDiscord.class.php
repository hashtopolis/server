<?php

class HashtopolisNotificationDiscordWebhook extends HashtopolisNotification {
  protected     $receiver;
  public static $name = "Discord Webhook";
  
  function getTemplateName() {
    return "notifications/discord";
  }
  
  function getObjects() {
    return array();
  }
  
  function sendMessage($message, $subject = "") {
    $json_data = array(
      'content'=>"$message"
  );

    $make_json = json_encode($json_data);
    $ch = curl_init($this->receiver);
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt( $ch, CURLOPT_POST, 1);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $make_json);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec( $ch );

    return $response;

  }
}

HashtopolisNotification::add('Discord Webhook', new HashtopolisNotificationDiscordWebhook());

