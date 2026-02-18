<?php

use DBA\Factory;

use DBA\LogEntry;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class LogEntryAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/logentries";
  }
  
  public static function getDBAclass(): string {
    return LogEntry::class;
  }
  
  protected function createObject(array $data): int {
    throw new HttpError("Logentries cannot be created via API");
  }
  
  protected function deleteObject(object $object): void {
    throw new HttpError("Logentries cannot be deleted via API");
  }
}

use Slim\App;
/** @var App $app */
LogEntryAPI::register($app);