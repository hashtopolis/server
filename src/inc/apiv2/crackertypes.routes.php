<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\CrackerBinaryType;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;


require_once(dirname(__FILE__) . "/shared.inc.php");


class CrackerBinaryTypeAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/crackertypes";
    }

    public static function getDBAclass(): string {
      return CrackerBinaryType::class;
    }

    protected function getFactory(): object {
      return Factory::getCrackerBinaryTypeFactory();
    }

    public function getExpandables(): array {
      return ["crackerVersions"];
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }
    
    protected function createObject($QUERY): int {
      CrackerUtils::createBinaryType($QUERY[CrackerBinaryType::TYPE_NAME]);

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(CrackerBinaryType::TYPE_NAME, $QUERY[CrackerBinaryType::TYPE_NAME], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(CrackerBinaryType::CRACKER_BINARY_TYPE_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      assert(count($objects) == 1);
      
      return $objects[0]->getId();
    }


    protected function deleteObject(object $object): void {
      CrackerUtils::deleteBinaryType($object->getId());
    }
}

CrackerBinaryTypeAPI::register($app);