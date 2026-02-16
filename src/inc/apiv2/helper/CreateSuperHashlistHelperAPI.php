<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\inc\defines\DHashlistFormat;
use Hashtopolis\inc\utils\HashlistUtils;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\OrderFilter;
use Hashtopolis\dba\QueryFilter;

use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\HTException;

class CreateSuperHashlistHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/createSuperHashlist";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Hashlist::PERM_CREATE, Hashlist::PERM_READ];
  }
  
  /**
   * HashlistIds is an array of hashlist ids of the hashlists that have to be combined into a superHashlist.
   * Name is the name of the newly created superHashlist.
   */
  public function getFormFields(): array {
    return [
      "hashlistIds" => ["type" => "array", "subtype" => "int"],
      "name" => ["type" => "str"],
    ];
  }
  
  public static function getResponse(): string {
    return "Hashlist";
  }
  
  /**
   * Endpoint to create a super hashlist from multiple hashlists
   * @throws HTException
   */
  public function actionPost($data): object|array|null {
    /* Validate incoming hashlists */
    $hashlistIds = [];
    foreach ($data["hashlistIds"] as $hashlistId) {
      $hashlistIds[] = self::getHashlist($hashlistId)->getId();
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
    
    /* TODO: Make it bit more transparent and auto-expands hashlists by default */
    return $objects[0];
    
  }
}
