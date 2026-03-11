<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\JwtApiKey;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;

class JwtTokenUtils {

  public static function createKey(int $userId, int $startValid, int $endValid) {
    $user = Factory::getUserFactory()->get($userId);
    if ($user == null) {
      throw new HttpError("Invalid user ID");
    }

    $key = new JwtApiKey(null, $startValid, $endValid, $userId, 0);
    Factory::getJwtApiKeyFactory()->save($key);
    return $key;
  }

  public static function deleteKey(JwtApiKey $JwtToken) {
    $expireTime = $JwtToken->getEndValid();
    if (time() < $expireTime) {
      throw new HttpForbidden("Not possible to delete Api key when it has not expired yet. revoke it instead");
    }
    Factory::getJwtApiKeyFactory()->delete($JwtToken);

  }
}