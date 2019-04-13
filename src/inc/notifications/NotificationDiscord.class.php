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
    $username = APP_NAME;
    $data = json_encode(array(
          "content" => $message
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

HashtopolisNotification::add('Discord Webhook', new HashtopolisNotificationDiscordWebhook());

