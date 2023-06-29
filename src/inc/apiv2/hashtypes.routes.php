<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\HashType;
use DBA\Factory;

require_once(dirname(__FILE__) . "/shared.inc.php");


class HashTypeAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/hashtypes";
    }

    public static function getDBAclass(): string {
      return HashType::class;
    }

    protected function getFactory(): object {
      return Factory::getHashTypeFactory();
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
      $hashtypeId = $QUERY[HashType::HASH_TYPE_ID];

      HashtypeUtils::addHashtype(
        $hashtypeId,
        $QUERY[HashType::DESCRIPTION],
        $QUERY[HashType::IS_SALTED],
        $QUERY[HashType::IS_SLOW_HASH],
        $this->getUser()
      );

      /* On succesfully insert, return ID */
      return $hashtypeId;
    }

    protected function deleteObject(object $object): void {
      HashtypeUtils::deleteHashtype($object->getId());
    }
}

HashTypeAPI::register($app);