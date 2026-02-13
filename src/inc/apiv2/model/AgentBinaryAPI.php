<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\inc\utils\AgentBinaryUtils;

use Hashtopolis\dba\models\AgentBinary;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\common\error\HttpError;
use Hashtopolis\inc\HTException;


class AgentBinaryAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/agentbinaries";
  }
  
  public static function getDBAclass(): string {
    return AgentBinary::class;
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    $agentBinary = AgentBinaryUtils::newBinary(
      $data[AgentBinary::BINARY_TYPE],
      $data[AgentBinary::OPERATING_SYSTEMS],
      $data[AgentBinary::FILENAME],
      $data[AgentBinary::VERSION],
      $data[AgentBinary::UPDATE_TRACK],
      $this->getCurrentUser()
    );
    return $agentBinary->getId();
  }
  
  /**
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
    AgentBinaryUtils::deleteBinary($object->getId());
  }
  
  protected function getUpdateHandlers($id, $current_user): array {
    return [
      AgentBinary::BINARY_TYPE => fn($value) => AgentBinaryUtils::editType($id, $value, $current_user),
      AgentBinary::FILENAME => fn($value) => AgentBinaryUtils::editName($id, $value, $current_user),
      AgentBinary::UPDATE_TRACK => fn($value) => AgentBinaryUtils::editUpdateTracker($id, $value, $current_user),
    ];
  }
}
