<?php
use DBA\Factory;
use DBA\OrderFilter;
use DBA\QueryFilter;

use DBA\CrackerBinary;
use DBA\Hashlist;
use DBA\Supertask;
use DBA\Task;
use DBA\TaskWrapper;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");
require_once(dirname(__FILE__) . "/../model/hashlists.routes.php");

class CreateSuperHashlistHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/createSuperHashlist";
  }

  public static function getAvailableMethods(): array {
    return ['POST'];
  }

  public function getRequiredPermissions(string $method): array
  {
    return [Hashlist::PERM_CREATE, Hashlist::PERM_READ];
  }

  public function getFormFields(): array 
  {
    return  [
      "hashlistIds" => ["type" => "array", "subtype" => "int"],
      "name" => ["type" => "str"],
    ];
  }

  public function actionPost($data): object|null {
    /* Validate incoming hashlists */
    $hashlistIds = [];
    foreach($data["hashlistIds"] as $hashlistId) {
      array_push($hashlistIds, self::getHashlist($hashlistId)->getId());
    }

    /* Execute helper */
    HashlistUtils::createSuperhashlist($hashlistIds, $data["name"], $this->getCurrentUser());

    /* Quick to retrieve newly created SuperHashlist (which is of type Hashlist) */
    $qFs = [
       new QueryFilter(Hashlist::FORMAT, DHashlistFormat::SUPERHASHLIST, "=")
    ];
    $oF = new OrderFilter(Hashlist::HASHLIST_ID, "DESC");       
    $objects = self::getModelFactory(Hashlist::class)->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
    assert(count($objects) > 0);

    /* TODO: Make it bit more transparant and auto-expands hashlists by default */
    return $objects[0];

  }
}  

CreateSuperHashlistHelperAPI::register($app);