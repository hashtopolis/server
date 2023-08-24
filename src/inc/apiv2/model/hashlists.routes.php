<?php
use DBA\ContainFilter;
use DBA\JoinFilter;
use DBA\Factory;
use DBA\QueryFilter;

use DBA\Hash;
use DBA\Hashlist;
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

    protected function getFactory(): object {
      return Factory::getHashlistFactory();
    }

    public function getExpandables(): array {
      return ["accessGroup", "hashType", "hashes", "tasks"];
    }

    protected function doExpand(object $object, string $expand): mixed {
      assert($object instanceof Hashlist);
      switch($expand) {
        case 'accessGroup':
          $obj = Factory::getAccessGroupFactory()->get($object->getAccessGroupId());
          return $this->obj2Array($obj);
        case 'hashType':
          $obj = Factory::getHashTypeFactory()->get($object->getHashTypeId());
          return $this->obj2Array($obj);
        case 'hashes':
          $qF = new QueryFilter(Hash::HASHLIST_ID, $object->getId(), "=");
          return $this->filterQuery(Factory::getHashFactory(), $qF);
        case 'tasks':
          $qF = new QueryFilter(TaskWrapper::HASHLIST_ID, $object->getHashTypeId(), "=", Factory::getTaskWrapperFactory());
          $jF = new JoinFilter(Factory::getTaskWrapperFactory(), Task::TASK_WRAPPER_ID, TaskWrapper::TASK_WRAPPER_ID);
          return $this->joinQuery(Factory::getTaskFactory(), $qF, $jF);
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