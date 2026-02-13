<?php

namespace Hashtopolis\inc\notifications;

use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DProxyTypes;
use Hashtopolis\inc\SConfig;

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
      "content" => $message
    );
    
    $ch = curl_init($this->receiver);
    
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
      curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, "true");
    }
    
    $make_json = json_encode($json_data);
    $ch = curl_init($this->receiver);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $make_json);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    return curl_exec($ch);
    
  }
}

HashtopolisNotification::add('Discord Webhook', new HashtopolisNotificationDiscordWebhook());
