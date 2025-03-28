<?php
use DBA\Chunk;
use DBA\Factory;

use DBA\User;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class SetUserPasswordHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/setUserPassword";
  }

  public static function getAvailableMethods(): array {
    return ['POST'];
  }

  public function getRequiredPermissions(string $method): array
  {
    return [User::PERM_UPDATE];
  }

  public function getFormFields(): array 
  {
    return  [
      User::USER_ID => ["type" => "int"],
      "password" => ["type" => "str"]
    ];
  }

  public function actionPost($data): object|array|null {
    $user = self::getUser($data[User::USER_ID]);

    /* Set user password if provided */
    UserUtils::setPassword(
      $user->getId(),
      $data["password"],
      $this->getCurrentUser()
    );
    return null;
  }
}  

SetUserPasswordHelperAPI::register($app);