<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\dba\models\LogEntry;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;


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
   * @throws HttpError
   */
  protected function deleteObject(object $object): void {
    throw new HttpError("Logentries cannot be deleted via API");
  }
}
