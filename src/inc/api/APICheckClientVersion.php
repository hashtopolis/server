<?php

namespace Hashtopolis\inc\api;

use Hashtopolis\inc\agent\PActions;
use Hashtopolis\inc\agent\PQueryCheckClientVersion;
use Hashtopolis\inc\agent\PResponseClientUpdate;
use Hashtopolis\inc\agent\PValues;
use Hashtopolis\inc\agent\PValuesUpdateVersion;
use Hashtopolis\inc\defines\DConfig;
use Hashtopolis\inc\defines\DServerLog;
use Hashtopolis\dba\models\AgentBinary;
use Hashtopolis\dba\QueryFilter;
use Hashtopolis\dba\Factory;
use Composer\Semver\Comparator;
use Hashtopolis\inc\SConfig;
use Hashtopolis\inc\Util;

class APICheckClientVersion extends APIBasic {
  public function execute($QUERY = array()) {
    // check if provided hash is the same as script and send file contents if not
    if (!PQueryCheckClientVersion::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::CHECK_CLIENT_VERSION, 'Invalid version check query!');
    }
    $this->checkToken(PActions::CHECK_CLIENT_VERSION, $QUERY);
    
    $version = $QUERY[PQueryCheckClientVersion::VERSION];
    $type = $QUERY[PQueryCheckClientVersion::TYPE];
    
    $qF = new QueryFilter(AgentBinary::BINARY_TYPE, $type, "=");
    $result = Factory::getAgentBinaryFactory()->filter([Factory::FILTER => $qF], true);
    if ($result == null) {
      DServerLog::log(DServerLog::WARNING, "Agent " . $this->agent->getId() . " sent unknown client type: " . $type);
      $this->sendErrorResponse(PActions::CHECK_CLIENT_VERSION, "Type not found!");
    }
    
    $this->updateAgent(PActions::CHECK_CLIENT_VERSION);
    if (Comparator::greaterThan($result->getVersion(), $version)) {
      DServerLog::log(DServerLog::DEBUG, "Agent " . $this->agent->getId() . " got notified about client update");
      $this->sendResponse(array(
          PResponseClientUpdate::ACTION => PActions::CHECK_CLIENT_VERSION,
          PResponseClientUpdate::RESPONSE => PValues::SUCCESS,
          PResponseClientUpdate::VERSION => PValuesUpdateVersion::NEW_VERSION,
          PResponseClientUpdate::URL => Util::buildServerUrl() . SConfig::getInstance()->getVal(DConfig::BASE_URL) . "/agents.php?download=" . $result->getId()
        )
      );
    }
    else {
      $this->sendResponse(array(
          PResponseClientUpdate::ACTION => PActions::CHECK_CLIENT_VERSION,
          PResponseClientUpdate::RESPONSE => PValues::SUCCESS,
          PResponseClientUpdate::VERSION => PValuesUpdateVersion::UP_TO_DATE
        )
      );
    }
  }
}
