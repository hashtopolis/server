<?php

namespace Hashtopolis\inc;

/**
 * Handles the languages, you can get all available languages and get the replacements
 * for the template files.
 */
class Lang {
  private $language;
  private $array;
  private $available;
  private $langArr;
  private $defaultLanguage = "EN-en";
  
  /**
   * Constructs the language object. Language files need to be included BEFORE this constructor.
   *
   */
  public function __construct() {
    global $LANG;
    
    $availableLanguages = array();
    $dir = scandir(dirname(__FILE__) . "/../lang/");
    foreach ($dir as $entry) {
      if (strlen($entry) == 0) {
        continue;
      }
      else if ($entry[0] == '.') {
        continue;
      }
      include(dirname(__FILE__) . "/../lang/" . $entry);
      $availableLanguages[] = str_replace(".php", "", $entry);
    }
    $this->available = $availableLanguages;
    $this->langArr = $LANG;
    
    // default language setting
    $this->array = $this->langArr[$this->defaultLanguage];
    $this->language = $this->defaultLanguage;
    
    if (isset($_GET['setlang'])) {
      if (in_array($_GET['setlang'], $this->available)) {
        $this->language = $_GET['setlang'];
        $this->array = $this->langArr["" . $this->language];
        setcookie("htp_lang", $this->language, time() + 86400);
      }
    }
    else if (isset($_COOKIE['htp_lang'])) {
      if (in_array($_COOKIE['htp_lang'], $this->available)) {
        $this->language = $_COOKIE['htp_lang'];
        $this->array = $this->langArr[$this->language];
        setcookie("htp_lang", $this->language, time() + 86400);
      }
    }
  }
  
  public function render($text) {
    $matches = array();
    preg_match_all('/(___([a-zA-Z0-9\-_]+?)___)/mis', $text, $matches);
    for ($i = 0; $i < sizeof($matches[0]); $i++) {
      $toReplace = $matches[1][$i];
      $languageKey = str_replace("___", "", $matches[1][$i]);
      $text = str_replace($toReplace, $this->getText($languageKey), $text);
    }
    return $text;
  }
  
  /**
   * Check if a given key is present in the current language. If strict set to false, it uses default language
   * as fallback.
   *
   * @param string $key key to check for existance
   * @param bool $strict set to true if it should only check in the current language and not also in the default language
   * @return bool true if key exists, false if not
   */
  public function isKey($key, $strict = false) {
    if (isset($this->array[$key])) {
      return true;
    }
    else if (!$strict) {
      if (isset($this->langArr[$this->defaultLanguage][$key])) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Get a text in the selected language for a given key from the template.
   *
   * @param string $key identifier in the language file where the text is stored
   * @return string containing the replacement for key
   */
  public function getText($key) {
    if (isset($this->array[$key])) {
      return $this->array[$key];
    }
    else {
      if (isset($this->langArr[$this->defaultLanguage][$key])) {
        return $this->langArr[$this->defaultLanguage][$key];
      }
      return "___" . $key . "___";
    }
  }
  
  /**
   * Get a list of all available languages.
   *
   * @return array list with languages
   */
  public function getAvailableLanguages() {
    return $this->available;
  }
  
  /**
   * Get the number of available languages.
   *
   * @return int number of languages
   */
  public function getNumAvailableLanguages() {
    return sizeof($this->available);
  }
  
  /**
   * Get the written name of a given language name.
   *
   * @param string $name name identifier of language
   * @return string containing the name, false if language is not found
   */
  public function getLanguageName($name) {
    if (isset($this->langArr[$name]['name'])) {
      return $this->langArr[$name]['name'];
    }
    return false;
  }
  
  /**
   * Get the name of the current language
   *
   * @return string lanugage name
   */
  public function getCurrentLanguage() {
    return $this->language;
  }
  
  /**
   * Check if a given language is currently set
   *
   * @param string $lang language to check
   * @return boolean true if language is currently active, false if not
   */
  public function isCurrentLang($lang) {
    if ($lang === $this->language) {
      return true;
    }
    return false;
  }
}




