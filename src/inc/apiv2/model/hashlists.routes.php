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

use DBA\User;
use Middlewares\Utils\HttpErrorException;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class HashlistAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/hashlists";
  }
  
  public static function getDBAclass(): string {
    return Hashlist::class;
  }
  
  
  public static function getToOneRelationships(): array {
    return [
      'accessGroup' => [
        'key' => Hashlist::ACCESS_GROUP_ID,
        
        'relationType' => AccessGroup::class,
        'relationKey' => AccessGroup::ACCESS_GROUP_ID,
      ],
      'hashType' => [
        'key' => Hashlist::HASH_TYPE_ID,
        
        'relationType' => HashType::class,
        'relationKey' => HashType::HASH_TYPE_ID,
      ],
    ];
  }
  
  
  public static function getToManyRelationships(): array {
    return [
      'hashes' => [
        'key' => Hashlist::HASHLIST_ID,
        
        'relationType' => Hash::class,
        'relationKey' => Hash::HASHLIST_ID,
      ],
      /* Special case due to superhashlist setup. PARENT_HASHLIST_ID in use in intermediate table */
      'hashlists' => [
        'key' => Hashlist::HASHLIST_ID,
        
        'junctionTableType' => HashlistHashlist::class,
        'junctionTableFilterField' => HashlistHashlist::PARENT_HASHLIST_ID,
        'junctionTableJoinField' => HashlistHashlist::HASHLIST_ID,
        
        'relationType' => Hashlist::class,
        'relationKey' => Hashlist::HASHLIST_ID,
      ],
      'tasks' => [
        'key' => Hashlist::HASHLIST_ID,
        
        'junctionTableType' => TaskWrapper::class,
        'junctionTableFilterField' => TaskWrapper::HASHLIST_ID,
        'junctionTableJoinField' => TaskWrapper::TASK_WRAPPER_ID,
        
        'relationType' => Task::class,
        'relationKey' => Task::TASK_WRAPPER_ID,
      ],
    ];
  }
  
  protected function getSingleACL(User $user, object $object): bool {
    $accessGroupsUser = Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user));
    
    return in_array($object->getAccessGroupId(), $accessGroupsUser);
  }
  
  protected function getFilterACL(): array {
    return [
      Factory::FILTER => [
        new ContainFilter(Hashlist::ACCESS_GROUP_ID, Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser())))
      ]
    ];
  }
  
  public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return [
      "hashlistSeperator" => ['type' => 'str', "null" => True],
      "sourceType" => ['type' => 'str'],
      "sourceData" => ['type' => 'str'],
    ];
  }
  
  /**
   * @throws HttpErrorException
   * @throws HttpError
   * @throws HTException
   */
  protected function createObject(array $data): int {
    return $this->createObjectAndGetResult($data)["pk"];
  }

  /**
   * @throws HttpErrorException
   * @throws HttpError
   * @throws HTException
   */
  protected function createObjectAndGetResult(array $data): array {
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
    
    if ($data["sourceType"] == "paste") {
      if (strlen($data["sourceData"]) == 0) {
        throw new HttpError("sourceType=paste, requires sourceData to be non-empty");
      }
      else if ($dummyPost["hashfield"] == false) {
        throw new HttpError("sourceData not valid base64 encoding");
      }
    }
    
    $hashlistData = HashlistUtils::createHashlist(
      $data[Hashlist::HASHLIST_NAME],
      $data[Hashlist::IS_SALTED],
      $data[Hashlist::IS_SECRET],
      $data[Hashlist::HEX_SALT],
      $data["saltSeparator"] ?? "",
      $data[Hashlist::FORMAT],
      $data[Hashlist::HASH_TYPE_ID],
      $data[Hashlist::SALT_SEPARATOR] ?? $data["saltSeparator"] ?? "",
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
      HashlistUtils::editNotes($hashlistData["hashlist"]->getId(), $data["notes"], $this->getCurrentUser());
    }
    HashlistUtils::setArchived($hashlistData["hashlist"]->getId(), $data[UQueryHashlist::HASHLIST_IS_ARCHIVED], $this->getCurrentUser());
    
    $creationResult["pk"] = $hashlistData["hashlist"]->getId();
    $creationResult["creationInformation"] = $hashlistData["statistics"];

    return $creationResult;
  }
  
  /**
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
    HashlistUtils::delete($object->getId(), $this->getCurrentUser());
  }
  
  protected function getUpdateHandlers($id, $current_user): array {
    return [
      Hashlist::IS_ARCHIVED => fn($value) => HashlistUtils::setArchived($id, $value, $current_user),
      Hashlist::NOTES => fn($value) => HashlistUtils::editNotes($id, $value, $current_user),
      Hashlist::IS_SECRET => fn($value) => HashlistUtils::setSecret($id, $value, $current_user),
      Hashlist::HASHLIST_NAME => fn($value) => HashlistUtils::rename($id, $value, $current_user),
      Hashlist::ACCESS_GROUP_ID => fn($value) => HashlistUtils::changeAccessGroup($id, $value, $current_user)
    ];
  }
}

use Slim\App;
/** @var App $app */
HashlistAPI::register($app);
