<?php

class CSRF {
  const CSRF_SECRET = "csrfSecret";
  
  public static function init() {
    global $OBJECTS;
    
    if (!isset($_SESSION[CSRF::CSRF_SECRET])) {
      // generate a secret
      $_SESSION[CSRF::CSRF_SECRET] = Util::randomString(40);
    }
    
    // set a token
    $key = Util::randomString(30);
    $OBJECTS['csrf'] = $key . ":" . base64_encode(hash("sha256", $key . $_SESSION[CSRF::CSRF_SECRET] . $key, true));
  }
  
  public static function check($csrf) {
    $csrf = explode(":", $csrf);
    if (sizeof($csrf) != 2) {
      UI::addMessage(UI::ERROR, "Invalid form submission!");
      return false;
    }
    else if (!isset($_SESSION[CSRF::CSRF_SECRET])) {
      UI::addMessage(UI::ERROR, "Invalid form submission!");
      return false;
    }
    
    $key = $csrf[0];
    $check = base64_encode(hash("sha256", $key . $_SESSION[CSRF::CSRF_SECRET] . $key, true));
    if ($check == $csrf[1]) {
      return true;
    }
    UI::addMessage(UI::ERROR, "Invalid form submission!");
    return false;
  }
}



