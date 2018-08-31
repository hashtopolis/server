<?php

class HashtopolisNotificationTelegram extends HashtopolisNotification {
  protected     $receiver;
  public static $name = "Telegram";

  function getTemplateName() {
    return "notifications/telegram";
  }

  function getObjects() {
    return array();
  }

  function sendMessage($message, $subject = "") {
    $botToken = SConfig::getInstance()->getVal(DConfig::TELEGRAM_BOT_TOKEN);
    $data = array(
      "chat_id" => $this->receiver,
      "text" => $message
    );

    $ch = curl_init("https://api.telegram.org/bot{$botToken}/sendmessage");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
  }
}

HashtopolisNotification::add('Telegram', new HashtopolisNotificationTelegram());

