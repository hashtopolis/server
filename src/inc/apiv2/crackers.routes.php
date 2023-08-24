<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\CrackerBinary;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

require_once(dirname(__FILE__) . "/shared.inc.php");


class CrackerBinaryAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/crackers";
    }

    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public static function getDBAclass(): string {
      return CrackerBinary::class;
    }

    public function getFeatures(): array {
      return CrackerBinary::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getCrackerBinaryFactory();
    }

    public function getExpandables(): array {
      return ["crackerBinaryType"];
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
      $object = CrackerUtils::createBinary(
        $QUERY[CrackerBinary::VERSION],
        $QUERY[CrackerBinary::BINARY_NAME],
        $QUERY[CrackerBinary::DOWNLOAD_URL],
        $QUERY[CrackerBinary::CRACKER_BINARY_TYPE_ID]
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(CrackerBinary::VERSION, $QUERY[CrackerBinary::VERSION], '='),
        new QueryFilter(CrackerBinary::BINARY_NAME, $QUERY[CrackerBinary::BINARY_NAME], '='),
        new QueryFilter(CrackerBinary::DOWNLOAD_URL, $QUERY[CrackerBinary::DOWNLOAD_URL], '='),
        new QueryFilter(CrackerBinary::CRACKER_BINARY_TYPE_ID, $QUERY[CrackerBinary::CRACKER_BINARY_TYPE_ID], '='),

      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(CrackerBinary::CRACKER_BINARY_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      /* No unique properties set on columns, thus multiple entries could exists, pick the latest (DESC ordering used) */
      assert(count($objects) >= 1);
      
      return $objects[0]->getId();
    }

    protected function deleteObject(object $object): void {
      CrackerUtils::deleteBinary($object->getId());
    }
}
CrackerBinaryAPI::register($app);