<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Factory;
use DBA\LogEntry;
use DBA\QueryFilter;
use DBA\OrderFilter;



require_once(dirname(__FILE__) . "/shared.inc.php");


class LogEntryAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/logentries";
    }

    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public static function getDBAclass(): string {
      return LogEntry::class;
    }

    protected function getFactory(): object {
      return Factory::getLogEntryFactory();
    }

    public function getExpandables(): array {
      return [];
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }

    protected function checkPermission(object $object): bool
    {
      return true;
    }
    

    protected function createObject($QUERY): int {
      /* Parameter is used as primary key in database */

      Util::createLogEntry(
        $QUERY[LogEntry::ISSUER],
        $QUERY[LogEntry::ISSUER_ID],
        $QUERY[LogEntry::LEVEL],
        $QUERY[LogEntry::MESSAGE]
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(LogEntry::ISSUER, $QUERY[LogEntry::ISSUER], '='),
        new QueryFilter(LogEntry::ISSUER_ID, $QUERY[LogEntry::ISSUER_ID], '='),
        new QueryFilter(LogEntry::LEVEL, $QUERY[LogEntry::LEVEL], '='),
        new QueryFilter(LogEntry::MESSAGE, $QUERY[LogEntry::MESSAGE], '=')
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