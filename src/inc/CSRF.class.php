<?php

class CSRF {
  public static function init() {
    if (!isset($_SESSION[StartupConfig::getInstance()->getPepper(3)])) {
      // generate a secret
      $_SESSION[StartupConfig::getInstance()->getPepper(3)] = Util::randomString(40);
    }
    
    // set a token
    $key = Util::randomString(30);
    UI::add('csrf', $key . ":" . base64_encode(hash("sha256", $key . $_SESSION[StartupConfig::getInstance()->getPepper(3)] . $key, true)));
  }
  
  public static function check($csrf) {
    $csrf = explode(":", $csrf);
    if (sizeof($csrf) != 2) {
      UI::addMessage(UI::ERROR, "Invalid form submission!");
      return false;
    }
    else if (!isset($_SESSION[StartupConfig::getInstance()->getPepper(3)])) {
      UI::addMessage(UI::ERROR, "Invalid form submission!");
      return false;
    }
    
    $key = $csrf[0];
    $check = base64_encode(hash("sha256", $key . $_SESSION[StartupConfig::getInstance()->getPepper(3)] . $key, true));
    if ($check == $csrf[1]) {
      return true;
    }
    UI::addMessage(UI::ERROR, "Invalid form submission!");
    return false;
  }
}



