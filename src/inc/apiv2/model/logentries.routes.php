<?php
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

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
      assert(False, "Logentries cannot be created via API");
      return -1;
    }

    protected function deleteObject(object $object): void {
      Factory::getLogEntryFactory()->delete($object);
    }
}

LogEntryAPI::register($app);