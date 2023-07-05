<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Factory;
use DBA\Config;
use DBA\QueryFilter;
use DBA\OrderFilter;



require_once(dirname(__FILE__) . "/shared.inc.php");


class ConfigAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/configs";
    }

    public static function getAvailableMethods(): array {
      return ['GET', 'PATCH'];
    }

    public static function getDBAclass(): string {
      return Config::class;
    }   

    protected function getFactory(): object {
      return Factory::getConfigFactory();
    }

    public function getExpandables(): array {
      return ['configSection'];
    }
 
    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }

    protected function createObject($QUERY): int {
       /* Dummy code to implement abstract functions */
       assert(False, "Configs cannot be created via API");
       return -1;
    }

    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "Configs cannot be deleted via API");
    }
}

ConfigAPI::register($app);