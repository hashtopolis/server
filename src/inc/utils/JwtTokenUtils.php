<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\JwtApiKey;
use Hashtopolis\inc\apiv2\error\HttpError;

class JwtTokenUtils {

  public static function createKey($userId, $startValid, $endValid) {
    $user = Factory::getUserFactory()->get($userId);
    if ($user == null) {
      throw new HttpError("Invalid user ID");
    }

    $key = new JwtApiKey(null, $startValid, $endValid, $userId, 0);
    Factory::getJwtApiKeyFactory()->save($key);
    return $key;
  }
}