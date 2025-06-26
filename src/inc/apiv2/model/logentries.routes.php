<?php
use DBA\Factory;

use DBA\LogEntry;
use JetBrains\PhpStorm\NoReturn;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class LogEntryAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/logentries";
    }

    public static function getDBAclass(): string {
      return LogEntry::class;
    }

    #[NoReturn] protected function createObject(array $data): int {
      assert(False, "Logentries cannot be created via API");
    }

    protected function deleteObject(object $object): void {
      Factory::getLogEntryFactory()->delete($object);
    }
}

LogEntryAPI::register($app);