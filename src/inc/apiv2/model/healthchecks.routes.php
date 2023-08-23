<?php
use DBA\Factory;
use DBA\QueryFilter;

use DBA\HealthCheck;

require_once(dirname(__FILE__) . "/../common/AbstractModelAPI.class.php");


class HealthCheckAPI extends AbstractModelAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/healthchecks";
    }
 
    public static function getDBAclass(): string {
      return HealthCheck::class;
    }

    protected function getFactory(): object {
      return Factory::getHealthCheckFactory();
    }

    public function getExpandables(): array {
      return ['crackerBinary', 'healthCheckAgents'];
    }
 
    protected function doExpand($object, string $expand): mixed {
      assert($object instanceof HealthCheck);
      switch($expand) {
        case 'crackerBinary':
          $obj = Factory::getCrackerBinaryFactory()->get($object->getCrackerBinaryId());
          return $this->obj2Array($obj);
        case 'healthCheckAgents':
          $qF = new QueryFilter(HealthCheck::HEALTH_CHECK_ID, $object->getId(), "=");
          return $this->filterQuery(Factory::getHealthCheckAgentFactory(), $qF);
      }
    }  
    
    protected function getFilterACL(): array {
      return [];
    }

    public function getFormFields(): array {
    // TODO Form declarations in more generic class to allow auto-generated OpenAPI specifications
    return  [];
    }

    protected function createObject(array $data): int {
      $obj = HealthUtils::createHealthCheck(
        $data[HealthCheck::HASHTYPE_ID],
        $data[HealthCheck::CHECK_TYPE],
        $data[HealthCheck::CRACKER_BINARY_ID]
      );

      return $obj->getId();
    }

    protected function deleteObject(object $object): void {
      HealthUtils::deleteHealthCheck($object->getId());
    }
}

HealthCheckAPI::register($app);