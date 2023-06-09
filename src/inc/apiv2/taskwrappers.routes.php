<?php

use DBA\AccessGroup;
use DBA\AccessGroupUser;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\TaskWrapper;
use DBA\Factory;
use DBA\QueryFilter;
use DBA\OrderFilter;


require_once(dirname(__FILE__) . "/shared.inc.php");


class TaskWrappersAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/taskwrappers";
    }

    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::CREATE_HASHLIST_ACCESS;
    }

    public static function getDBAclass(): string {
      return TaskWrapper::class;
    }    

    public function getFeatures(): array {
      return TaskWrapper::getFeatures();
    }

    protected function getFactory(): object {
      return Factory::getTaskWrapperFactory();
    }

    public function getExpandables(): array {
      // TODO: Implement
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
      assert(False, "TaskWrappers cannot be created via API");
      return -1;
    }

    protected function deleteObject(object $object): void {
      assert(False, "TaskWrappers cannot be deleted via API");
    }
}

TaskWrappersAPI::register($app);