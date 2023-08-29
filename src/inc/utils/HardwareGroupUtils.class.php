<?php

use DBA\Factory;
use DBA\Agent;
use DBA\HardwareGroup;
use DBA\QueryFilter;
class HardwareGroupUtils {

public static function updateHardwareOfAgent($devices, $agent) {
  $qF = new QueryFilter(HardwareGroup::DEVICES, $devices, "=");

  $res = Factory::getHardwareGroupFactory()->filter([Factory::FILTER => [$qF]], true);

  if(isset($res)) {
      Factory::getAgentFactory()->set($agent, Agent::HARDWARE_GROUP_ID, $res->getId());  
    } else {
      //make new hardwareGroup and add the id to the agent
      $newHardwareGroup = new HardwareGroup(null, $devices);
      $savedHardwareGroup = Factory::getHardwareGroupFactory()->save($newHardwareGroup);
      
      Factory::getAgentFactory()->set($agent, Agent::HARDWARE_GROUP_ID, $savedHardwareGroup->getId());
    }
  return $agent;
}

public static function getDevicesForAgent($agent) {
  $qF = new QueryFilter(HardwareGroup::HARDWARE_GROUP_ID, $agent->getHardwareGroupId(), "=");
  $res = Factory::getHardwareGroupFactory()->filter([Factory::FILTER => [$qF]], true);
  return $res->getDevices();
}

public static function getDevicesFromBenchmark($benchmark) {
  $qF = new QueryFilter(HardwareGroup::HARDWARE_GROUP_ID, $benchmark->getHardwareGroupId(), "=");
  $res = Factory::getHardwareGroupFactory()->filter([Factory::FILTER => [$qF]], true);
  return $res->getDevices();
}

public static function getHardwareGroupByDevices($devices) {
  $qF = new QueryFilter(HardwareGroup::DEVICES, $devices, "=");
  $res = Factory::getHardwareGroupFactory()->filter([Factory::FILTER => [$qF]], true);

  return $res;
}
}