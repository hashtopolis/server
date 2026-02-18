<?php

use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DProxyTypes;

/**
 * @deprecated
 */
class ConfigTest extends HashtopolisTest {
  protected $minVersion = "0.7.0";
  protected $maxVersion = "master";
  protected $runType    = HashtopolisTest::RUN_FAST;
  
  public function init($version) {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Initializing " . $this->getTestName() . "...");
    parent::init($version);
  }
  
  public function run() {
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, "Running " . $this->getTestName() . "...");
    $this->testListConfig(DConfig::getConstants());
    $this->testSetConfig(DConfig::HASHLIST_ALIAS, "__HL__");
    $this->testGetConfig(DConfig::HASHLIST_ALIAS, "__HL__");
    $this->testSetConfig('unknown-config', "blub", false, false);
    $this->testGetConfig('unknown-config', '', false);
    $this->testSetConfig('unknown-config', "blub", true, true);
    $this->testGetConfig('unknown-config', 'blub');
    
    $this->testSetConfig(DConfig::CONTACT_EMAIL, "this-is-not-a-mail", false, false);
    $this->testSetConfig(DConfig::NOTIFICATIONS_PROXY_ENABLE, "100", false, false);
    $this->testSetConfig(DConfig::STATUS_TIMER, "no-number", false, false);
    $this->testSetConfig(DConfig::NOTIFICATIONS_PROXY_TYPE, DProxyTypes::HTTPS);
    $this->testSetConfig(DConfig::NOTIFICATIONS_PROXY_TYPE, 'proto', false, false);
    HashtopolisTestFramework::log(HashtopolisTestFramework::LOG_INFO, $this->getTestName() . " completed");
  }
  
  private function testSetConfig($item, $value, $force = false, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "config",
      "request" => "setConfig",
      "configItem" => $item,
      "value" => $value,
      "force" => $force,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("ConfigTest:testSetConfig($item,$value,$force,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("ConfigTest:testSetConfig($item,$value,$force,$assert)", "Response does not match assert");
    }
    else {
      $this->testSuccess("ConfigTest:testSetConfig($item,$value,$force,$assert)");
    }
  }
  
  private function testGetConfig($item, $value, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "config",
      "request" => "getConfig",
      "configItem" => $item,
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("ConfigTest:testGetConfig($item,$value,$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("ConfigTest:testGetConfig($item,$value,$assert)", "Response does not match assert");
    }
    else {
      if (!$assert) {
        $this->testSuccess("ConfigTest:testGetConfig($item,$value,$assert)");
        return;
      }
      else if ($response['value'] != $value) {
        $this->testFailed("ConfigTest:testGetConfig($item,$value,$assert)", "Response OK, but wrong value");
        return;
      }
      $this->testSuccess("ConfigTest:testGetConfig($item,$value,$assert)");
    }
  }
  
  private function testListConfig($configs, $assert = true) {
    $response = HashtopolisTestFramework::doRequest([
      "section" => "config",
      "request" => "listConfig",
      "accessKey" => "mykey"
    ], HashtopolisTestFramework::REQUEST_UAPI
    );
    if ($response === false) {
      $this->testFailed("ConfigTest:testListConfig([" . implode(",", $configs) . "],$assert)", "Empty response");
    }
    else if (!$this->validState($response['response'], $assert)) {
      $this->testFailed("ConfigTest:testListConfig([" . implode(",", $configs) . "],$assert)", "Response does not match assert");
    }
    else {
      if (!$assert) {
        $this->testSuccess("ConfigTest:testListConfig([" . implode(",", $configs) . "],$assert)");
        return;
      }
      $items = [];
      foreach ($response['items'] as $item) {
        $items[$item['item']] = true;
      }
      foreach ($configs as $c) {
        if ($c == 'jeSuisHashtopussy' || $c == 'donateOff') {
          continue;
        }
        if (!isset($items[$c])) {
          $this->testFailed("ConfigTest:testListConfig([" . implode(",", $configs) . "],$assert)", "Response OK, but item $c missing");
          return;
        }
      }
      $this->testSuccess("ConfigTest:testListConfig([" . implode(",", $configs) . "],$assert)");
    }
  }
  
  public function getTestName() {
    return "Config Test";
  }
}

HashtopolisTestFramework::register(new ConfigTest());