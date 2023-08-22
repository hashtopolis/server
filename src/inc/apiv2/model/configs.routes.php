<?php
use DBA\Factory;

use DBA\Config;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class ConfigAPI extends AbstractModelAPI {
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

    protected function doExpand(object $object, string $expand): mixed {
      assert($object instanceof Config);
      switch($expand) {
        case 'configSection':
          $obj = Factory::getConfigSectionFactory()->get($object->getConfigSectionId());
          return $this->obj2Array($obj);
      }
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
       assert(False, "Configs cannot be created via API");
       return -1;
    }

    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "Configs cannot be deleted via API");
    }
}

ConfigAPI::register($app);