<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Factory;
use DBA\ConfigSection;
use DBA\QueryFilter;
use DBA\OrderFilter;



require_once(dirname(__FILE__) . "/shared.inc.php");


class ConfigSectionAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/configsections";
    }

    public static function getAvailableMethods(): array {
      return ['GET'];
    }

    public static function getDBAclass(): string {
      return ConfigSection::class;
    }   

    protected function getFactory(): object {
      return Factory::getConfigSectionFactory();
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

    protected function createObject($mappedQuery, $QUERY): int {
       /* Dummy code to implement abstract functions */
       assert(False, "ConfigSections cannot be created via API");
       return -1;
    }

    public function updateObject(object $object, array $data, array $mappedFeatures, array $processed = []): void {
      assert(False, "ConfigSections cannot be updated via API");
    }

    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "ConfigSections cannot be deleted via API");
    }
}

ConfigSectionAPI::register($app);