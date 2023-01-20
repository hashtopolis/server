<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Supertask;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;

require_once(dirname(__FILE__) . "/shared.inc.php");


class SupertaskAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/supertasks";
    }

    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public static function getDBAclass(): string {
      return Supertask::class;
    }

    protected function getFactory(): object {
      return Factory::getSupertaskFactory();
    }

    public function getExpandables(): array {
      return [ "pretasks" ];
    }

    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
      return  [
        "pretasks" => ['type' => 'array', 'subtype' => 'int']
      ];
    }

    protected function checkPermission(object $object): bool
    {
      return true;
    }
    

    protected function createObject($QUERY): int {
      SupertaskUtils::createSupertask(
        $QUERY[Supertask::SUPERTASK_NAME],
        $QUERY["pretasks"],
      );

      /* On succesfully insert, return ID */
      $qFs = [
        new QueryFilter(Supertask::SUPERTASK_NAME, $QUERY[Supertask::SUPERTASK_NAME], '=')
      ];

      /* Hackish way to retreive object since Id is not returned on creation */
      $oF = new OrderFilter(Supertask::SUPERTASK_ID, "DESC");
      $objects = $this->getFactory()->filter([Factory::FILTER => $qFs, Factory::ORDER => $oF]);
      /* No unique properties set on columns, thus multiple entries could exists, pick the latest (DESC ordering used) */
      assert(count($objects) >= 1);

      return $objects[0]->getId();      
    }

    protected function deleteObject(object $object): void {
      SupertaskUtils::deleteSupertask($object->getId());
    }
}

SupertaskAPI::register($app);