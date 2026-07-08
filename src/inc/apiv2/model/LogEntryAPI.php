<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\models\LogEntry;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;


/**
 * @extends AbstractModelAPI<LogEntry>
 */
class LogEntryAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/logentries";
  }
  
  public static function getDBAclass(): string {
    return LogEntry::class;
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    throw new HttpError("Logentries cannot be created via API");
  }
  
  /**
   * @param LogEntry $object
   * @throws HttpError
   */
  protected function deleteObject(AbstractModel $object): void {
    throw new HttpError("Logentries cannot be deleted via API");
  }

  public static function getAvailableMethods(): array {
    return ['GET'];
  }

}
