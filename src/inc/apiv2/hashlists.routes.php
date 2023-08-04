<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Hashlist;
use DBA\Factory;
use DBA\ContainFilter;

use Middlewares\Utils\HttpErrorException;

require_once(dirname(__FILE__) . "/shared.inc.php");


class HashlistAPI extends AbstractBaseAPI {
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

    protected function createObject($QUERY): int {
      // Cast to createHashlist compatible upload format
      $dummyPost = [];
      switch ($QUERY["sourceType"]) {
        case "paste":
          $dummyPost["hashfield"] = base64_decode($QUERY["sourceData"]);
          break;
        case "import":
          $dummyPost["importfile"] = $QUERY["sourceData"];
          break;
        case "url":
          $dummyPost["url"] = $QUERY["sourceData"];
          break;
        default:
          // TODO: Choice validation are model based checks
          throw new HttpErrorException("sourceType value '" . $QUERY["sourceType"] . "' is not supported (choices paste, import, url");
      }

      // TODO: validate input is valid base64 encoded
      if ($QUERY["sourceType"] == "paste") {
        if (strlen($QUERY["sourceData"]) == 0) {
          // TODO: Should be 400 instead
          throw new HttpErrorException("sourceType=paste, requires sourceData to be non-empty");
        }
      }
      
      $hashlist = HashlistUtils::createHashlist(
        $QUERY[UQueryHashlist::HASHLIST_NAME],
        $QUERY[UQueryHashlist::HASHLIST_IS_SALTED],
        $QUERY[UQueryHashlist::HASHLIST_IS_SECRET],
        $QUERY[UQueryHashlist::HASHLIST_HEX_SALTED],
        $QUERY[UQueryHashlist::HASHLIST_SEPARATOR],
        $QUERY[UQueryHashlist::HASHLIST_FORMAT],
        // hashTypeId is a bit weird because the UQueryHashlist::HASHLIST_HASH_TYPE_ID is not the same as db column Hashlist::HASH_TYPE_ID
        $QUERY[Hashlist::HASH_TYPE_ID],
        (array_key_exists("saltSeperator", $QUERY)) ? $QUERY["saltSeparator"] : $QUERY[UQueryHashlist::HASHLIST_SEPARATOR],
        $QUERY[UQueryHashlist::HASHLIST_ACCESS_GROUP_ID],
        $QUERY["sourceType"],
        $dummyPost,
        [],
        $this->getUser(),
        $QUERY[UQueryHashlist::HASHLIST_USE_BRAIN],
        $QUERY[UQueryHashlist::HASHLIST_BRAIN_FEATURES]
      );

      // Modify fields not set on hashlist creation
      if (array_key_exists("notes", $QUERY)) {
        HashlistUtils::editNotes($hashlist->getId(), $QUERY["notes"], $this->getUser());
      };
      HashlistUtils::setArchived($hashlist->getId(), $QUERY[UQueryHashlist::HASHLIST_IS_ARCHIVED], $this->getUser());

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