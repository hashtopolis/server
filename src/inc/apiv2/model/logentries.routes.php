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

    protected function getFactory(): object {
      return Factory::getLogEntryFactory();
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }

    protected function createObject(array $data): int {
      /* Parameter is used as primary key in database */

      Util::createLogEntry(
        $data[LogEntry::ISSUER],
        $data[LogEntry::ISSUER_ID],
        $data[LogEntry::LEVEL],
        $data[LogEntry::MESSAGE]
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(LogEntry::ISSUER, $data[LogEntry::ISSUER], '='),
        new QueryFilter(LogEntry::ISSUER_ID, $data[LogEntry::ISSUER_ID], '='),
        new QueryFilter(LogEntry::LEVEL, $data[LogEntry::LEVEL], '='),
        new QueryFilter(LogEntry::MESSAGE, $data[LogEntry::MESSAGE], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(LogEntry::TIME, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) == 1);

      return $objects[0]->getId();
    }

    protected function deleteObject(object $object): void {
      Factory::getLogEntryFactory()->delete($object);
    }
}

LogEntryAPI::register($app);