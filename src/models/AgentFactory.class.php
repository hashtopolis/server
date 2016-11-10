<?php

class AgentFactory extends AbstractModelFactory {
  function getModelName() {
    return "Agent";
  }
  
  function getModelTable() {
    return "Agent";
  }
  
  function isCachable() {
    return false;
  }
  
  function getCacheValidTime() {
    return -1;
  }
  
  function getNullObject() {
    $o = new Agent(-1, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
    return $o;
  }
  
  function createObjectFromDict($pk, $dict) {
    $o = new Agent($pk, $dict['agentName'], $dict['uid'], $dict['os'], $dict['gpus'], $dict['hcVersion'], $dict['cmdPars'], $dict['wait'], $dict['ignoreErrors'], $dict['isActive'], $dict['isTrusted'], $dict['token'], $dict['lastAct'], $dict['lastTime'], $dict['lastIp'], $dict['userId'], $dict['cpuOnly']);
    return $o;
  }
}