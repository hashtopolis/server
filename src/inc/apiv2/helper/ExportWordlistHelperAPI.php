<?php

namespace Hashtopolis\inc\apiv2\helper;

use Hashtopolis\inc\utils\HashlistUtils;
use Hashtopolis\dba\models\File;
use Hashtopolis\dba\models\Hash;
use Hashtopolis\dba\models\Hashlist;
use Hashtopolis\inc\apiv2\common\AbstractHelperAPI;
use Hashtopolis\inc\HTException;

class ExportWordlistHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/exportWordlist";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Hashlist::PERM_READ, Hash::PERM_READ, File::PERM_CREATE];
  }
  
  /**
   * hashlistId is the Id of the hashlist where you want to export the wordlist of.
   */
  public function getFormFields(): array {
    return [
      Hashlist::HASHLIST_ID => ["type" => "int"],
    ];
  }
  
  public static function getResponse(): string {
    return "File";
  }
  
  /**
   * Endpoint to export a wordlist of the cracked hashes inside a hashlist.
   * @throws HTException
   */
  public function actionPost($data): object|array|null {
    $hashlist = self::getHashlist($data[Hashlist::HASHLIST_ID]);
    
    $arr = HashlistUtils::createWordlists($hashlist->getId(), $this->getCurrentUser());
    
    return $arr[2];
  }
}
