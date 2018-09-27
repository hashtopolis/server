<?php
use DBA\Agent;
use DBA\QueryFilter;
use DBA\HealthCheckAgent;
use DBA\Factory;
use DBA\HealthCheck;

class HealthUtils{
  /**
   * @param int $checkAgentId 
   * @throws HTException 
   */
  public static function resetAgentCheck($checkAgentId){
    $checkAgent = Factory::getHealthCheckAgentFactory()->get($checkAgentId);
    if($checkAgent == null){
      throw new HTException("Invalid health check agent ID!");
    }

    // check if we also need to "un-complete" the health check because of this
    $check = Factory::getHealthCheckFactory()->get($checkAgent->getHealthCheckId());
    if($check->getStatus() == DHealthCheckStatus::COMPLETED){
      $check->setStatus(DHealthCheckStatus::PENDING);
    }
    else if($check->getStatus() == DHealthCheckStatus::ABORTED){
      throw new HTException("You cannot restart an agent check of an aborted health check!");
    }
    Factory::getHealthCheckFactory()->update($check);

    $checkAgent->setStatus(DHealthCheckAgentStatus::PENDING);
    $checkAgent->setStart(0);
    $checkAgent->setEnd(0);
    $checkAgent->setErrors("");
    $checkAgent->setCracked(0);
    $checkAgent->setNumGpus(0);
    Factory::getHealthCheckAgentFactory()->update($checkAgent);
  }

  /**
   * Checks if there is a running health check which the agent has not completed yet.
   * @param Agent $agent
   * @return HealthCheckAgent
   */
  public static function checkNeeded($agent){
    $qF1 = new QueryFilter(HealthCheckAgent::AGENT_ID, $agent->getId(), "=");
    $qF2 = new QueryFilter(HealthCheckAgent::STATUS, DHealthCheckAgentStatus::PENDING, "=");
    $check = Factory::getHealthCheckAgentFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    if(sizeof($check) == 0){
      return false;
    }
    foreach($check as $c){
      // test if the check is still running
      $healthCheck = Factory::getHealthCheckFactory()->get($c->getHealthCheckId());
      if($healthCheck->getStatus() == DHealthCheckStatus::PENDING){
        return $c;
      }
    }
    return false;
  }

  /**
   * Check if the health check is completed (all agents sent a response)
   * @param HealthCheck $healthCheck 
   */
  public static function checkCompletion($healthCheck){
    $qF = new QueryFilter(HealthCheckAgent::HEALTH_CHECK_ID, $healthCheck->getId(), "=");
    $checks = Factory::getHealthCheckAgentFactory()->filter([Factory::FILTER => $qF]);
    foreach($checks as $check){
      if($check->getStatus() != DHealthCheckAgentStatus::COMPLETED && $check->getStatus() != DHealthCheckAgentStatus::FAILED){
        return; // we can stop here, at least one agent has not finished yet
      }
    }
    $healthCheck->setStatus(DHealthCheckStatus::COMPLETED);
    Factory::getHealthCheckFactory()->update($healthCheck);
  }

  private static function getAttackMode($type){
    switch($type){
      case DHealthCheckType::BRUTE_FORCE:
        return " -a 3";
    }
    throw new HTException("Not able to get attack mode for this type!");
  }

  private static function getAttackInput($type){
    switch($type){
      case DHealthCheckType::BRUTE_FORCE:
        return " -1 ?l?u?d ?1?1?1?1?1";
    }
    throw new HTException("Not able to get attack input for this type!");
  }

  private static function getAttackPlain($hashtypeId, $type, $crackable){
    if($type == DHealthCheckType::BRUTE_FORCE && $hashtypeId == DHealthCheckMode::MD5){
      return Util::randomString(($crackable)?5:8);
    }
    throw new HTException("Not able to get attack plain for attack $type and hashtype $hashtypeId ($crackable)");
  }

  private static function getAttackNumHashes($hashtypeId){
    switch($hashtypeId){
      case DHealthCheckMode::MD5:
        return 100;
    }
    return DHealthCheck::NUM_HASHES;
  }

  /**
   * @param int $hashtypeId
   * @param int $type
   * @param int $crackerBinaryId
   * @throws HTException
   * @return HealthCheck
   */
  public static function createHealthCheck($hashtypeId, $type, $crackerBinaryId){
    $crackerBinary = Factory::getCrackerBinaryFactory()->get($crackerBinaryId);
    if($crackerBinary == null){
      throw new HTException("Invalid cracker binary selected!");
    }
    else if($type != DHealthCheckType::BRUTE_FORCE){
      throw new HTException("Invalid health check type!");
    }

    // we use len 5 here, but this can be adjusted depending on the agents abilities
    $hashes = [];
    $numHashes = HealthUtils::getAttackNumHashes($hashtypeId);
    $expected = rand(0.1*$numHashes,0.8*$numHashes);
    for($i=0;$i<$numHashes;$i++){
      $hashes[] = HealthUtils::generateHash($hashtypeId, HealthUtils::getAttackPlain($hashtypeId, $type, $i < $expected));
    }

    $cmd = SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS).HealthUtils::getAttackMode($type).HealthUtils::getAttackInput($type);

    // create check
    $healthCheck = new HealthCheck(null, 
      time(), 
      DHealthCheckStatus::PENDING, 
      $type, 
      $hashtypeId, 
      $crackerBinaryId, 
      $expected,
      $cmd);
    $healthCheck = Factory::getHealthCheckFactory()->save($healthCheck);

    // save hashes
    $filename = dirname(__FILE__)."/../../tmp/health-check-".$healthCheck->getId().".txt";
    file_put_contents($filename, implode("\n", $hashes));

    // check if file actually exists
    if(!file_exists($filename)){
      Factory::getHealthCheckFactory()->delete($healthCheck);
      throw new HTException("Failed to create hashes in tmp directory!");
    }

    // apply it to all agents
    $agents = Factory::getAgentFactory()->filter([]);
    $entries = [];
    foreach($agents as $agent){
      $entries[] = new HealthCheckAgent(null, $healthCheck->getId(), $agent->getId(), DHealthCheckAgentStatus::PENDING, 0, 0, 0, 0, "");
    }
    Factory::getHealthCheckAgentFactory()->massSave($entries);
  }

  /**
   * @param int $hashtypeId
   * @param string $plain
   * @throws HTException
   * @return string
   */
  public static function generateHash($hashtypeId, $plain){
    switch($hashtypeId){
      case DHealthCheckMode::MD5:
        return md5($plain);
      default:
        throw new HTException("No implementation for this hash type available to generate hashes!");
    }
  }
}