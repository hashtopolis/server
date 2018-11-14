<?php

use DBA\AgentBinary;
use DBA\QueryFilter;
use DBA\User;
use DBA\Factory;

class AgentBinaryUtils {
  /**
   * @param string $type
   * @param string $os
   * @param string $filename
   * @param string $version
   * @param User $user
   * @throws HTException
   */
  public static function newBinary($type, $os, $filename, $version, $user) {
    if (strlen($version) == 0) {
      throw new HTException("Version cannot be empty!");
    }
    else if (!file_exists(dirname(__FILE__) . "/../../bin/$filename")) {
      throw new HTException("Provided filename does not exist!");
    }
    $qF = new QueryFilter(AgentBinary::TYPE, $type, "=");
    $result = Factory::getAgentBinaryFactory()->filter([Factory::FILTER => $qF], true);
    if ($result != null) {
      throw new HTException("You cannot have two binaries with the same type!");
    }
    $agentBinary = new AgentBinary(null, $type, $version, $os, $filename);
    Factory::getAgentBinaryFactory()->save($agentBinary);
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "New Binary " . $agentBinary->getFilename() . " was added!");
  }
  
  /**
   * @param int $binaryId
   * @param string $type
   * @param string $os
   * @param string $filename
   * @param string $version
   * @param User $user
   * @throws HTException
   */
  public static function editBinary($binaryId, $type, $os, $filename, $version, $user) {
    if (strlen($version) == 0) {
      throw new HTException("Version cannot be empty!");
    }
    else if (!file_exists(dirname(__FILE__) . "/../../bin/$filename")) {
      throw new HTException("Provided filename does not exist!");
    }
    $agentBinary = AgentBinaryUtils::getBinary($binaryId);
    
    $qF1 = new QueryFilter(AgentBinary::TYPE, $type, "=");
    $qF2 = new QueryFilter(AgentBinary::AGENT_BINARY_ID, $agentBinary->getId(), "<>");
    $result = Factory::getAgentBinaryFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($result != null) {
      throw new HTException("You cannot have two binaries with the same type!");
    }
    
    $agentBinary->setType($type);
    $agentBinary->setOperatingSystems($os);
    $agentBinary->setFilename($filename);
    $agentBinary->setVersion($version);
    
    Factory::getAgentBinaryFactory()->update($agentBinary);
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "Binary " . $agentBinary->getFilename() . " was updated!");
  }
  
  /**
   * @param int $binaryId
   * @throws HTException
   */
  public static function deleteBinary($binaryId) {
    $agentBinary = AgentBinaryUtils::getBinary($binaryId);
    Factory::getAgentBinaryFactory()->delete($agentBinary);
    unlink(dirname(__FILE__) . "/../../bin/" . $agentBinary->getFilename());
  }
  
  /**
   * @param int $binaryId
   * @throws HTException
   * @return AgentBinary
   */
  public static function getBinary($binaryId) {
    $agentBinary = Factory::getAgentBinaryFactory()->get($binaryId);
    if ($agentBinary == null) {
      throw new HTException("Binary does not exist!");
    }
    return $agentBinary;
  }

  /**
   * Retrieves the latest version number for the according agent type and track.
   * 
   * @param string $agent 
   * @param string $track 
   * @return string
   */
  public static function getLatestVersion($agent, $track){
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'https://archive.hashtopolis.org/agent/' . $agent . '/' . $track,
    ));
    $resp = curl_exec($curl);
    curl_close($curl);
    return $resp;
  }

  public static function getAgentUpdate($agent, $track){
    $qF = new QueryFilter(AgentBinary::TYPE, $agent, "=");
    $agent = Factory::getAgentBinaryFactory()->filter([Factory::FILTER => $qF], true);
    if($agent == null){
      throw new HTException("Invalid agent binary type!");
    }
    $latest = AgentBinaryUtils::getLatestVersion($agent->getType(), $track);
    if(strlen($latest) == 0){
      throw new HTException("Failed to retrieve latest version!");
    }
    if(Util::versionComparison($agent->getVersion(), $latest) > 0){
      return $latest;
    }
    return false;
  }
}