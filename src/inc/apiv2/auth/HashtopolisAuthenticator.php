<?php


/* Authentication middleware for token retrival */

namespace Hashtopolis\inc\apiv2\auth;
use DLogEntry;
use DLogEntryIssuer;
use Hashtopolis\inc\Encryption;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\User;
use Hashtopolis\dba\QueryFilter;
use Tuupola\Middleware\HttpBasicAuthentication\AuthenticatorInterface;
use Hashtopolis\inc\Util;

class HashtopolisAuthenticator implements AuthenticatorInterface {
  public function __invoke(array $arguments): bool {
    $username = $arguments["user"];
    $password = $arguments["password"];
    
    $filter = new QueryFilter(User::USERNAME, $username, "=");
    
    $user = Factory::getUserFactory()->filter([Factory::FILTER => $filter], true);
    if ($user === null) {
      return false;
    }
    
    if ($user->getIsValid() != 1) {
      return false;
    }
    else if (!Encryption::passwordVerify($password, $user->getPasswordSalt(), $user->getPasswordHash())) {
      Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::WARN, "Failed login attempt due to wrong password!");
      return false;
    }
    Factory::getUserFactory()->set($user, User::LAST_LOGIN_DATE, time());
    return true;
  }
}