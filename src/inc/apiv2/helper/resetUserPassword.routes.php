<?php

use DBA\User;
use \Psr\Http\Message\ServerRequestInterface;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class ResetUserPasswordHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/resetUserPassword";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [];
  }
  
  public function preCommon(ServerRequestInterface $request): void {
    // nothing, there is no user for this request as it is an unauthenticated request
  }
  
  public function getFormFields(): array {
    return [
      User::EMAIL => ["type" => "str"],
      User::USERNAME => ["type" => "str"],
    ];
  }
  
  public function actionPost($data): array|null {
    UserUtils::userForgotPassword($data[User::USERNAME], $data[User::EMAIL]);
    
    # TODO: Check how to handle custom return messages that are not object, probably we want that to be in some kind of standardized form.
    return ["reset" => "success"];
  }
}

ResetUserPasswordHelperAPI::register($app);