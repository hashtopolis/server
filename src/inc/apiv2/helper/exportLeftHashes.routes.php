<?php

use DBA\File;
use DBA\Hash;
use DBA\Hashlist;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class ExportLeftHashesHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/exportLeftHashes";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Hashlist::PERM_READ, Hash::PERM_READ, File::PERM_CREATE];
  }
  
  /**
   * hashlistId is the id of the hashlist where you want to export the uncracked hashes of.
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
   * Endpoint to export uncracked hashes of a hashlist.
   * @throws HTException
   */
  public function actionPost($data): object|array|null {
    $hashlist = self::getHashlist($data[Hashlist::HASHLIST_ID]);
    
    return HashlistUtils::leftlist($hashlist->getId(), $this->getCurrentUser());
  }
}

ExportLeftHashesHelperAPI::register($app);