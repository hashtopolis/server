<?php

namespace Hashtopolis\inc\utils;

use Exception;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\JwtApiKey;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;

class JwtTokenUtils {
  
  /**
   * @param int $userId
   * @param int $startValid
   * @param int $endValid
   * @return JwtApiKey
   * @throws HttpError
   * @throws Exception
   */
  public static function createKey(int $userId, int $startValid, int $endValid): JwtApiKey {
    $user = Factory::getUserFactory()->get($userId);
    if ($user == null) {
      throw new HttpError("Invalid user ID");
    }

    $key = new JwtApiKey(null, $startValid, $endValid, $userId, 0);
    Factory::getJwtApiKeyFactory()->save($key);
    return $key;
  }
  
  /**
   * @param JwtApiKey $JwtToken
   * @return void
   * @throws HttpForbidden
   * @throws Exception
   */
  public static function deleteKey(JwtApiKey $JwtToken): void {
    $expireTime = $JwtToken->getEndValid();
    if (time() < $expireTime) {
      throw new HttpForbidden("Cannot delete API key before it expires; revoke it instead.");
    }
    Factory::getJwtApiKeyFactory()->delete($JwtToken);

  }
}