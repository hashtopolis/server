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
  
  public function getFormFields(): array {
    return [
      Hashlist::HASHLIST_ID => ["type" => "int"],
      "sourceData" => ['type' => 'str'],
      "separator" => ['type' => 'str'],
    ];
  }
  
  public function actionPost($data): array|null {
    $hashlist = self::getHashlist($data[Hashlist::HASHLIST_ID]);
    
    $result = HashlistUtils::processZap($hashlist->getId(), $data["separator"], "paste", ["hashfield" => $data["sourceData"]], [], $this->getCurrentUser());
    
    # TODO: Check how to handle custom return messages that are not object, probably we want that to be in some kind of standardized form.
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