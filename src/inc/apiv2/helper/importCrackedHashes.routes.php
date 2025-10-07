<?php

use DBA\Hash;
use DBA\Hashlist;

require_once(dirname(__FILE__) . "/../common/AbstractHelperAPI.class.php");

class ImportCrackedHashesHelperAPI extends AbstractHelperAPI {
  public static function getBaseUri(): string {
    return "/api/v2/helper/importCrackedHashes";
  }
  
  public static function getAvailableMethods(): array {
    return ['POST'];
  }
  
  public function getRequiredPermissions(string $method): array {
    return [Hashlist::PERM_UPDATE, Hash::PERM_UPDATE];
  }
  
  /**
   * HashlistId is the Id of the hashlist where you want to import the cracked hashes into.
   * SourceData is the cracked hashes you want to import.
   * Seperator is the seperator that has been used for the salt in the hashes.
   */
  public function getFormFields(): array {
    return [
      Hashlist::HASHLIST_ID => ["type" => "int"],
      "sourceData" => ['type' => 'str'],
      "separator" => ['type' => 'str'],
    ];
  }
  
  public static function getResponse(): array {
    return [
      "totalLines" => 100,
      "newCracked" => 5,
      "alreadyCracked" => 2,
      "invalid" => 1,
      "notFound" => 1,
      "processTime" => 60,
      "tooLongPlaintexts" => 4,
    ];
  }
  
  /**
   * Endpoint to import cracked hashes into a hashlist.
   * @throws HTException
   */
  public function actionPost($data): object|array|null {
    $hashlist = self::getHashlist($data[Hashlist::HASHLIST_ID]);
    
    $importData = base64_decode($data["sourceData"]);
    
    $result = HashlistUtils::processZap($hashlist->getId(), $data["separator"], "paste", ["hashfield" => $importData], [], $this->getCurrentUser());
    
    return [
      "totalLines" => $result[0],
      "newCracked" => $result[1],
      "alreadyCracked" => $result[2],
      "invalid" => $result[3],
      "notFound" => $result[4],
      "processTime" => $result[5],
      "tooLongPlaintexts" => $result[6],
    ];
  }
}

ImportCrackedHashesHelperAPI::register($app);