<?php
use DBA\Chunk;
use DBA\Factory;

use DBA\User;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class changeOwnPasswordHelper extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/changeOwnPassword";
  }

  public static function getAvailableMethods(): array {
    return ['POST'];
  }

  //Since this helper only allows the change of own password, no permissions are needed.
  public function getRequiredPermissions(string $method): array
  {
    return [];
  }

  /**
   * password is the new password that you want to set.
   */
  public function getFormFields(): array 
  {
    return  [
      "password" => ["type" => "str"]
    ];
  }

  public static function getResponse(): array {
    return ["Change password" => "Success"];
  }

  /**
   * Endpoint to set a password of an user.
   */
  public function actionPost($data): object|array|null {
    $user = $this->getCurrentUser();

    /* Set user password if provided */
    UserUtils::changePassword($user, $data["password"]);
    return $this->getResponse();
  }
}  

changeOwnPasswordHelper::register($app);