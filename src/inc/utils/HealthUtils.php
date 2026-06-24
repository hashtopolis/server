<?php

namespace Hashtopolis\inc\utils;

use Hashtopolis\dba\models\Agent;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\models\HealthCheckAgent;
use Hashtopolis\dba\Factory;
use Hashtopolis\dba\models\HealthCheck;
use Hashtopolis\inc\apiv2\error\HttpError;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DHealthCheck;
use Hashtopolis\inc\defines\DHealthCheckAgentStatus;
use Hashtopolis\inc\defines\DHealthCheckMode;
use Hashtopolis\inc\defines\DHealthCheckStatus;
use Hashtopolis\inc\defines\DHealthCheckType;
use Hashtopolis\inc\HTException;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\Util;

class HealthUtils {
  /**
   * @param int $checkAgentId
   * @throws HTException
   */
  public static function resetAgentCheck($checkAgentId) {
    $checkAgent = Factory::getHealthCheckAgentFactory()->get($checkAgentId);
    if ($checkAgent == null) {
      throw new HTException("Invalid health check agent ID!");
    }
    
    // check if we also need to "un-complete" the health check because of this
    $check = Factory::getHealthCheckFactory()->get($checkAgent->getHealthCheckId());
    if ($check->getStatus() == DHealthCheckStatus::COMPLETED) {
      $check->setStatus(DHealthCheckStatus::PENDING);
    }
    else if ($check->getStatus() == DHealthCheckStatus::ABORTED) {
      throw new HTException("You cannot restart an agent check of an aborted health check!");
    }
    Factory::getHealthCheckFactory()->update($check);
    
    Factory::getHealthCheckAgentFactory()->mset($checkAgent, [
        HealthCheckAgent::STATUS => DHealthCheckAgentStatus::PENDING,
        HealthCheckAgent::START => 0,
        HealthCheckAgent::END => 0,
        HealthCheckAgent::ERRORS => "",
        HealthCheckAgent::CRACKED => 0,
        HealthCheckAgent::NUM_GPUS => 0
      ]
    );
  }
  
  /**
   * Checks if there is a running health check which the agent has not completed yet.
   * @param Agent $agent
   * @return HealthCheckAgent|bool
   */
  public static function checkNeeded($agent) {
    $qF1 = new QueryFilter(HealthCheckAgent::AGENT_ID, $agent->getId(), "=");
    $qF2 = new QueryFilter(HealthCheckAgent::STATUS, DHealthCheckAgentStatus::PENDING, "=");
    $check = Factory::getHealthCheckAgentFactory()->filter([Factory::FILTER => [$qF1, $qF2]]);
    if (sizeof($check) == 0) {
      return false;
    }
    foreach ($check as $c) {
      // test if the check is still running
      $healthCheck = Factory::getHealthCheckFactory()->get($c->getHealthCheckId());
      if ($healthCheck->getStatus() == DHealthCheckStatus::PENDING) {
        return $c;
      }
    }
    return false;
  }
  
  /**
   * Check if the health check is completed (all agents sent a response)
   * @param HealthCheck $healthCheck
   */
  public static function checkCompletion($healthCheck) {
    $qF = new QueryFilter(HealthCheckAgent::HEALTH_CHECK_ID, $healthCheck->getId(), "=");
    $checks = Factory::getHealthCheckAgentFactory()->filter([Factory::FILTER => $qF]);
    foreach ($checks as $check) {
      if ($check->getStatus() != DHealthCheckAgentStatus::COMPLETED && $check->getStatus() != DHealthCheckAgentStatus::FAILED) {
        return; // we can stop here, at least one agent has not finished yet
      }
    }
    Factory::getHealthCheckFactory()->set($healthCheck, HealthCheck::STATUS, DHealthCheckStatus::COMPLETED);
  }
  
  /**
   * @param int $type
   * @return string
   * @throws HTException
   */
  private static function getAttackMode($type) {
    switch ($type) {
      case DHealthCheckType::BRUTE_FORCE:
        return " -a 3";
    }
    throw new HTException("Not able to get attack mode for this type!");
  }
  
  /**
   * @param int $hashtypeId
   * @param int $type
   * @return string
   * @throws HTException
   */
  private static function getAttackInput($hashtypeId, $type) {
    if ($type == DHealthCheckType::BRUTE_FORCE && $hashtypeId == DHealthCheckMode::MD5) {
      return " -1 ?l?u?d ?1?1?1?1?1";
    }
    else if ($type == DHealthCheckType::BRUTE_FORCE && $hashtypeId == DHealthCheckMode::BCRYPT) {
      return " ?l?l?l";
    }
    throw new HTException("Not able to get attack input for this type!");
  }
  
  /**
   * @param int $hashtypeId
   * @param int $type
   * @param bool $crackable
   * @return string
   * @throws HTException
   */
  private static function getAttackPlain($hashtypeId, $type, $crackable) {
    if ($type == DHealthCheckType::BRUTE_FORCE && $hashtypeId == DHealthCheckMode::MD5) {
      return Util::randomString(($crackable) ? 5 : 8);
    }
    else if ($type == DHealthCheckType::BRUTE_FORCE && $hashtypeId == DHealthCheckMode::BCRYPT) {
      return Util::randomString(($crackable) ? 3 : 8, "abcdefghijklmnopqrstuvwxyz");
    }
    throw new HTException("Not able to get attack plain for attack $type and hashtype $hashtypeId ($crackable)");
  }
  
  /**
   * @param int $hashtypeId
   * @return integer
   */
  private static function getAttackNumHashes($hashtypeId) {
    switch ($hashtypeId) {
      case DHealthCheckMode::MD5:
        return 100;
      case DHealthCheckMode::BCRYPT:
        return 10;
    }
    return DHealthCheck::NUM_HASHES;
  }
  
  /**
   * @param int $hashtypeId
   * @param int $type
   * @param int $crackerBinaryId
   * @return HealthCheck
   * @throws HttpError
   */
  public static function createHealthCheck($hashtypeId, $type, $crackerBinaryId) {
    $crackerBinary = Factory::getCrackerBinaryFactory()->get($crackerBinaryId);
    if ($crackerBinary == null) {
      throw new HttpError("Invalid cracker binary selected!");
    }
    else if ($type != DHealthCheckType::BRUTE_FORCE) {
      throw new HttpError("Invalid health check type!");
    }
    
    // we use len 5 here, but this can be adjusted depending on the agents abilities
    $hashes = [];
    $numHashes = HealthUtils::getAttackNumHashes($hashtypeId);
    $expected = rand(0.1 * $numHashes, 0.8 * $numHashes);
    for ($i = 0; $i < $numHashes; $i++) {
      $hashes[] = HealthUtils::generateHash($hashtypeId, HealthUtils::getAttackPlain($hashtypeId, $type, $i < $expected));
    }
    
    $cmd = SConfig::getInstance()->getVal(DConfig::HASHLIST_ALIAS) . HealthUtils::getAttackMode($type) . HealthUtils::getAttackInput($hashtypeId, $type);
    
    // create check
    $healthCheck = new HealthCheck(null,
      time(),
      DHealthCheckStatus::PENDING,
      $type,
      $hashtypeId,
      $crackerBinaryId,
      $expected,
      $cmd
    );
    $healthCheck = Factory::getHealthCheckFactory()->save($healthCheck);
    
    // save hashes
    $filename = "/tmp/health-check-" . $healthCheck->getId() . ".txt";
    file_put_contents($filename, implode("\n", $hashes));
    
    // check if file actually exists
    if (!file_exists($filename)) {
      Factory::getHealthCheckFactory()->delete($healthCheck);
      throw new HttpError("Failed to create hashes in tmp directory!");
    }
    
    // apply it to all agents
    $agents = Factory::getAgentFactory()->filter([]);
    $entries = [];
    foreach ($agents as $agent) {
      $entries[] = new HealthCheckAgent(null, $healthCheck->getId(), $agent->getId(), DHealthCheckAgentStatus::PENDING, 0, 0, 0, 0, "");
    }
    Factory::getHealthCheckAgentFactory()->massSave($entries);
    return $healthCheck;
  }
  
  /**
   * @param int $hashtypeId
   * @param string $plain
   * @return string
   * @throws HTException
   */
  public static function generateHash($hashtypeId, $plain) {
    switch ($hashtypeId) {
      case DHealthCheckMode::MD5:
        return md5($plain);
      case DHealthCheckMode::BCRYPT:
        return password_hash($plain, PASSWORD_BCRYPT, ["cost" => 5]);
      default:
        throw new HTException("No implementation for this hash type available to generate hashes!");
    }
  }
  
  /**
   * @param int $healthCheckId
   * @throws HTException
   */
  public static function deleteHealthCheck($healthCheckId) {
    $healthCheck = Factory::getHealthCheckFactory()->get($healthCheckId);
    if ($healthCheck === null) {
      throw new HTException("Invalid health check!");
    }
    $qF = new QueryFilter(HealthCheckAgent::HEALTH_CHECK_ID, $healthCheck->getId(), "=");
    Factory::getHealthCheckAgentFactory()->massDeletion([Factory::FILTER => $qF]);
    Factory::getHealthCheckFactory()->delete($healthCheck);
  }
}
