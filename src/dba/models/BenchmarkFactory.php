<?php

namespace Hashtopolis\dba\models;

use Hashtopolis\dba\AbstractModelFactory;

/**
 * @extends AbstractModelFactory<Benchmark>
 */
class BenchmarkFactory extends AbstractModelFactory {
  function getModelName(): string {
    return "Benchmark";
  }
  
  function getModelTable(): string {
    return "Benchmark";
  }

  function isMapping(): bool {
    return False;
  }
  
  function isCachable(): bool {
    return false;
  }
  
  function getCacheValidTime(): int {
    return -1;
  }
  
  /**
   * @return Benchmark
   */
  function getNullObject(): Benchmark {
    return new Benchmark(-1, null, null, null, null, null, null, null, null);
  }
  
  /**
   * @param array $dict
   * @return Benchmark
   */
  function createObjectFromDict(array $dict): Benchmark {
    $conv = [];
    foreach ($dict as $key => $val) {
      $conv[strtolower($key)] = $val;
    }
    $dict = $conv;
    return new Benchmark($dict['benchmarkid'], $dict['crackerbinaryid'], $dict['hashmode'], $dict['attackparameters'], $dict['devicesignature'], $dict['benchmarktype'], $dict['benchmarkvalue'], $dict['createtime'], $dict['expiretime']);
  }
}
