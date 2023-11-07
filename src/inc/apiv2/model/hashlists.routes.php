<?php

use DBA\AccessGroup;
use DBA\ContainFilter;
use DBA\Factory;

use DBA\Hash;
use DBA\HashType;
use DBA\Hashlist;
use DBA\HashlistHashlist;
use DBA\Task;
use DBA\TaskWrapper;

use Middlewares\Utils\HttpErrorException;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class HashlistAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/hashlists";
    }

    public static function getDBAclass(): string {
      return Hashlist::class;
    }   

    public static function getExpandables(): array {
      return ["accessGroup", "hashType", "hashes", "tasks", "hashlists"];
    }
     
    protected static function fetchExpandObjects(array $objects, string $expand): mixed {     
      /* Ensure we receive the proper type */
      array_walk($objects, function($obj) { assert($obj instanceof Hashlist); });

      /* Expand requested section */
      switch($expand) {
        case 'accessGroup':
          return self::getForeignKeyRelation(
            $objects,
            Hashlist::ACCESS_GROUP_ID,
            Factory::getAccessGroupFactory(),
            AccessGroup::ACCESS_GROUP_ID
          );
        case 'hashType':
          return self::getForeignKeyRelation(
            $objects,
            Hashlist::HASH_TYPE_ID,
            Factory::getHashTypeFactory(),
            HashType::HASH_TYPE_ID
          );        
        case 'hashes':
          return self::getManyToOneRelation(
            $objects,
            Hashlist::HASHLIST_ID,
            Factory::getHashFactory(),
            Hash::HASHLIST_ID
          );
        case 'hashlists':
          /* PARENT_HASHLIST_ID in use in intermediate table */
          return self::getManyToOneRelationViaIntermediate(
            $objects, 
            Hashlist::HASHLIST_ID,
            Factory::getHashlistHashlistFactory(),
            HashlistHashlist::PARENT_HASHLIST_ID,
            Factory::getHashlistFactory(),
            Hashlist::HASHLIST_ID,
          );
        case 'tasks':
          return self::getManyToOneRelationViaIntermediate(
            $objects,
            Hashlist::HASHLIST_ID,
            Factory::getTaskWrapperFactory(),
            TaskWrapper::HASHLIST_ID, 
            Factory::getTaskFactory(),
            Task::TASK_WRAPPER_ID,
          );
        default:
          throw new BadFunctionCallException("Internal error: Expansion '$expand' not implemented!");
      }
    }

    protected function getFilterACL(): array {
      return [new ContainFilter(Hashlist::ACCESS_GROUP_ID, Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser())))];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [
        "hashlistSeperator" => ['type' => 'str', "null" => True],
        "sourceType" => ['type' => 'str'],
        "sourceData" => ['type' => 'str'],
      ];
    }

    protected function createObject(array $data): int {
      // Cast to createHashlist compatible upload format
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

      // TODO: validate input is valid base64 encoded
      if ($data["sourceType"] == "paste") {
        if (strlen($data["sourceData"]) == 0) {
          // TODO: Should be 400 instead
          throw new HttpErrorException("sourceType=paste, requires sourceData to be non-empty");
        }
      }
    
      $hashlist = HashlistUtils::createHashlist(
        $data[Hashlist::HASHLIST_NAME],
        $data[Hashlist::IS_SALTED],
        $data[Hashlist::IS_SECRET],
        $data[Hashlist::HEX_SALT],
        $data["hashlistSeperator"] ?? "",
        $data[Hashlist::FORMAT],
        $data[Hashlist::HASH_TYPE_ID],
        $data[Hashlist::SALT_SEPARATOR] ?? $data["hashlistSeperator"] ?? "",
        $data[UQueryHashlist::HASHLIST_ACCESS_GROUP_ID],
        $data["sourceType"],
        $dummyPost,
        [],
        $this->getCurrentUser(),
        $data[Hashlist::BRAIN_ID],
        $data[Hashlist::BRAIN_FEATURES]
      );

      // Modify fields not set on hashlist creation
      if (array_key_exists("notes", $data)) {
        HashlistUtils::editNotes($hashlist->getId(), $data["notes"], $this->getCurrentUser());
      };
      HashlistUtils::setArchived($hashlist->getId(), $data[UQueryHashlist::HASHLIST_IS_ARCHIVED], $this->getCurrentUser());

      return $hashlist->getId();
    }

    protected function deleteObject(object $object): void {
      HashlistUtils::delete($object->getId(), $this->getCurrentUser());
    }

    public function updateObject(object $object, $data, $processed = []): void {

      $key = Hashlist::IS_ARCHIVED;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        HashlistUtils::setArchived($object->getId(), $data[$key], $this->getCurrentUser());
      }

      $key = Hashlist::NOTES;
      if (array_key_exists($key, $data)) {
        array_push($processed, $key);
        HashlistUtils::editNotes($object->getId(), $data[$key], $this->getCurrentUser());
      }

      parent::updateObject($object, $data, $processed = []);
    }
}

HashlistAPI::register($app);