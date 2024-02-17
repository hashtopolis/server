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

  public function getRequiredPermissions(string $method): array
  {
    return [Hashlist::PERM_READ, Hash::PERM_READ, File::PERM_CREATE];
  }

  public function getFormFields(): array 
  {
    return  [
      Hashlist::HASHLIST_ID => ["type" => "int"],
    ];
  }

  public function actionPost($data): array|null {
    $hashlist = self::getHashlist($data[Hashlist::HASHLIST_ID]);
    
    $file = HashlistUtils::leftlist($hashlist->getId(), $this->getCurrentUser());
    
    return $this->object2Array($file);
  }
}

ExportLeftHashesHelperAPI::register($app);