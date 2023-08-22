<?php
use DBA\Hashlist;
use DBA\Factory;
use DBA\ContainFilter;

use Middlewares\Utils\HttpErrorException;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class HashlistAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/hashlists";
    }

    public static function getDBAclass(): string {
      return Hashlist::class;
    }   

    protected function getFactory(): object {
      return Factory::getHashlistFactory();
    }

    public function getExpandables(): array {
      return ["accessGroup", "hashType", "hashes", "tasks"];
    }

    protected function getFilterACL(): array {
      return [new ContainFilter(Hashlist::ACCESS_GROUP_ID, Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getUser())))];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [
        "sourceType" => ['type' => 'str'],
        "sourceData" => ['type' => 'str'],
      ];
    }

    protected function createObject($mappedQuery, $QUERY): int {
      // Cast to createHashlist compatible upload format
      $dummyPost = [];
      switch ($mappedQuery["sourceType"]) {
        case "paste":
          $dummyPost["hashfield"] = base64_decode($mappedQuery["sourceData"]);
          break;
        case "import":
          $dummyPost["importfile"] = $mappedQuery["sourceData"];
          break;
        case "url":
          $dummyPost["url"] = $mappedQuery["sourceData"];
          break;
        default:
          // TODO: Choice validation are model based checks
          throw new HttpErrorException("sourceType value '" . $mappedQuery["sourceType"] . "' is not supported (choices paste, import, url");
      }

      // TODO: validate input is valid base64 encoded
      if ($mappedQuery["sourceType"] == "paste") {
        if (strlen($mappedQuery["sourceData"]) == 0) {
          // TODO: Should be 400 instead
          throw new HttpErrorException("sourceType=paste, requires sourceData to be non-empty");
        }
      }
      
      $hashlist = HashlistUtils::createHashlist(
        $mappedQuery[UQueryHashlist::HASHLIST_NAME],
        $mappedQuery[UQueryHashlist::HASHLIST_IS_SALTED],
        $mappedQuery[UQueryHashlist::HASHLIST_IS_SECRET],
        $mappedQuery[UQueryHashlist::HASHLIST_HEX_SALTED],
        $mappedQuery[UQueryHashlist::HASHLIST_SEPARATOR],
        $mappedQuery[UQueryHashlist::HASHLIST_FORMAT],
        // hashTypeId is a bit weird because the UQueryHashlist::HASHLIST_HASH_TYPE_ID is not the same as db column Hashlist::HASH_TYPE_ID
        $mappedQuery[Hashlist::HASH_TYPE_ID],
        (array_key_exists("saltSeperator", $mappedQuery)) ? $mappedQuery["saltSeparator"] : $mappedQuery[UQueryHashlist::HASHLIST_SEPARATOR],
        $mappedQuery[UQueryHashlist::HASHLIST_ACCESS_GROUP_ID],
        $mappedQuery["sourceType"],
        $dummyPost,
        [],
        $this->getUser(),
        $mappedQuery[UQueryHashlist::HASHLIST_USE_BRAIN],
        $mappedQuery[UQueryHashlist::HASHLIST_BRAIN_FEATURES]
      );

      // Modify fields not set on hashlist creation
      if (array_key_exists("notes", $mappedQuery)) {
        HashlistUtils::editNotes($hashlist->getId(), $mappedQuery["notes"], $this->getUser());
      };
      HashlistUtils::setArchived($hashlist->getId(), $mappedQuery[UQueryHashlist::HASHLIST_IS_ARCHIVED], $this->getUser());

      return $hashlist->getId();
    }

    protected function deleteObject(object $object): void {
      HashlistUtils::delete($object->getId(), $this->getUser());
    }

    public function updateObject(object $object, $data, $mappedFeatures, $processed = []): void {

      $key = Hashlist::IS_ARCHIVED;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        HashlistUtils::setArchived($object->getId(), $data[$key], $this->getUser());
      }

      $key = Hashlist::NOTES;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        HashlistUtils::editNotes($object->getId(), $data[$key], $this->getUser());
      }


      parent::updateObject($object, $data, $mappedFeatures, $processed = []);
    }
}

HashlistAPI::register($app);