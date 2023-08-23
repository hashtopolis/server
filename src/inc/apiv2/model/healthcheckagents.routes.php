<?php
use DBA\Factory;
use DBA\HealthCheck;
use DBA\HealthCheckAgent;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class HealthCheckAgentAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/healthcheckagents";
    }

    public static function getAvailableMethods(): array {
      return ['GET'];
    }

    public static function getDBAclass(): string {
      return HealthCheckAgent::class;
    }
    protected function getFactory(): object {
      return Factory::getHealthCheckAgentFactory();
    }

    public function getExpandables(): array {
      return ['agent', 'healthCheck'];
    }

    protected function doExpand(object $object, string $expand): mixed {
      assert($object instanceof HealthCheckAgent);
      switch($expand) {
        case 'agent':
          $obj = Factory::getAgentFactory()->get($object->getAgentId());
          return $this->obj2Array($obj);      
        case 'healthCheck':
          $obj = Factory::getHealthCheckFactory()->get($object->getHealthCheckId());
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
    
    protected function createObject(array $object): int {
       /* Dummy code to implement abstract functions */
       assert(False, "HealthCheckAgents cannot be created via API");
       return -1;
    }

    public function updateObject(object $object, array $data, array $processed = []): void {
      assert(False, "HealthCheckAgents cannot be updated via API");
   }

    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      assert(False, "HealthCheckAgents cannot be deleted via API");
    }
}

HealthCheckAgentAPI::register($app);