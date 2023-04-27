<?php

use DBA\Factory;
use DBA\Agent;
use DBA\HardwareGroup;
use DBA\QueryFilter;
class HardwareGroupUtils {

public static function updateHardwareOfAgent($devices, $agent) {
  $qF = new QueryFilter("devices", $devices, "=");

  $res = Factory::getHardwareGroupFactory()->filter([Factory::FILTER => [$qF]], true);

  if(isset($res)) {
      Factory::getAgentFactory()->set($agent, Agent::HARDWARE_GROUP_ID, $res->getId());  
  } else {
      //nieuwe hardware group maken en id bij agent zetten
      $newHardwareGroup = new HardwareGroup(null, $devices);
      $savedHardwareGroup = Factory::getHardwareGroupFactory()->save($newHardwareGroup);

      Factory::getAgentFactory()->set($agent, Agent::HARDWARE_GROUP_ID, $savedHardwareGroup->getId());
    }
}

public static function getDevicesFromBenchmark($benchmark) {
  $qF = new QueryFilter("HardwareGroupId", $benchmark->getHardwareGroupId(), "=");
  $res = Factory::getHardwareGroupFactory()->filter([Factory::FILTER => [$qF]], true);
  return $res->getDevices();
}

public static function getHardwareGroupByDevices($devices) {
  $qF = new QueryFilter("devices", $devices, "=");
  $res = Factory::getHardwareGroupFactory()->filter([Factory::FILTER => [$qF]], true);

  return $res;
}
}