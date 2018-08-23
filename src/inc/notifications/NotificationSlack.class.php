<?php

class HashtopolisNotificationSlack extends HashtopolisNotification {
  protected     $receiver;
  public static $name = "Slack";

  function getTemplateName() {
    return "notifications/slack";
  }

  function getObjects() {
    return array();
  }

  function sendMessage($message, $subject = "") {
    $data = json_encode(array("text" => $message));

    $ch = curl_init($this->receiver);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
  }
}

HashtopolisNotification::add('Slack', new HashtopolisNotificationSlack());

