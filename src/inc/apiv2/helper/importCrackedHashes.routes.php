<?php

use DBA\Hash;
use DBA\Hashlist;

use Middlewares\Utils\HttpErrorException;

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
      "sourceType" => ['type' => 'str'],
      "sourceData" => ['type' => 'str'],
      "separator" => ['type' => 'str'],
      "overwrite" => ['type' => 'int'],
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
   * @throws HttpError
   */
  public function actionPost($data): object|array|null {
    $hashlist = self::getHashlist($data[Hashlist::HASHLIST_ID]);
    
    // Cast to processZap compatible upload format
    $dummyPost = [];
    switch ($data["sourceType"]) {
      case "paste":
        $dummyPost["hashfield"] = base64_decode($data["sourceData"]);
        break;
      case "import":
        $dummyPost["importfile"] = $data["sourceData"];
        break;
      case "url":
        $dummyPost["url"] = $data["sourceData"];
        break;
      default:
        // TODO: Choice validation are model based checks
        throw new HttpErrorException("sourceType value '" . $data["sourceType"] . "' is not supported (choices paste, import, url");
    }

    if ($data["sourceType"] == "paste") {
      if (strlen($data["sourceData"]) == 0) {
        throw new HttpError("sourceType=paste, requires sourceData to be non-empty");
      }
      else if ($dummyPost["hashfield"] == false) {
        throw new HttpError("sourceData not valid base64 encoding");
      }
    }
    
    $result = HashlistUtils::processZap($hashlist->getId(), $data["separator"], $data["sourceType"], $dummyPost, [], $this->getCurrentUser(), (isset($data["overwrite"]) && intval($data["overwrite"]) == 1) ? true : false);
    
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

use Slim\App;
/** @var App $app */
ImportCrackedHashesHelperAPI::register($app);