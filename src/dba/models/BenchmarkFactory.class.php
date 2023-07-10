<?php

namespace DBA;

class BenchmarkFactory extends AbstractModelFactory {
  function getModelName() {
    return "Benchmark";
  }
  
  function getModelTable() {
    return "Benchmark";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  /**
   * @return Benchmark
   */
  function getNullObject() {
    $o = new Benchmark(-1, null, null, null, null, null, null);
    return $o;
  }
  
  /**
   * @param string $pk
   * @param array $dict
   * @return Benchmark
   */
  function createObjectFromDict($pk, $dict) {
    $o = new Benchmark($dict['benchmarkId'], $dict['benchmarkType'], $dict['benchmarkValue'], $dict['attackParameters'], $dict['hashMode'], $dict['hardwareGroupId'], $dict['ttl']);
    return $o;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return Benchmark|Benchmark[]
   */
  function filter($options, $single = false) {
    $join = false;
    if (array_key_exists('join', $options)) {
      $join = true;
    }
    if ($single) {
      if ($join) {
        return parent::filter($options, $single);
      }
      return Util::cast(parent::filter($options, $single), Benchmark::class);
    }
    $objects = parent::filter($options, $single);
    if ($join) {
      return $objects;
    }
    $models = array();
    foreach ($objects as $object) {
      $models[] = Util::cast($object, Benchmark::class);
    }
    return $models;
  }
  
  /**
   * @param string $pk
   * @return Benchmark
   */
  function get($pk) {
    return Util::cast(parent::get($pk), Benchmark::class);
  }
  
  /**
   * @param Benchmark $model
   * @return Benchmark
   */
  function save($model) {
    return Util::cast(parent::save($model), Benchmark::class);
  }
}