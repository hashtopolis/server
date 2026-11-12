<?php
 
class HashtopolisNotificationTeamsWebhook extends HashtopolisNotification {
  protected $receiver;
  public static $name = "Teams Webhook";
 
  function getTemplateName() {
    return "notifications/Teams";
  }
 
  function getObjects() {
    return array();
  }
 
  function sendMessage($message, $subject = "") {
    $json_data = array(
      "body" => array(
        "content" => $message,
        "subject" => $subject,
        "attachments" => array() // Ensures Power Automate receives a valid array
      )
    );
 
    $make_json = json_encode($json_data);
 
    $ch = curl_init($this->receiver);
 
    // Proxy configuration if enabled
    if (SConfig::getInstance()->getVal(DConfig::NOTIFICATIONS_PROXY_ENABLE) == 1) {
      curl_setopt($ch, CURLOPT_PROXY, SConfig::getInstance()->getVal(DConfig::NOTIFICATIONS_PROXY_SERVER));
      curl_setopt($ch, CURLOPT_PROXYPORT, SConfig::getInstance()->getVal(DConfig::NOTIFICATIONS_PROXY_PORT));
      $type = CURLPROXY_HTTP;
      switch (SConfig::getInstance()->getVal(DConfig::NOTIFICATIONS_PROXY_TYPE)) {
        case DProxyTypes::HTTPS:
          $type = CURLPROXY_HTTPS;
          break;
        case DProxyTypes::SOCKS4:
          $type = CURLPROXY_SOCKS4;
          break;
        case DProxyTypes::SOCKS5:
          $type = CURLPROXY_SOCKS5;
          break;
      }
      curl_setopt($ch, CURLOPT_PROXYTYPE, $type);
      curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
    }
 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $make_json);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 
    return curl_exec($ch);
  }
}
 
HashtopolisNotification::add('Teams Webhook', new HashtopolisNotificationTeamsWebhook());