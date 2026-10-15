<?php

namespace Hashtopolis\inc\apiv2\model;

use Hashtopolis\dba\AbstractModel;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\Benchmark;
use Hashtopolis\dba\models\CrackerBinary;
use Hashtopolis\inc\apiv2\common\AbstractModelAPI;
use Hashtopolis\inc\apiv2\error\HttpError;


/**
 * @extends AbstractModelAPI<Benchmark>
 */
class BenchmarkAPI extends AbstractModelAPI {
  public static function getBaseUri(): string {
    return "/api/v2/ui/benchmarks";
  }
  
  public static function getAvailableMethods(): array {
    return ['GET', 'DELETE'];
  }
  
  public static function getDBAclass(): string {
    return Benchmark::class;
  }
  
  public static function getToOneRelationships(): array {
    return [
      'crackerBinary' => [
        'key' => Benchmark::CRACKER_BINARY_ID,
        
        'relationType' => CrackerBinary::class,
        'relationKey' => CrackerBinary::CRACKER_BINARY_ID,
      ],
    ];
  }
  
  /**
   * @throws HttpError
   */
  protected function createObject(array $data): int {
    throw new HttpError("Benchmarks are created by agents and cannot be created via API");
  }
  
  /**
   * @throws HttpError
   */
  public function updateObject(int $objectId, array $data): void {
    throw new HttpError("Benchmarks cannot be updated via API");
  }
  
  /**
   * @param Benchmark $object
   * @throws HttpError
   */
  protected function deleteObject(AbstractModel $object): void {
    Factory::getBenchmarkFactory()->delete($object);
  }
}
