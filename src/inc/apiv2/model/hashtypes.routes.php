<?php

use DBA\HashType;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


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
   * @throws HTException
   */
  protected function deleteObject(object $object): void {
    HashtypeUtils::deleteHashtype($object->getId());
  }
}

use Slim\App;
/** @var App $app */
HashTypeAPI::register($app);