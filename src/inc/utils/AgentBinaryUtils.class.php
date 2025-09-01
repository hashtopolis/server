<?php

use DBA\AgentBinary;
use DBA\QueryFilter;
use DBA\User;
use DBA\Factory;
use Composer\Semver\Comparator;

require_once(__DIR__ . "/../apiv2/common/ErrorHandler.class.php");

class AgentBinaryUtils {
  /**
   * @param string $type
   * @param string $os
   * @param string $filename
   * @param string $version
   * @param string $updateTrack
   * @param User $user
   * @return AgentBinary
   * @throws HttpError
   */
  public static function newBinary(string $type, string $os, string $filename, string $version, string $updateTrack, User $user): AgentBinary {
    if (strlen($version) == 0) {
      throw new HttpError("Version cannot be empty!");
    }
    else if (!file_exists(dirname(__FILE__) . "/../../bin/" . basename($filename))) {
      throw new HttpError("Provided filename does not exist!");
    }
    $qF = new QueryFilter(AgentBinary::BINARY_TYPE, $type, "=");
    $result = Factory::getAgentBinaryFactory()->filter([Factory::FILTER => $qF], true);
    if ($result != null) {
      throw new HttpError("You cannot have two binaries with the same type!");
    }
    $agentBinary = new AgentBinary(null, $type, $version, $os, $filename, $updateTrack, '');
    $agentBinary = Factory::getAgentBinaryFactory()->save($agentBinary);
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "New Binary " . $agentBinary->getFilename() . " was added!");
    
    return $agentBinary;
  }
  
  /**
   * @param int $binaryId
   * @param string $type
   * @param string $os
   * @param string $filename
   * @param string $version
   * @param string $updateTrack
   * @param User $user
   * @throws HTException
   */
  public static function editBinary($binaryId, $type, $os, $filename, $version, $updateTrack, $user) {
    if (strlen($version) == 0) {
      throw new HTException("Version cannot be empty!");
    }
    else if (!file_exists(dirname(__FILE__) . "/../../bin/" . basename($filename))) {
      throw new HTException("Provided filename does not exist!");
    }
    $agentBinary = AgentBinaryUtils::getBinary($binaryId);
    
    $qF1 = new QueryFilter(AgentBinary::BINARY_TYPE, $type, "=");
    $qF2 = new QueryFilter(AgentBinary::AGENT_BINARY_ID, $agentBinary->getId(), "<>");
    $result = Factory::getAgentBinaryFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($result != null) {
      throw new HTException("You cannot have two binaries with the same type!");
    }
    
    if ($updateTrack != $agentBinary->getUpdateTrack()) {
      Factory::getAgentBinaryFactory()->set($agentBinary, AgentBinary::UPDATE_AVAILABLE, '');
    }
    Factory::getAgentBinaryFactory()->mset($agentBinary, [
        AgentBinary::BINARY_TYPE => $type,
        AgentBinary::OPERATING_SYSTEMS => $os,
        AgentBinary::FILENAME => $filename,
        AgentBinary::VERSION => $version,
        AgentBinary::UPDATE_TRACK => $updateTrack
      ]
    );
    
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "Binary " . $agentBinary->getFilename() . " was updated!");
  }

  public static function editUpdateTracker($binaryId, $updateTracker, $user) {
    $binary = AgentBinaryUtils::getBinary($binaryId);
    if ($updateTracker != $binary->getUpdateTrack()) {
      Factory::getAgentBinaryFactory()->mset($binary, [
        AgentBinary::UPDATE_AVAILABLE => '',
        AgentBinary::UPDATE_TRACK => $updateTracker
      ]
    );
    } else {
      Factory::getAgentBinaryFactory()->set($binary, AgentBinary::UPDATE_TRACK, $updateTracker);
    }
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "Binary " . $binary->getFilename() . " was updated!");
  }

  public static function editName($binaryId, $filename, $user) {
    if (!file_exists(dirname(__FILE__) . "/../../bin/" . basename($filename))) {
      throw new HTException("Provided filename does not exist!");
    }
    $agentBinary = AgentBinaryUtils::getBinary($binaryId);
    Factory::getAgentBinaryFactory()->set($agentBinary, AgentBinary::FILENAME, $filename);
    Util::createLogEntry(DLogEntryIssuer::USER, $user->getId(), DLogEntry::INFO, "Binary " . $agentBinary->getFilename() . " was updated!");
  }

  public static function editType($binaryId, $type, $user) {
    $agentBinary = AgentBinaryUtils::getBinary($binaryId);
    
    $qF1 = new QueryFilter(AgentBinary::BINARY_TYPE, $type, "=");
    $qF2 = new QueryFilter(AgentBinary::AGENT_BINARY_ID, $agentBinary->getId(), "<>");
    $result = Factory::getAgentBinaryFactory()->filter([Factory::FILTER => [$qF1, $qF2]], true);
    if ($result != null) {
      throw new HTException("You cannot have two binaries with the same type!");
    }
    Factory::getAgentBinaryFactory()->set($agentBinary, AgentBinary::BINARY_TYPE, $type);
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
   * @return AgentBinary
   * @throws HTException
   */
  public static function getBinary($binaryId) {
    $agentBinary = Factory::getAgentBinaryFactory()->get($binaryId);
    if ($agentBinary == null) {
      throw new HTException("Binary does not exist!");
    }
    return $agentBinary;
  }
  
  /**
   * @param int $binaryId
   * @throws HTException
   */
  public static function executeUpgrade($binaryId) {
    $agentBinary = AgentBinaryUtils::getBinary($binaryId);
    // check if there is really an update available
    if (!AgentBinaryUtils::checkUpdate($binaryId)) {
      throw new HTException("No update available!");
    }
    $track = $agentBinary->getUpdateTrack();
    
    $extension = Util::extractFileExtension($agentBinary->getFilename());
    
    // download file to tmp directory
    Util::downloadFromUrl(HTP_AGENT_ARCHIVE . $agentBinary->getBinaryType() . "/$track/" . $agentBinary->getUpdateAvailable() . "." . $extension, "/tmp/" . $agentBinary->getUpdateAvailable() . "." . $extension);
    
    // download checksum
    Util::downloadFromUrl(HTP_AGENT_ARCHIVE . $agentBinary->getBinaryType() . "/$track/" . $agentBinary->getUpdateAvailable() . "." . $extension . ".sha256", "/tmp/" . $agentBinary->getUpdateAvailable() . "." . $extension . ".sha256");
    
    // check checksum
    $sum = hash_file("sha256", "/tmp/" . $agentBinary->getUpdateAvailable() . "." . $extension);
    $check = file_get_contents("/tmp/" . $agentBinary->getUpdateAvailable() . "." . $extension . ".sha256");
    if ($sum != $check) {
      throw new HTException("Checksum check for updated agent failed!");
    }
    
    // move file to right place
    rename("/tmp/" . $agentBinary->getUpdateAvailable() . "." . $extension, dirname(__FILE__) . "/../../bin/" . $agentBinary->getFilename());
    $sum = hash_file("sha256", dirname(__FILE__) . "/../../bin/" . $agentBinary->getFilename());
    if ($sum != $check) {
      throw new HTException("Failed to move new agent to right location!");
    }
    
    // update version number of agent and reset flag
    Factory::getAgentBinaryFactory()->mset($agentBinary, [AgentBinary::VERSION => $agentBinary->getUpdateAvailable(), AgentBinary::UPDATE_AVAILABLE => '']);
  }
  
  /**
   * @param int $binaryId
   * @return boolean|string
   * @throws HTException
   */
  public static function checkUpdate($binaryId) {
    $agentBinary = AgentBinaryUtils::getBinary($binaryId);
    $update = AgentBinaryUtils::getAgentUpdate($agentBinary->getBinaryType(), $agentBinary->getUpdateTrack());
    Factory::getAgentBinaryFactory()->set($agentBinary, AgentBinary::UPDATE_AVAILABLE, ($update) ? $update : '');
    return $update;
  }
  
  /**
   * Retrieves the latest version number for the according agent type and track.
   *
   * @param string $agent
   * @param string $track
   * @return string
   * @throws HTException
   */
  public static function getLatestVersion($agent, $track) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => HTP_AGENT_ARCHIVE . $agent . '/' . $track . '/HEAD',
      )
    );
    $resp = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($http_code != 200) {
      throw new HTException("Invalid HTTP status code: $http_code");
    }
    curl_close($curl);
    return trim($resp);
  }
  
  /**
   * @param string $agent
   * @param string $track
   * @return boolean|string
   * @throws HTException
   */
  public static function getAgentUpdate($agent, $track) {
    $qF = new QueryFilter(AgentBinary::BINARY_TYPE, $agent, "=");
    $agent = Factory::getAgentBinaryFactory()->filter([Factory::FILTER => $qF], true);
    if ($agent == null) {
      throw new HTException("Invalid agent binary type!");
    }
    $latest = AgentBinaryUtils::getLatestVersion($agent->getBinaryType(), $track);
    if (strlen($latest) == 0) {
      throw new HTException("Failed to retrieve latest version!");
    }
    if (Comparator::lessThan($agent->getVersion(), $latest)) {
      return $latest;
    }
    return false;
  }
}
