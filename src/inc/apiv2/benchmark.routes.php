<?php

use DBA\Benchmark;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;

use DBA\Hash;
use DBA\Factory;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotImplementedException;

require_once(dirname(__FILE__) . "/shared.inc.php");


class BenchmarkAPI extends AbstractBaseAPI {
    public static function getBaseUri(): string {
      return "/api/v2/ui/benchmarks";
    }

    public function getPermission(): string {
      // TODO: Find proper permission
      return DAccessControl::DELETE_BENCHMARK_ACCESS;
    }

    public static function getDBAclass(): string {
      return Benchmark::class;
    }   

    protected function getFactory(): object {
      return Factory::getBenchmarkFactory();
    }

    public function getExpandables(): array {
      return ["crackerBinary", "hardwareGroup"];
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
      assert(False, "Benchmarks cannot be created via API");
      return -1;
    }


    protected function deleteObject(object $object): void {
      /* Dummy code to implement abstract functions */
      BenchmarkUtils::delete($object->getId());
    }
}

BenchmarkAPI::register($app);