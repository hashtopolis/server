<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\models\HashType;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\utils\HashtypeUtils;
use Hashtopolis\inc\HTException;


/**
 * @extends AbstractModelAPI<HashType>
 */
class HashTypeAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/hashtypes";
  }
  
  public static function getDBAclass(): string {
    return HashType::class;
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    $hashtype = HashtypeUtils::addHashtype(
      $data[HashType::HASH_TYPE_ID],
      $data[HashType::DESCRIPTION],
      $data[HashType::IS_SALTED],
      $data[HashType::IS_SLOW_HASH],
      $this->getCurrentUser()
    );
    
    return $hashtype->getId();
  }
  
  /**
   * @param HashType $object
   * @throws HTException
   */
  protected function deleteObject(AbstractModel $object): void {
    HashtypeUtils::deleteHashtype($object->getId());
  }
}
