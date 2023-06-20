<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Hash;
use DBA\Factory;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotImplementedException;

require_once(dirname(__FILE__) . "/shared.inc.php");


class HashAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/hashes";
    }

    public static function getAvailableMethods(): array {
      return ['GET'];
    }

    public static function getDBAclass(): string {
      return Hash::class;
    }   

    protected function getFactory(): object {
      return Factory::getHashFactory();
    }

    public function getExpandables(): array {
      return ["hashlist", "chunk"];
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
      /* Dummy code to implement abstract functions */
      assert(False, "Chunks cannot be created via API");
      return -1;
    }


    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "Chunks cannot be deleted via API");
    }
}

HashAPI::register($app);