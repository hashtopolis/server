<?php

namespace Hashtopolis\inc\apiv2\model;

use Exception;
use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\ContainFilter;
use Hashtopolis\dba\Factory;
use Hashtopolis\inc\utils\CrackerUtils;
use Hashtopolis\inc\utils\AccessUtils;

use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\dba\models\AccessGroup;
use Hashtopolis\dba\models\CrackerBinaryType;
use Hashtopolis\dba\models\Task;
use Hashtopolis\dba\models\User;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\apiv2\error\HttpForbidden;
use Hashtopolis\inc\apiv2\error\ResourceNotFoundError;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\Util;


/**
 * @extends AbstractModelAPI<CrackerBinary>
 */
class CrackerBinaryAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/crackers";
  }

  public static function getDBAclass(): string {
    return CrackerBinary::class;
  }

  /**
   * @param CrackerBinary $object
   * @throws Exception
   */
  protected function getSingleACL(User $user, AbstractModel $object): bool {
    return in_array(
      $object->getAccessGroupId(),
      Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($user))
    );
  }

  /**
   * @throws Exception
   */
  protected function getFilterACL(): array {
    return [
      Factory::FILTER => [
        new ContainFilter(
          CrackerBinary::ACCESS_GROUP_ID,
          Util::arrayOfIds(AccessUtils::getAccessGroupsOfUser($this->getCurrentUser()))
        )
      ]
    ];
  }

  /**
   * Extra fields which are valid for creation of object. With one of the source
   * fields given, the archive of the cracker binary is uploaded to the server
   * instead of referencing it with an external download url.
   */
  public function getFormFields(): array {
    return [
      "sourceType" => ['type' => 'str', 'null' => True,
        'choices' => [
          "inline" => "Archive provided as base64 data in sourceData",
          "import" => "Archive taken from the import directory, sourceData is the filename",
          "url" => "Archive fetched from an http(s) url given in sourceData"
        ]],
      "sourceData" => ['type' => 'str', 'null' => True]
    ];
  }
  
  public static function getToOneRelationships(): array {
    return [
      'crackerBinaryType' => [
        'key' => CrackerBinary::CRACKER_BINARY_TYPE_ID,
        
        'relationType' => CrackerBinaryType::class,
        'relationKey' => CrackerBinaryType::CRACKER_BINARY_TYPE_ID,
      ],
      'accessGroup' => [
        'key' => CrackerBinary::ACCESS_GROUP_ID,

        'relationType' => AccessGroup::class,
        'relationKey' => AccessGroup::ACCESS_GROUP_ID,
      ],
    ];
  }
  
  public static function getToManyRelationships(): array {
    return [
      'tasks' => [
        'key' => CrackerBinary::CRACKER_BINARY_ID,
        
        'relationType' => Task::class,
        'relationKey' => Task::CRACKER_BINARY_ID,
      ],
    ];
  }
  
  /**
   * @throws HttpError
   * @throws HTException
   */
  protected function createObject(array $data): int {
    if (isset($data["sourceType"])) {
      if (isset($data[CrackerBinary::DOWNLOAD_URL])) {
        throw new HttpError("downloadUrl cannot be provided when the archive is uploaded with sourceType!");
      }
      if (!isset($data["sourceData"])) {
        throw new HttpError("sourceData is required when sourceType is provided!");
      }
      $binary = CrackerUtils::createBinaryFromUpload(
        $data[CrackerBinary::VERSION],
        $data[CrackerBinary::BINARY_NAME],
        $data[CrackerBinary::CRACKER_BINARY_TYPE_ID],
        $data["sourceType"],
        $data["sourceData"],
        $data[CrackerBinary::ACCESS_GROUP_ID],
        $this->getCurrentUser()
      );
      return $binary->getId();
    }
    if (!isset($data[CrackerBinary::DOWNLOAD_URL]) || strlen($data[CrackerBinary::DOWNLOAD_URL]) == 0) {
      throw new HttpError("Please provide all information!");
    }
    $binary = CrackerUtils::createBinary(
      $data[CrackerBinary::VERSION],
      $data[CrackerBinary::BINARY_NAME],
      $data[CrackerBinary::DOWNLOAD_URL],
      $data[CrackerBinary::CRACKER_BINARY_TYPE_ID],
      $data[CrackerBinary::ACCESS_GROUP_ID],
      $this->getCurrentUser()
    );
    return $binary->getId();
  }

  /**
   * @param CrackerBinary $object
   * @throws HTException
   */
  protected function deleteObject(AbstractModel $object): void {
    CrackerUtils::deleteBinary($object->getId());
  }

  /**
   * The access group of a binary can be changed, but only to a group the user is
   * also a member of (and only from a group the user has access to).
   *
   * @param int $id
   * @param User $current_user
   */
  protected function getUpdateHandlers($id, $current_user): array {
    return [
      CrackerBinary::ACCESS_GROUP_ID => fn($value) => CrackerUtils::changeAccessGroup($id, $value, $current_user)
    ];
  }

  /**
   * The download url of locally stored binaries is owned by the server, so it
   * cannot be overwritten with a patch.
   *
   * @param int $objectId
   * @param array $data
   * @throws HttpError
   * @throws HttpForbidden
   * @throws ResourceNotFoundError
   * @throws HTException
   */
  protected function updateObject(int $objectId, array $data): void {
    if (array_key_exists(CrackerBinary::DOWNLOAD_URL, $data)) {
      $binary = CrackerUtils::getBinary($objectId);
      if ($binary->getFilename() !== null) {
        throw new HttpError("The download url of a locally stored cracker binary cannot be changed!");
      }
    }
    parent::updateObject($objectId, $data);
  }
}
