<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\utils\UserUtils;

class SetUserPasswordHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/setUserPassword";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [User::PERM_UPDATE];
  }
  
  /**
   * userId is the id of the user of which you want to change the password.
   * password is the new password that you want to set.
   */
  public function getFormFields(): array {
    return [
      User::USER_ID => ["type" => "int"],
      "password" => ["type" => "str"]
    ];
  }
  
  public static function getResponse(): array {
    return ["Set password" => "Success"];
  }
  
  /**
   * Endpoint to set a password of an user.
   * @throws HTException
   */
  public function actionPost($data): object|array|null {
    $user = self::getUser($data[User::USER_ID]);
    
    /* Set user password if provided */
    UserUtils::setPassword(
      $user->getId(),
      $data["password"],
      $this->getCurrentUser()
    );
    return $this->getResponse();
  }
}
