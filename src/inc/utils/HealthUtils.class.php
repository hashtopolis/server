<?php
use DBA\Agent;
use DBA\QueryFilter;
use DBA\HealthCheckAgent;
use DBA\Factory;
use DBA\HealthCheck;

class HealthUtils{
  /**
   * Checks if there is a running health check which the agent has not completed yet.
   * @param Agent $agent
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
        return true;
      }
    }
    return false;
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
    $expected = rand(0.1*DHealthCheck::NUM_HASHES,0.8*DHealthCheck::NUM_HASHES);
    for($i=0;$i<DHealthCheck::NUM_HASHES;$i++){
      if($i > $expected){
        $hashes = HealthUtils::generateHash($hashtypeId, Util::randomString(8));
      }
      else{
        $hashes = HealthUtils::generateHash($hashtypeId, Util::randomString(5));
      }
    }

    // create check
    $healthCheck = new HealthCheck(null, time(), DHealthCheckStatus::PENDING, $type, $hashtypeId, $crackerBinaryId, $expected);
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
      case 0:
        return md5($plain);
      default:
        throw new HTException("No implementation for this hash type available to generate hashes!");
    }
  }
}