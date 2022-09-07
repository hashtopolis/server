<?php

namespace APIv2;

use DBA\ApiKey,
  DBA\Factory,
  DBA\QueryFilter;

class Authentication {

  private static $instance;

  private $authenticated;
  private $user;

  public static function instance() {
    if (self::$instance == null) {
      self::$instance = new Authentication();
    }
    return self::$instance;
  }

  /**
   * Try to authenticate using given access key.
   *
   * @param string $key the key to try and authenticate with
   * @return bool true if the key exists and is still valid, otherwise false
   */
  public function authenticateWithApiKey($key) {
    $qF = new QueryFilter(ApiKey::ACCESS_KEY, $key, "=");
    $apiKey = Factory::getApiKeyFactory()->filter([Factory::FILTER => $qF], true);

    if (!$this->isApiKeyValid($apiKey)) {
      return false;
    }
    $this->user = Factory::getUserFactory()->get($apiKey->getUserId());

    $apiKey->setAccessCount($apiKey->getAccessCount() + 1);
    Factory::getApiKeyFactory()->update($apiKey);

    return $this->authenticated = true;
  }

  private function isApiKeyValid($apiKey) {
    if ($apiKey == null) {
      return false; // invalid access key
    }
    if ($apiKey->getStartValid() > time() || $apiKey->getEndValid() < time()) {
      return false; // expired access key
    }
    return true;
  }

  public function isAuthenticated() {
    return $this->authenticated;
  }

  public function user() {
    return $this->user;
  }
}